<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Services\PricingEngine;
use App\Services\ProductStockService;
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
        'bill_to_name', 'bill_to_phone', 'bill_to_address', 'terms_conditions',
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

    /**
     * Keep the linked Query in sync with this quotation (spec §5: "once a quotation exists
     * it shows the Quotation #"). Call after items are persisted + totals recalculated.
     *
     * - Deal-originated quotation: the deal already spawned a Query — just attach this
     *   quotation's number/value/tags to it, without touching its "Auto-generated from
     *   Deal #…" description.
     * - Standalone ("Cash Sale") quotation: no deal, no existing query — spawn one, tagged
     *   Quotation Request + product category, landing in Negotiating.
     */
    public function syncLinkedQuery(): void
    {
        $items = $this->items()->with('product')->get();
        $categories = $items->pluck('product.category')->filter()->unique()->values()->all();

        $query = $this->deal?->salesQuery ?? $this->salesQueries()->where('source', 'quotation')->first();

        if ($query) {
            $query->update([
                'quotation_id' => $this->id,
                'value' => $this->grand_total,
                'tags' => array_values(array_unique(array_merge($query->tags ?? [], ['Quotation Request'], $categories))),
            ]);

            return;
        }

        if ($this->deal_id) {
            return; // Deal exists but its query is missing — don't fabricate a second one.
        }

        $productList = $items
            ->map(fn (QuotationItem $i) => $i->product
                ? "{$i->product->description} (Qty: ".SalesQuery::formatQty((float) $i->qty).')'
                : null)
            ->filter()
            ->implode(', ');

        $description = "Auto-generated from Quotation #{$this->friendly_id}";
        if ($productList) {
            $description .= ' — '.$productList;
        }

        SalesQuery::create([
            'customer_id' => $this->customer_id,
            'phone' => $this->bill_to_phone ?: $this->customer?->phone,
            'description' => $description,
            'tags' => array_values(array_unique(array_merge(['Quotation Request'], $categories))),
            'source' => 'quotation',
            'quotation_id' => $this->id,
            'value' => $this->grand_total,
            'status' => SalesQuery::STATUS_NEGOTIATING,
            'assigned_staff_id' => $this->staff_id,
        ]);
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
                'vendor_id' => $item->vendor_id,
                'qty' => $item->qty,
                'rate' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'amount' => $item->line_amount,
                'sort_order' => $index,
            ]);
        }

        // A confirmed sale (this bypasses Invoices\Create::save() entirely, so
        // it needs its own copy of the same stock decrement).
        app(ProductStockService::class)->decrement($this->items);

        if ($this->deal) {
            $this->deal->markWon();
        }

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
