<?php

namespace App\Http\Livewire\ShopCatalog\Products;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\SettingOption;
use App\Models\ShopCatalog\Product;
use App\Models\ShopCatalog\Shop;
use App\Models\ShopCatalog\ShopProductPrice;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithBasicTable, WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public array $form = ['code' => '', 'description' => '', 'category' => '', 'brand' => ''];

    /** Newly-selected image (temporary upload), if any. */
    public $image = null;

    /** The currently-saved image path when editing, so the form can preview/keep/clear it. */
    public ?string $existingImagePath = null;

    /** Filters the list by the same canonical category list used in the form. */
    public string $categoryFilter = '';

    /**
     * Repeatable shop+price rows edited alongside the product itself, so a new
     * product can be assigned to a shop (with its price) in the same step
     * instead of a separate trip to the Prices page. Each row: ['id' => existing
     * ShopProductPrice id or null, 'shop_id' => int|null, 'price' => string].
     */
    public array $priceRows = [];

    protected function rules(): array
    {
        return [
            'form.code' => 'required|string|max:50',
            'form.description' => 'required|string|max:191',
            'form.category' => 'nullable|string|in:'.implode(',', array_keys($this->categoryOptions())),
            'form.brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'priceRows.*.shop_id' => 'nullable|exists:shop_catalog.shops,id',
            'priceRows.*.price' => 'nullable|numeric|min:0',
        ];
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    /**
     * The canonical category list is the main CRM's own product categories
     * (App\Models\SettingOption) — read live across the connection, same as
     * the merged product search — so a category picked here always means the
     * same thing it means everywhere else in the app, and can be filtered on
     * reliably instead of drifting into free-text typos.
     */
    protected function categoryOptions(): array
    {
        return SettingOption::options(SettingOption::PRODUCT_CATEGORY);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $product = Product::findOrFail($id);
        $this->form = $product->only(array_keys($this->form));
        $this->image = null;
        $this->existingImagePath = $product->image_path;
        $this->priceRows = $product->prices->map(fn (ShopProductPrice $price) => [
            'id' => $price->id, 'shop_id' => $price->shop_id, 'price' => (string) $price->price,
        ])->all();

        if (empty($this->priceRows)) {
            $this->addPriceRow();
        }

        $this->showForm = true;
    }

    /** Clears the currently-saved image; takes effect when the form is saved. */
    public function removeImage(): void
    {
        $this->image = null;
        $this->existingImagePath = null;
    }

    public function addPriceRow(): void
    {
        $this->priceRows[] = ['id' => null, 'shop_id' => null, 'price' => ''];
    }

    public function removePriceRow(int $index): void
    {
        unset($this->priceRows[$index]);
        $this->priceRows = array_values($this->priceRows);
    }

    public function save(): void
    {
        $this->validate();

        $product = $this->editingId
            ? tap(Product::findOrFail($this->editingId))->update($this->form)
            : Product::create($this->form);

        if ($this->image) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->update(['image_path' => $this->image->store('shop-catalog/products', 'public')]);
        } elseif ($this->editingId && $this->existingImagePath === null && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $product->update(['image_path' => null]);
        }

        $keptPriceIds = [];

        foreach ($this->priceRows as $row) {
            if (empty($row['shop_id']) || $row['price'] === '' || $row['price'] === null) {
                continue;
            }

            $price = ShopProductPrice::updateOrCreate(
                ['product_id' => $product->id, 'shop_id' => $row['shop_id']],
                ['price' => $row['price']]
            );
            $keptPriceIds[] = $price->id;
        }

        // Rows that started as a real price entry but were cleared or removed
        // in the form should stop carrying that shop+price fact.
        $product->prices()->whereNotIn('id', $keptPriceIds)->delete();

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Product saved.');
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
        session()->flash('status', 'Product deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = ['code' => '', 'description' => '', 'category' => '', 'brand' => ''];
        $this->image = null;
        $this->existingImagePath = null;
        $this->priceRows = [['id' => null, 'shop_id' => null, 'price' => '']];
    }

    public function render()
    {
        $products = Product::with('prices.shop')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%");
            }))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy($this->sortField === 'created_at' ? 'description' : $this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('shop-catalog.products.index', [
            'products' => $products,
            'shops' => Shop::orderBy('name')->pluck('name', 'id'),
            'categories' => $this->categoryOptions(),
        ])->layout('layouts.shop-catalog');
    }
}
