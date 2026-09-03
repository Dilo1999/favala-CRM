<?php

namespace App\Http\Livewire\Crm\Prices;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\Vendor;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public bool $showForm = false;

    public ?int $editingId = null;

    public array $form = ['product_id' => null, 'vendor_id' => null, 'price' => ''];

    public ?int $productFilter = null;

    protected function rules(): array
    {
        return [
            'form.product_id' => 'required|exists:products,id',
            'form.vendor_id' => 'required|exists:vendors,id',
            'form.price' => 'required|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->productFilter = request()->integer('product') ?: null;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->form['product_id'] = $this->productFilter;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->form = ProductVendorPrice::findOrFail($id)->only(['product_id', 'vendor_id', 'price']);
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            ProductVendorPrice::findOrFail($this->editingId)->update($this->form);
        } else {
            ProductVendorPrice::create($this->form + ['added_by' => auth()->id()]);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Price saved.');
    }

    public function delete(int $id): void
    {
        ProductVendorPrice::findOrFail($id)->delete();
        session()->flash('status', 'Price entry deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = ['product_id' => null, 'vendor_id' => null, 'price' => ''];
    }

    public function render()
    {
        $prices = ProductVendorPrice::with(['product', 'vendor', 'addedBy'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->whereHas('product', fn ($p) => $p->where('description', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
                    ->orWhereHas('vendor', fn ($v) => $v->where('company_name', 'like', "%{$this->search}%"));
            }))
            ->when($this->productFilter, fn ($q) => $q->where('product_id', $this->productFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('crm.prices.index', [
            'prices' => $prices,
            'products' => Product::orderBy('description')->pluck('description', 'id'),
            'vendors' => Vendor::orderBy('company_name')->pluck('company_name', 'id'),
            'filteredProduct' => $this->productFilter ? Product::find($this->productFilter) : null,
        ])->layout('layouts.crm');
    }
}
