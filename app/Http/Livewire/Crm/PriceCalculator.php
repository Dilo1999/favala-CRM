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
     * Optional convenience: look a product up and push its current cost into the
     * (otherwise fully client-side) Alpine calculator via a browser event. The
     * calculator itself still works with zero data dependency if this is never used.
     */
    public function pickProduct(int $index, string $key): void
    {
        $product = Product::find($this->resolveProductId($key));
        $cost = (float) ($product?->cheapestCurrentPrice()?->price ?? 0);

        $this->closeProductSearch();

        $this->dispatchBrowserEvent('product-cost-picked', [
            'label' => $product?->description,
            'cost' => $cost,
        ]);
    }

    public function render()
    {
        return view('crm.price-calculator')->layout('layouts.crm');
    }
}
