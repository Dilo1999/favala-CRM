<?php

namespace App\Http\Livewire\Crm\Quotations;

use App\Http\Livewire\Concerns\HasProductSearch;
use App\Models\Customer;
use App\Models\Deal;
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

    // Deliberately untyped, same reason as $record in mount(): this is bound
    // directly via wire:model to a <select> whose "None" option submits "",
    // and PHP's typed-property coercion rejects "" => ?int with a TypeError.
    public $dealId = null;

    public ?int $customer_id = null;

    public string $quotation_date;

    public string $expiry_date;

    public ?string $bill_to_name = null;

    public ?string $bill_to_phone = null;

    public ?string $bill_to_address = null;

    public ?string $terms_conditions = null;

    public array $items = [];

    public string $discount_type = 'flat';

    public float $discount_value = 0;

    public float $gst_percent = 8;

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
            $this->items = $record->items->map(fn ($i) => [
                'product_id' => $i->product_id, 'product_label' => $i->product?->description, 'vendor_id' => $i->vendor_id, 'cost' => (float) $i->cost,
                'qty' => (float) $i->qty, 'markup_percent' => (float) $i->markup_percent,
                'discount_type' => $i->discount_type, 'discount_value' => (float) $i->discount_value,
            ])->all();

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

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null, 'product_label' => null, 'vendor_id' => null, 'cost' => 0, 'qty' => 1,
            'markup_percent' => (float) config('crm.default_markup_percent'), 'discount_type' => 'flat', 'discount_value' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updateItemProduct(int $index, ?string $productId): void
    {
        $product = $productId ? Product::find($productId) : null;
        $best = $product?->cheapestCurrentPrice();

        $this->items[$index]['product_id'] = $productId;
        $this->items[$index]['product_label'] = $product?->description;
        $this->items[$index]['vendor_id'] = $best?->vendor_id;
        $this->items[$index]['cost'] = (float) ($best?->price ?? 0);
    }

    /** Called by the <x-product-search> picker (spec §6.7: "Product (searchable)"). */
    public function pickProduct(int $index, int $productId): void
    {
        $this->updateItemProduct($index, (string) $productId);
        $this->closeProductSearch();
    }

    public function updateItemVendor(int $index, ?string $vendorId): void
    {
        $productId = $this->items[$index]['product_id'] ?? null;
        $price = $productId
            ? ProductVendorPrice::where('product_id', $productId)->where('vendor_id', $vendorId)->latest('id')->first()
            : null;

        $this->items[$index]['vendor_id'] = $vendorId;
        $this->items[$index]['cost'] = (float) ($price?->price ?? 0);
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
        return PricingEngine::order($this->lines, $this->discount_type, $this->discount_value, $this->gst_percent);
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'deal_id' => $this->dealId,
            'customer_id' => $this->customer_id,
            'quotation_date' => $this->quotation_date,
            'expiry_date' => $this->expiry_date,
            'bill_to_name' => $this->bill_to_name,
            'bill_to_phone' => $this->bill_to_phone,
            'bill_to_address' => $this->bill_to_address,
            'terms_conditions' => $this->terms_conditions,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'gst_percent' => $this->gst_percent,
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

        session()->flash('status', 'Quotation saved.');

        return redirect()->route('crm.quotations.show', $quotation);
    }

    public function render()
    {
        return view('crm.quotations.formulate', [
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'deals' => $this->recordId ? collect() : Deal::whereDoesntHave('quotations')
                ->with('customer')->orderByDesc('created_at')->limit(100)->get(),
        ])->layout('layouts.crm');
    }
}
