<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Product;
use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;

class Create extends Component
{
    public ?int $customer_id = null;

    public string $deal_date;

    public ?string $request_source = null;

    public ?int $assigned_staff_id = null;

    public string $stage = 'potential';

    public ?string $additional_details = null;

    public array $products = [['product_id' => null, 'qty' => 1]];

    public function mount(): void
    {
        $this->deal_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'deal_date' => 'required|date',
            'request_source' => 'nullable|string',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'stage' => 'required|in:potential,hot,lost',
            'additional_details' => 'nullable|string',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:0.01',
        ];
    }

    public function addProductRow(): void
    {
        $this->products[] = ['product_id' => null, 'qty' => 1];
    }

    public function removeProductRow(int $index): void
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products);
    }

    public function save()
    {
        $this->validate();

        $deal = Deal::create([
            'customer_id' => $this->customer_id,
            'deal_date' => $this->deal_date,
            'request_source' => $this->request_source,
            'assigned_staff_id' => $this->assigned_staff_id,
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
            'productOptions' => Product::orderBy('description')->pluck('description', 'id'),
        ])->layout('layouts.crm');
    }
}
