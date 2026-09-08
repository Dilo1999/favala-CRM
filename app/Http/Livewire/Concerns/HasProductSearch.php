<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Product;
use App\Models\ShopCatalog\Product as ShopProduct;
use Illuminate\Support\Collection;

/**
 * Search-as-you-type product picker for repeating line-item rows (Quotations,
 * Invoices, Deals). Replaces a single giant preloaded <select> — which doesn't
 * scale to the thousands of products the catalog is expected to hold (spec §2) —
 * with a small debounced query limited to a handful of matches.
 *
 * Results are merged live from the main `products` table AND the separate Shop
 * Catalog support system's database (App\Models\ShopCatalog\Product) — the two
 * are physically different databases, so this is two small queries merged in
 * PHP, not a SQL union. Each result carries an opaque string `key` ("p{id}" for
 * the main catalog, "s{id}" for the shop catalog) rather than a raw id, because
 * the id sequences of the two catalogs collide (both start at 1).
 *
 * Host component must implement `pickProduct(int $index, string $key): void`,
 * which should resolve the key via `resolveProductId()` to get a real
 * `products.id` before doing anything else, then call closeProductSearch().
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

        $catalog = Product::query()
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get()
            ->map(fn (Product $product) => (object) [
                'key' => "p{$product->id}",
                'description' => $product->description,
                'code' => $product->code,
                'origin' => null,
            ]);

        $shopMatches = ShopProduct::query()
            ->with('prices')
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get()
            ->map(function (ShopProduct $product) {
                $cheapest = $product->cheapestPrice();

                return (object) [
                    'key' => "s{$product->id}",
                    'description' => $product->description,
                    'code' => $product->code,
                    'origin' => $cheapest
                        ? "via {$cheapest->shop->name} — ".number_format($cheapest->price, 2)
                        : 'Shop Catalog',
                ];
            });

        return $catalog->concat($shopMatches)
            ->sortBy('description')
            ->take(20)
            ->values();
    }

    /**
     * Resolve an opaque search-result key back to a real `products.id`. A
     * shop-catalog pick ("s{id}") is materialized into the main `products`
     * table (matched by code) at this point — the moment it's actually used
     * in a transaction — rather than during search, which stays read-only.
     */
    protected function resolveProductId(string $key): int
    {
        if ($key[0] === 'p') {
            return (int) substr($key, 1);
        }

        $shopProduct = ShopProduct::findOrFail((int) substr($key, 1));

        return Product::firstOrCreate(
            ['code' => $shopProduct->code],
            [
                'description' => $shopProduct->description,
                'category' => $shopProduct->category,
                'brand' => $shopProduct->brand,
            ]
        )->id;
    }
}
