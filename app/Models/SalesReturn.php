<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
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

    protected $fillable = ['invoice_id', 'customer_id', 'date', 'status', 'refund_applied_at', 'value', 'reason', 'created_by'];

    protected $casts = [
        'date' => 'date',
        'refund_applied_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * Credit the return's value against the invoice (spec §6.10 open question —
     * resolved as: reduce the invoice's grand total by the returned value; any
     * portion the customer had already paid beyond the new, smaller total is
     * recorded as a system-generated negative "Refund" payment so the invoice's
     * amount_paid/balance_due/payment_status all stay derived the normal way.
     * Idempotent via refund_applied_at — safe to call even if already applied.
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

            $value = round((float) $this->value, 2);
            $newGrandTotal = max(round($invoice->grand_total - $value, 2), 0);
            $cashRefund = round(max(0, $invoice->amount_paid - $newGrandTotal), 2);

            $invoice->forceFill(['grand_total' => $newGrandTotal])->save();

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
    }

    /** Undoes applyRefundToInvoice() — restores the invoice total and removes the refund payment. */
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

            $value = round((float) $this->value, 2);

            $invoice->forceFill(['grand_total' => round($invoice->grand_total + $value, 2)])->save();

            $invoice->payments()
                ->where('method', Payment::METHOD_REFUND)
                ->where('reference', "Return #{$this->friendly_id}")
                ->delete();

            $invoice->recalculatePaymentStatus();

            $this->forceFill(['refund_applied_at' => null])->save();
        });
    }

    /** Move to a new status, applying/reversing the invoice credit as the refunded state is entered/left. */
    public function transitionTo(string $status): void
    {
        $wasRefunded = $this->status === self::STATUS_REFUNDED;
        $becomingRefunded = $status === self::STATUS_REFUNDED;

        $this->update(['status' => $status]);

        if (! $wasRefunded && $becomingRefunded) {
            $this->applyRefundToInvoice();
        } elseif ($wasRefunded && ! $becomingRefunded) {
            $this->reverseRefundFromInvoice();
        }
    }
}
