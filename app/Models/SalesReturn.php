<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Services\ProductStockService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SalesReturn extends Model
{
    use HasFactory, HasFriendlyId;

    protected $table = 'returns';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PROCESSED => 'Processed',
        self::STATUS_REFUNDED => 'Refunded',
    ];

    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'invoice_id', 'customer_id', 'date', 'status', 'refund_applied_at', 'value', 'reason', 'created_by',
        'approval_status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'refund_applied_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'RET';
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    /**
     * Credit the return's value against the invoice. An invoice's own
     * subtotal/gst/grand_total are what was actually billed and never
     * change after the fact — a return only ever affects what's been paid,
     * via a system-generated negative "Refund" payment, so balance_due
     * (derived the normal way in Invoice::recalculatePaymentStatus()) comes
     * back up to reflect that the invoice is unpaid again. Capped at what
     * was actually paid — there's no cash to hand back beyond that, even if
     * the return itself is worth more. Idempotent via refund_applied_at —
     * safe to call even if already applied.
     */
    public function applyRefundToInvoice(): void
    {
        if ($this->refund_applied_at || ! $this->invoice_id) {
            return;
        }

        DB::transaction(function () {
            $invoice = Invoice::whereKey($this->invoice_id)->lockForUpdate()->first();

            if (! $invoice) {
                return;
            }

            $cashRefund = round(min((float) $this->value, max(0, $invoice->amount_paid)), 2);

            if ($cashRefund > 0) {
                $invoice->payments()->create([
                    'date' => now()->toDateString(),
                    'method' => Payment::METHOD_REFUND,
                    'reference' => "Return #{$this->friendly_id}",
                    'received_by' => auth()->id(),
                    'amount' => -$cashRefund,
                ]);
            }

            $invoice->recalculatePaymentStatus();

            $this->forceFill(['refund_applied_at' => now()])->save();
        });

        // Outside the DB transaction above (which locks the invoice row) since
        // this is a network call to crm-test-service — stock coming back is
        // the mirror of the decrement Invoice creation applied.
        app(ProductStockService::class)->restore($this->stockLines());
    }

    /**
     * ReturnItem doesn't store which vendor a return's product was sold at —
     * only InvoiceItem does (populated at invoice-creation time). Derive it
     * here by matching on product_id against this return's own invoice, so
     * stock comes back to the same vendor it left from.
     */
    private function stockLines(): \Illuminate\Support\Collection
    {
        $vendorByProduct = $this->invoice?->items->keyBy('product_id') ?? collect();

        return $this->items->map(fn (ReturnItem $item) => (object) [
            'product_id' => $item->product_id,
            'vendor_id' => $vendorByProduct->get($item->product_id)?->vendor_id,
            'qty' => $item->qty,
        ]);
    }

    /**
     * Undoes applyRefundToInvoice() — removes the refund payment, so the
     * invoice's balance comes back down to what it actually was before this
     * return was refunded (the invoice's own subtotal/gst/grand_total were
     * never touched to begin with, so there's nothing to restore there).
     */
    public function reverseRefundFromInvoice(): void
    {
        if (! $this->refund_applied_at || ! $this->invoice_id) {
            return;
        }

        DB::transaction(function () {
            $invoice = Invoice::whereKey($this->invoice_id)->lockForUpdate()->first();

            if (! $invoice) {
                return;
            }

            $invoice->payments()
                ->where('method', Payment::METHOD_REFUND)
                ->where('reference', "Return #{$this->friendly_id}")
                ->delete();

            $invoice->recalculatePaymentStatus();

            $this->forceFill(['refund_applied_at' => null])->save();
        });

        // Undoes the restore applyRefundToInvoice() applied.
        app(ProductStockService::class)->decrement($this->stockLines());
    }

    /**
     * Move to a new status, applying/reversing the invoice credit as the refunded state
     * is entered/left. Refunded can only be reached through approve() below — a return
     * must be cleared by Management/Admin first, so this guards against skipping that.
     */
    public function transitionTo(string $status): void
    {
        if ($status === self::STATUS_REFUNDED && $this->approval_status !== self::APPROVAL_APPROVED) {
            throw new \RuntimeException('This return must be approved before it can be marked Refunded.');
        }

        $wasRefunded = $this->status === self::STATUS_REFUNDED;
        $becomingRefunded = $status === self::STATUS_REFUNDED;

        $this->update(['status' => $status]);

        if (! $wasRefunded && $becomingRefunded) {
            $this->applyRefundToInvoice();
        } elseif ($wasRefunded && ! $becomingRefunded) {
            $this->reverseRefundFromInvoice();
        }
    }

    /** Management/Admin clears a pending return for refund — approving and refunding happen together. */
    public function approve(User $approver): void
    {
        $this->forceFill([
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ])->save();

        $this->transitionTo(self::STATUS_REFUNDED);
    }

    /** Management/Admin declines a pending return — it stays in its current status, never refunded. */
    public function reject(User $approver): void
    {
        $this->forceFill([
            'approval_status' => self::APPROVAL_REJECTED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ])->save();
    }
}
