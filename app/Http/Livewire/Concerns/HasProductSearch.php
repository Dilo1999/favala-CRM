<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Search-as-you-type product picker for repeating line-item rows (Quotations,
 * Invoices, Deals). Replaces a single giant preloaded <select> — which doesn't
 * scale to the thousands of products the catalog is expected to hold (spec §2) —
 * with a small debounced query limited to a handful of matches.
 *
 * Host component must implement `pickProduct(int $index, int $productId): void`
 * (update the row, then call closeProductSearch()).
 */
trait HasProductSearch
{
    public string $productSearch = '';

    public ?int $productSearchRow = null;

    public function openProductSearch(int $index): void
    {
        $this->productSearchRow = $index;
        $this->productSearch = '';
    }

    public function closeProductSearch(): void
    {
        $this->productSearchRow = null;
        $this->productSearch = '';
    }

    public function getProductSearchResultsProperty(): Collection
    {
        $term = trim($this->productSearch);

        if ($term === '' || $this->productSearchRow === null) {
            return new Collection();
        }

        return Product::query()
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get();
    }
}
