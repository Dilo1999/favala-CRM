<?php

namespace App\Http\Livewire\Crm;

use App\Http\Livewire\Concerns\HasProductSearch;
use App\Models\Product;
use Livewire\Component;

class PriceCalculator extends Component
{
    use HasProductSearch;

    protected $layout = 'layouts.crm';

    /**
     * Optional convenience: look a product up and add it (with its current cost)
     * to the otherwise fully client-side Alpine calculator via a browser event.
     * One shared search box — each pick appends a new product row, so searching
     * again immediately after adds another. The calculator itself still works
     * with zero data dependency if this is never used.
     */
    public function pickProduct(int $index, string $key): void
    {
        $selection = $this->resolveProductSelection($key);
        $product = Product::find($selection->product_id);
        // Use the specific vendor/shop price that was actually picked from the
        // search list — falling back to cheapest only for the no-price case.
        $cost = $selection->price ?? (float) ($product?->cheapestCurrentPrice()?->price ?? 0);

        $this->closeProductSearch();

        $this->dispatchBrowserEvent('product-picked', [
            'label' => $product?->description,
            'cost' => $cost,
        ]);
    }

    public function render()
    {
        return view('crm.price-calculator')->layout('layouts.crm');
    }
}
