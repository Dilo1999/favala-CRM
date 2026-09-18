<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasFriendlyId;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'quotation_id', 'customer_id', 'staff_id', 'reference_number', 'invoice_date', 'expiry_date',
        'bill_to_name', 'bill_to_phone', 'bill_to_address',
        'subtotal', 'discount_type', 'discount_value', 'gst_percent', 'gst_amount',
        'grand_total', 'amount_paid', 'balance_due', 'payment_status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'payment_status' => self::STATUS_PENDING,
        'discount_type' => 'flat',
        'discount_value' => 0,
        'gst_percent' => 8,
        'amount_paid' => 0,
        'balance_due' => 0,
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'INV';
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function lastPayment(): ?Payment
    {
        return $this->payments()->latest('date')->latest('id')->first();
    }

    public function isFullyDelivered(): bool
    {
        $totalOrdered = $this->items()->sum('qty');
        $totalDelivered = $this->deliveries()->with('items')->get()
            ->flatMap->items->sum('delivery_qty');

        return $totalOrdered > 0 && $totalDelivered >= $totalOrdered;
    }

    /**
     * Qty/amount actually refunded back per product — only returns that
     * reached STATUS_REFUNDED took stock back and paid cash out (see
     * SalesReturn::applyRefundToInvoice()), so a still-pending/processed
     * return doesn't yet reduce what the customer is shown as holding.
     * Expects `returns.items` to be loaded (or lazy-loads it on first use).
     */
    public function refundedByProduct(): \Illuminate\Support\Collection
    {
        return $this->returns
            ->where('status', SalesReturn::STATUS_REFUNDED)
            ->flatMap->items
            ->groupBy('product_id')
            ->map(fn ($items) => [
                'qty' => (float) $items->sum('qty'),
                'amount' => (float) $items->sum('amount'),
            ]);
    }

    /** Total credited back across every return that actually reached STATUS_REFUNDED on this invoice. */
    public function refundedValue(): float
    {
        return (float) $this->returns->where('status', SalesReturn::STATUS_REFUNDED)->sum('value');
    }

    /**
     * The invoice's totals net of whatever's actually been refunded — what
     * the customer owes today, not what was billed before any of it came
     * back. Subtotal is reduced by the refunded lines' own (pre-GST,
     * post-line-discount) amount; grand total is reduced by the return's
     * `value`, which already folds in GST and any order-level discount at
     * the same ratio the whole invoice carries (see
     * Returns\Create::getTotalValueProperty()) — the same figure that was
     * actually credited back, capped or not. Discount/GST are then derived
     * backwards from that so the three numbers stay internally consistent.
     */
    public function adjustedTotals(): array
    {
        $refundedValue = $this->refundedValue();
        $refundedSubtotal = $this->refundedByProduct()->sum('amount');

        $subtotal = round((float) $this->subtotal - $refundedSubtotal, 2);
        $grandTotal = max(round((float) $this->grand_total - $refundedValue, 2), 0);

        // A later payment recorded after a return was refunded (e.g. the
        // customer bought again on the same invoice) can leave more actually
        // paid than this return-adjusted total — never show a total smaller
        // than what's genuinely been paid, capped at what was originally
        // billed so it can't inflate past the real invoice.
        $paidFloor = min((float) $this->amount_paid, (float) $this->grand_total);
        if ($paidFloor > $grandTotal) {
            $subtotal = round($subtotal + ($paidFloor - $grandTotal), 2);
            $grandTotal = $paidFloor;
        }

        $gstPercent = (float) $this->gst_percent;
        $taxable = $gstPercent > 0 ? round($grandTotal / (1 + $gstPercent / 100), 2) : $grandTotal;
        $gst = round($grandTotal - $taxable, 2);
        $discount = round($subtotal - $taxable, 2);

        $balanceDue = max(round($grandTotal - (float) $this->amount_paid, 2), 0);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'gst' => $gst,
            'grand_total' => $grandTotal,
            'balance_due' => $balanceDue,
            'refunded_value' => $refundedValue,
        ];
    }

    /** Receive a payment (spec §6.8) and recompute paid/balance/status. */
    public function receivePayment(float $amount, string $method, ?string $reference, ?User $receivedBy, ?string $receiptPath = null): Payment
    {
        $payment = $this->payments()->create([
            'date' => now()->toDateString(),
            'method' => $method,
            'reference' => $reference,
            'receipt_path' => $receiptPath,
            'received_by' => $receivedBy?->id,
            'amount' => $amount,
        ]);

        $this->recalculatePaymentStatus();

        return $payment;
    }

    public function recalculatePaymentStatus(): void
    {
        $paid = round((float) $this->payments()->sum('amount'), 2);
        $balance = round((float) $this->grand_total - $paid, 2);

        // A full return refunds every cent paid, netting `paid` back to ~0
        // alongside a grand_total reduced to ~0 by the same return — that
        // also satisfies "balance <= 0", so without requiring paid > 0 first,
        // a fully-refunded invoice (holding no money at all) was wrongly
        // showing as Paid. Distinguished from a genuinely-just-created,
        // never-paid invoice (also paid = 0) by whether a refund actually
        // happened here.
        $status = self::STATUS_PENDING;
        if ($paid > 0 && $balance > 0.001) {
            $status = self::STATUS_PARTIAL;
        } elseif ($paid > 0 && $balance <= 0.001) {
            $status = self::STATUS_PAID;
        } elseif ($balance <= 0.001 && $this->payments()->where('method', Payment::METHOD_REFUND)->exists()) {
            $status = self::STATUS_REFUNDED;
        }

        $this->forceFill([
            'amount_paid' => $paid,
            'balance_due' => max($balance, 0),
            'payment_status' => $status,
        ])->save();
    }
}
