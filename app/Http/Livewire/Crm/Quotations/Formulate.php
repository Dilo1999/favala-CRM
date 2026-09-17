<?php

namespace App\Http\Livewire\Crm\Quotations;

use App\Http\Livewire\Concerns\HasProductSearch;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\Quotation;
use App\Models\Vendor;
use App\Services\PricingEngine;
use Livewire\Component;

class Formulate extends Component
{
    use HasProductSearch;

    public ?int $recordId = null;

    public ?string $friendlyId = null;

    public bool $hasInvoice = false;

    // Deliberately untyped, same reason as $record in mount(): this is bound
    // directly via wire:model to a <select> whose "None" option submits "",
    // and PHP's typed-property coercion rejects "" => ?int with a TypeError.
    public $dealId = null;

    // Deliberately untyped, same reason as $dealId above: bound live via
    // wire:model to a <select> whose blank "Select customer…" option submits
    // "", and PHP's typed-property coercion rejects "" => ?int with an
    // uncaught TypeError before validation ever runs.
    public $customer_id = null;

    public string $quotation_date;

    public string $expiry_date;

    public ?string $bill_to_name = null;

    public ?string $bill_to_phone = null;

    public ?string $bill_to_address = null;

    public ?string $terms_conditions = null;

    public array $items = [];

    public string $discount_type = 'flat';

    // Deliberately untyped, same reason as $customer_id above: bound live via
    // wire:model to a number input, and briefly clearing the field to retype a
    // new value round-trips "" through a strictly-typed float property, which
    // throws mid-edit. Normalized back to a number in updated*() below.
    public $discount_value = 0;

    public $gst_percent = 8;

    // Deliberately untyped: this component backs both /quotations/create (no route
    // parameter) and /quotations/{record}/edit. A nullable Model type-hint here
    // trips Livewire v2's implicit route-model-binding on the param-less route
    // (it tries to bind "record" anyway and throws ModelNotFoundException).
    public function mount($record = null): void
    {
        $this->quotation_date = now()->toDateString();
        $this->expiry_date = now()->addDays(config('crm.document_expiry_days'))->toDateString();
        $this->gst_percent = (float) config('crm.gst_percent');

        $record = $record ? Quotation::find($record) : null;

        if ($record && $record->exists) {
            $this->recordId = $record->id;
            $this->friendlyId = $record->friendly_id;
            $this->hasInvoice = $record->invoices()->count() > 0;
            $this->dealId = $record->deal_id;
            $this->customer_id = $record->customer_id;
            $this->quotation_date = $record->quotation_date->toDateString();
            $this->expiry_date = optional($record->expiry_date)->toDateString() ?? $this->expiry_date;
            $this->bill_to_name = $record->bill_to_name;
            $this->bill_to_phone = $record->bill_to_phone;
            $this->bill_to_address = $record->bill_to_address;
            $this->terms_conditions = $record->terms_conditions;
            $this->discount_type = $record->discount_type;
            $this->discount_value = (float) $record->discount_value;
            $this->gst_percent = (float) $record->gst_percent;
            $this->items = $record->items->map(function ($i) {
                $maxQty = $this->resolveMaxQty((string) $i->product_id, $i->vendor_id);

                // This quotation's own invoice (if any) already decremented
                // the vendor's stock by this exact line's qty, so the vendor's
                // *current* available no longer reflects that this line
                // already "owns" those units — add them back, or even
                // reducing the qty here would be wrongly blocked by a max
                // that's short by the amount this very line already took.
                if ($maxQty !== null && $this->hasInvoice) {
                    $maxQty += (float) $i->qty;
                }

                return [
                    'product_id' => $i->product_id, 'product_label' => $i->product?->description, 'vendor_id' => $i->vendor_id, 'cost' => (float) $i->cost,
                    'qty' => (float) $i->qty, 'markup_percent' => (float) $i->markup_percent,
                    'discount_type' => $i->discount_type, 'discount_value' => (float) $i->discount_value,
                    'max_qty' => $maxQty,
                ];
            })->all();

            return;
        }

        if ($dealId = request()->query('dealId')) {
            $this->dealId = (int) $dealId;
            $this->populateFromDeal($this->dealId);
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    /**
     * Fill customer + line items from a deal's requested products. Used both when
     * arriving via ?dealId= (e.g. "Convert to Quotation" from the deal page) and
     * when picking a deal from the "Populate from Deal" dropdown on this page.
     */
    protected function populateFromDeal(int $dealId): void
    {
        $deal = Deal::with('products.product')->find($dealId);

        if (! $deal) {
            return;
        }

        $this->customer_id = $deal->customer_id;
        $this->bill_to_name = $deal->customer?->contact_person;
        $this->bill_to_phone = $deal->customer?->phone;
        $this->bill_to_address = $deal->customer?->address;
        $this->items = $deal->products->map(function ($dp) {
            $best = $dp->product?->cheapestCurrentPrice();

            return [
                'product_id' => $dp->product_id, 'product_label' => $dp->product?->description, 'vendor_id' => $best?->vendor_id, 'cost' => (float) ($best?->price ?? 0),
                'qty' => (float) $dp->qty, 'markup_percent' => (float) config('crm.default_markup_percent'),
                'discount_type' => 'flat', 'discount_value' => 0,
                'max_qty' => $this->resolveMaxQty((string) $dp->product_id, $best?->vendor_id),
            ];
        })->all();

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function updatedDealId($value): void
    {
        if ($this->recordId) {
            return;
        }

        if ($value) {
            $this->dealId = (int) $value;
            $this->populateFromDeal($this->dealId);
        } else {
            // Normalize the <select>'s "" (None) back to null so it never
            // ends up as an empty string in $data['deal_id'] on save().
            $this->dealId = null;
        }
    }

    /** Normalizes a momentarily-blank discount field back to 0 instead of leaving "" sitting in a numeric property. */
    public function updatedDiscountValue($value): void
    {
        if ($value === '') {
            $this->discount_value = 0;
        }
    }

    /** Same as updatedDiscountValue() above, for the GST % field. */
    public function updatedGstPercent($value): void
    {
        if ($value === '') {
            $this->gst_percent = 0;
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null, 'product_label' => null, 'vendor_id' => null, 'cost' => 0, 'qty' => 1,
            'markup_percent' => (float) config('crm.default_markup_percent'), 'discount_type' => 'flat', 'discount_value' => 0,
            'max_qty' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * $vendorId/$cost let a caller apply a *specific* vendor's price (e.g. the
     * one actually picked from the product search list) instead of always
     * defaulting to the cheapest — passing neither keeps the old "cheapest
     * wins" behavior for callers that don't care which vendor.
     */
    public function updateItemProduct(int $index, ?string $productId, ?int $vendorId = null, ?float $cost = null): void
    {
        $product = $productId ? Product::find($productId) : null;

        if ($vendorId === null) {
            $best = $product?->cheapestCurrentPrice();
            $vendorId = $best?->vendor_id;
            $cost = (float) ($best?->price ?? 0);
        }

        $this->items[$index]['product_id'] = $productId;
        $this->items[$index]['product_label'] = $product?->description;
        $this->items[$index]['vendor_id'] = $vendorId;
        $this->items[$index]['cost'] = (float) ($cost ?? 0);
        $this->items[$index]['max_qty'] = $this->resolveMaxQty($productId, $vendorId);

        // Clamp an already-entered qty down to the new max — e.g. switching
        // to a lower-stock vendor after typing a qty that vendor can't cover.
        if ($this->items[$index]['max_qty'] !== null && (float) $this->items[$index]['qty'] > $this->items[$index]['max_qty']) {
            $this->items[$index]['qty'] = $this->items[$index]['max_qty'];
        }
    }

    /** Called by the <x-product-search> picker (spec §6.7: "Product (searchable)"). */
    public function pickProduct(int $index, string $key): void
    {
        $selection = $this->resolveProductSelection($key);
        $this->updateItemProduct($index, (string) $selection->product_id, $selection->vendor_id, $selection->price);
        $this->closeProductSearch();
    }

    public function updateItemVendor(int $index, ?string $vendorId): void
    {
        // The "—" (no vendor) option submits "" from $event.target.value, not
        // null — normalize it here so it never ends up inserted into the
        // nullable-but-integer quotation_items.vendor_id column as a literal
        // empty string, which fails under strict SQL mode.
        $vendorId = $vendorId !== '' ? $vendorId : null;

        $productId = $this->items[$index]['product_id'] ?? null;
        $price = $productId
            ? ProductVendorPrice::where('product_id', $productId)->where('vendor_id', $vendorId)->latest('id')->first()
            : null;

        $this->items[$index]['vendor_id'] = $vendorId;
        $this->items[$index]['cost'] = (float) ($price?->price ?? 0);
        $this->items[$index]['max_qty'] = $this->resolveMaxQty($productId, $vendorId ? (int) $vendorId : null);

        if ($this->items[$index]['max_qty'] !== null && (float) $this->items[$index]['qty'] > $this->items[$index]['max_qty']) {
            $this->items[$index]['qty'] = $this->items[$index]['max_qty'];
        }
    }

    public function getVendorOptionsProperty(): array
    {
        $options = [];
        foreach ($this->items as $i => $item) {
            $options[$i] = $item['product_id']
                ? Product::find($item['product_id'])?->prices()->with('vendor')->get()->unique('vendor_id')->pluck('vendor.company_name', 'vendor_id')->all() ?? []
                : [];
        }

        return $options;
    }

    public function getLinesProperty(): array
    {
        return collect($this->items)->map(fn ($item) => PricingEngine::line(
            (float) $item['cost'], (float) $item['qty'], (float) $item['markup_percent'],
            $item['discount_type'], (float) $item['discount_value']
        ))->all();
    }

    public function getSummaryProperty(): array
    {
        return PricingEngine::order($this->lines, $this->discount_type, (float) $this->discount_value, (float) $this->gst_percent);
    }

    protected function rules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'discount_value' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0',
        ];

        // Per-line max — capped at the vendor's available quantity for a
        // Source: CRM product, uncapped for Shop Catalog (not tracked there).
        // Whole units only — products are counted, not measured.
        foreach ($this->items as $i => $item) {
            $max = $item['max_qty'] ?? null;
            $rules["items.{$i}.qty"] = $max !== null
                ? ['required', 'integer', 'min:1', "max:{$max}"]
                : ['required', 'integer', 'min:1'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'items.*.qty.max' => 'Only :max available from this vendor for this product.',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'deal_id' => $this->dealId,
            'customer_id' => (int) $this->customer_id,
            'quotation_date' => $this->quotation_date,
            'expiry_date' => $this->expiry_date,
            'bill_to_name' => $this->bill_to_name,
            'bill_to_phone' => $this->bill_to_phone,
            'bill_to_address' => $this->bill_to_address,
            'terms_conditions' => $this->terms_conditions,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'gst_percent' => (float) $this->gst_percent,
        ];

        if ($this->recordId) {
            $quotation = Quotation::findOrFail($this->recordId);
            $quotation->update($data);
            $quotation->items()->delete();
        } else {
            $data['staff_id'] = auth()->id();
            $data['status'] = Quotation::STATUS_DRAFT;
            $quotation = Quotation::create($data);
        }

        foreach ($this->items as $i => $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'vendor_id' => $item['vendor_id'],
                'qty' => $item['qty'],
                'cost' => $item['cost'],
                'markup_percent' => $item['markup_percent'],
                'discount_type' => $item['discount_type'],
                'discount_value' => $item['discount_value'],
                'sort_order' => $i,
            ]);
        }

        $quotation->recalculateTotals();
        $quotation->syncLinkedQuery();

        session()->flash('status', $this->syncInvoiceIfPending($quotation)
            ? 'Quotation saved — its invoice was updated to match.'
            : 'Quotation saved.');

        return redirect()->route('crm.quotations.show', $quotation);
    }

    /**
     * A converted quotation's invoice previously never saw a later edit at
     * all — the two documents could show different totals for the same
     * sale. Once money has actually moved against the invoice, rewriting
     * its items out from under that would corrupt the payment/balance math
     * (the customer paid against the old total), so this only re-syncs an
     * invoice that's still fully unpaid — its items and totals still exist
     * purely because the quotation created them, so a further quotation
     * edit re-applying them is the same operation.
     *
     * @return bool Whether an invoice was actually re-synced.
     */
    private function syncInvoiceIfPending(Quotation $quotation): bool
    {
        $invoice = $quotation->invoices()->first();

        if (! $invoice || $invoice->payment_status !== Invoice::STATUS_PENDING) {
            return false;
        }

        $invoice->items()->delete();

        foreach ($quotation->items as $index => $item) {
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

        $invoice->forceFill([
            'subtotal' => $quotation->subtotal,
            'discount_type' => $quotation->discount_type,
            'discount_value' => $quotation->discount_value,
            'gst_percent' => $quotation->gst_percent,
            'gst_amount' => $quotation->gst_amount,
            'grand_total' => $quotation->grand_total,
            'balance_due' => $quotation->grand_total,
        ])->save();

        return true;
    }

    public function convert()
    {
        $quotation = Quotation::findOrFail($this->recordId);
        $invoice = $quotation->convertToInvoice(auth()->user());
        session()->flash('status', 'Quotation converted to invoice.');

        return redirect()->route('crm.invoices.show', $invoice);
    }

    public function render()
    {
        return view('crm.quotations.formulate', [
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'deals' => $this->recordId
                ? ($this->dealId ? Deal::with('customer')->whereKey($this->dealId)->get() : collect())
                : Deal::whereDoesntHave('quotations')->with('customer')->orderByDesc('created_at')->limit(100)->get(),
        ])->layout('layouts.crm');
    }
}
