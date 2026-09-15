<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Http\Livewire\Concerns\HasProductSearch;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Product;
use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;

class Create extends Component
{
    use HasProductSearch;

    // Deliberately untyped: bound live via wire:model to a <select> whose blank
    // "Select customer…"/"Unassigned" option submits "", and PHP's typed-property
    // coercion rejects "" => ?int with an uncaught TypeError before validation
    // ever runs.
    public $customer_id = null;

    public string $deal_date;

    public ?string $request_source = null;

    public $assigned_staff_id = null;

    public string $stage = 'potential';

    public ?string $additional_details = null;

    public array $products = [['product_id' => null, 'product_label' => null, 'qty' => 1, 'max_qty' => null]];

    public function mount(): void
    {
        $this->deal_date = now()->toDateString();
    }

    /** Normalizes the "Unassigned" option's "" back to null so it never reaches save() as an empty string. */
    public function updatedAssignedStaffId($value): void
    {
        if ($value === '') {
            $this->assigned_staff_id = null;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'deal_date' => 'required|date',
            'request_source' => 'nullable|string',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'stage' => 'required|in:potential,hot,lost',
            'additional_details' => 'nullable|string',
            'products.*.product_id' => 'required|exists:products,id',
        ];

        // Per-row max — capped at the vendor's available quantity for a
        // Source: CRM product (whichever vendor was picked in the search),
        // uncapped for Shop Catalog (not tracked there).
        foreach ($this->products as $i => $row) {
            $max = $row['max_qty'] ?? null;
            $rules["products.{$i}.qty"] = $max !== null
                ? ['required', 'numeric', 'min:0.01', "max:{$max}"]
                : ['required', 'numeric', 'min:0.01'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'products.*.qty.max' => 'Only :max available from this vendor for this product.',
        ];
    }

    public function addProductRow(): void
    {
        $this->products[] = ['product_id' => null, 'product_label' => null, 'qty' => 1, 'max_qty' => null];
    }

    public function removeProductRow(int $index): void
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products);
    }

    /**
     * Called by the <x-product-search> picker. Uses resolveProductSelection()
     * (not the simpler resolveProductId()) purely to know which vendor was
     * picked, so the qty cap below matches that vendor's available quantity —
     * DealProduct itself has no vendor_id column, so it isn't persisted.
     */
    public function pickProduct(int $index, string $key): void
    {
        $selection = $this->resolveProductSelection($key);

        $this->products[$index]['product_id'] = $selection->product_id;
        $this->products[$index]['product_label'] = Product::find($selection->product_id)?->description;
        $this->products[$index]['max_qty'] = $this->resolveMaxQty((string) $selection->product_id, $selection->vendor_id);

        if ($this->products[$index]['max_qty'] !== null && (float) $this->products[$index]['qty'] > $this->products[$index]['max_qty']) {
            $this->products[$index]['qty'] = $this->products[$index]['max_qty'];
        }

        $this->closeProductSearch();
    }

    public function save()
    {
        $this->validate();

        $deal = Deal::create([
            'customer_id' => (int) $this->customer_id,
            'deal_date' => $this->deal_date,
            'request_source' => $this->request_source,
            'assigned_staff_id' => $this->assigned_staff_id !== null ? (int) $this->assigned_staff_id : null,
            'stage' => $this->stage,
            'additional_details' => $this->additional_details,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->products as $row) {
            $deal->products()->create(['product_id' => $row['product_id'], 'qty' => $row['qty']]);
        }

        $deal->spawnQuery();

        session()->flash('status', 'Deal created.');

        return redirect()->route('crm.deals.show', $deal);
    }

    public function render()
    {
        return view('crm.deals.create', [
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'requestSources' => SettingOption::options(SettingOption::REQUEST_SOURCE),
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
        ])->layout('layouts.crm');
    }
}
