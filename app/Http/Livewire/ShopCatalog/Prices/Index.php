<?php

namespace App\Http\Livewire\ShopCatalog\Prices;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\ShopCatalog\Product;
use App\Models\ShopCatalog\Shop;
use App\Models\ShopCatalog\ShopProductPrice;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public bool $showForm = false;

    public ?int $editingId = null;

    public array $form = ['product_id' => null, 'shop_id' => null, 'price' => ''];

    public ?int $productFilter = null;

    public ?int $shopFilter = null;

    protected function rules(): array
    {
        return [
            'form.product_id' => 'required|exists:shop_catalog.shop_products,id',
            'form.shop_id' => 'required|exists:shop_catalog.shops,id',
            'form.price' => 'required|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->productFilter = request()->integer('product') ?: null;
        $this->shopFilter = request()->integer('shop') ?: null;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->form['product_id'] = $this->productFilter;
        $this->form['shop_id'] = $this->shopFilter;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->form = ShopProductPrice::findOrFail($id)->only(['product_id', 'shop_id', 'price']);
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            ShopProductPrice::findOrFail($this->editingId)->update($this->form);
        } else {
            ShopProductPrice::updateOrCreate(
                ['product_id' => $this->form['product_id'], 'shop_id' => $this->form['shop_id']],
                ['price' => $this->form['price']]
            );
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Price saved.');
    }

    public function delete(int $id): void
    {
        ShopProductPrice::findOrFail($id)->delete();
        session()->flash('status', 'Price entry deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = ['product_id' => null, 'shop_id' => null, 'price' => ''];
    }

    public function render()
    {
        $prices = ShopProductPrice::with(['product', 'shop'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->whereHas('product', fn ($p) => $p->where('description', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
                    ->orWhereHas('shop', fn ($s) => $s->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->productFilter, fn ($q) => $q->where('product_id', $this->productFilter))
            ->when($this->shopFilter, fn ($q) => $q->where('shop_id', $this->shopFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('shop-catalog.prices.index', [
            'prices' => $prices,
            'products' => Product::orderBy('description')->pluck('description', 'id'),
            'shops' => Shop::orderBy('name')->pluck('name', 'id'),
            'filteredProduct' => $this->productFilter ? Product::find($this->productFilter) : null,
            'filteredShop' => $this->shopFilter ? Shop::find($this->shopFilter) : null,
        ])->layout('layouts.shop-catalog');
    }
}
