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
