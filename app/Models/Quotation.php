<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Services\PricingEngine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory, HasFriendlyId;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    protected $fillable = [
        'deal_id', 'customer_id', 'quotation_date', 'expiry_date', 'staff_id', 'status',
        'bill_to_name', 'bill_to_phone', 'bill_to_address',
        'subtotal', 'discount_type', 'discount_value', 'gst_percent', 'gst_amount',
        'grand_total', 'total_profit', 'profit_margin',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'discount_type' => 'flat',
        'discount_value' => 0,
        'gst_percent' => 8,
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'QT';
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
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
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isExpired(): bool
    {
        return $this->status !== self::STATUS_SENT
            ? false
            : ($this->expiry_date && $this->expiry_date->isPast() && ! $this->invoices()->exists());
    }

    /** Recompute item lines + order totals via the shared PricingEngine (spec §8). */
    public function recalculateTotals(): void
    {
        $lines = $this->items->map(function (QuotationItem $item) {
            $priced = PricingEngine::line(
                (float) $item->cost,
                (float) $item->qty,
                (float) $item->markup_percent,
                $item->discount_type,
                (float) $item->discount_value
            );
            $item->forceFill([
                'unit_price' => $priced['unit_price'],
                'line_amount' => $priced['line_amount'],
            ])->save();

            return $priced;
        });

        $totals = PricingEngine::order(
            $lines->all(),
            $this->discount_type,
            (float) $this->discount_value,
            (float) $this->gst_percent
        );

        $this->forceFill([
            'subtotal' => $totals['subtotal'],
            'gst_amount' => $totals['gst_amount'],
            'grand_total' => $totals['grand_total'],
            'total_profit' => $totals['total_profit'],
            'profit_margin' => $totals['profit_margin'],
        ])->save();
    }

    public function markSent(): void
    {
        $this->update(['status' => self::STATUS_SENT]);
    }

    /** Convert to Invoice (spec §5): inherits number, items, GST and totals; starts fully unpaid. */
    public function convertToInvoice(?User $staff = null): Invoice
    {
        $invoice = Invoice::create([
            'quotation_id' => $this->id,
            'customer_id' => $this->customer_id,
            'staff_id' => $staff?->id ?? $this->staff_id,
            'invoice_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(config('crm.document_expiry_days'))->toDateString(),
            'bill_to_name' => $this->bill_to_name,
            'bill_to_phone' => $this->bill_to_phone,
            'bill_to_address' => $this->bill_to_address,
            'subtotal' => $this->subtotal,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'gst_percent' => $this->gst_percent,
            'gst_amount' => $this->gst_amount,
            'grand_total' => $this->grand_total,
            'amount_paid' => 0,
            'balance_due' => $this->grand_total,
            'payment_status' => 'pending',
        ]);

        foreach ($this->items as $index => $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'rate' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'amount' => $item->line_amount,
                'sort_order' => $index,
            ]);
        }

        if ($this->deal) {
            $this->deal->markWon();
        }

        // A query may already reference this quotation directly, or (more often) it was
        // spawned from the originating deal before the quotation existed — check both.
        $linkedQuery = $this->salesQueries()->first() ?? $this->deal?->salesQuery;

        if ($linkedQuery) {
            $linkedQuery->update(['status' => SalesQuery::STATUS_COMPLETED, 'quotation_id' => $this->id]);
        }

        return $invoice;
    }

    public function salesQueries(): HasMany
    {
        return $this->hasMany(SalesQuery::class);
    }
}
