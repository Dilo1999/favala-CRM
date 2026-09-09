<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\ShopCatalog\Product as ShopProduct;
use App\Models\Vendor;
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
            ->with('prices.vendor')
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get()
            ->map(function (Product $product) {
                $cheapest = $product->cheapestCurrentPrice();

                return (object) [
                    'key' => "p{$product->id}",
                    'description' => $product->description,
                    'code' => $product->code,
                    // Show the current price here too — a product that started life
                    // as a Shop Catalog pick (see resolveProductId() below) already
                    // has its price synced into our own vendor pricing, and hiding
                    // the Shop Catalog duplicate below must not also hide that price.
                    'origin' => $cheapest
                        ? "via {$cheapest->vendor->company_name} — ".number_format($cheapest->price, 2)
                        : null,
                ];
            });

        // A product picked from the Shop Catalog once already gets copied into
        // our own `products` table (see resolveProductId() below) so it stays
        // usable forever after — but the original Shop Catalog row never goes
        // away, so without this it would show up as two identical-looking
        // results. Once we have a local copy, that's the one to show.
        $localCodes = $catalog->pluck('code')->filter()->map(fn ($code) => mb_strtolower($code))->all();

        $shopMatches = ShopProduct::query()
            ->with('prices')
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get()
            ->reject(fn (ShopProduct $product) => $product->code && in_array(mb_strtolower($product->code), $localCodes, true))
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
     *
     * Materializing the product alone isn't enough: every caller of this trait
     * (Price Calculator, Quotations, Invoices) immediately looks up cost via
     * cheapestCurrentPrice(), which reads our own product_vendor_prices table —
     * so the shop catalog's price(s) must be copied over too, otherwise a
     * freshly-picked shop product always prices at zero.
     */
    protected function resolveProductId(string $key): int
    {
        if ($key[0] === 'p') {
            return (int) substr($key, 1);
        }

        $shopProduct = ShopProduct::with('prices.shop')->findOrFail((int) substr($key, 1));

        $product = Product::firstOrCreate(
            ['code' => $shopProduct->code],
            [
                'description' => $shopProduct->description,
                'category' => $shopProduct->category,
                'brand' => $shopProduct->brand,
            ]
        );

        $this->syncShopCatalogPrices($product, $shopProduct);

        return $product->id;
    }

    /**
     * Mirrors every shop's current price for this product into our own vendor
     * pricing (one local Vendor per shop, matched by name). Idempotent — only
     * writes a new price-history row when the shop's price actually changed,
     * consistent with the "most recent price wins" rule (spec §6.13).
     */
    protected function syncShopCatalogPrices(Product $product, ShopProduct $shopProduct): void
    {
        foreach ($shopProduct->prices as $shopPrice) {
            if (! $shopPrice->shop) {
                continue;
            }

            $vendor = Vendor::firstOrCreate(
                ['company_name' => $shopPrice->shop->name],
                [
                    'contact_person' => $shopPrice->shop->contact_person,
                    'phone' => $shopPrice->shop->phone,
                    'location' => $shopPrice->shop->location,
                ]
            );

            $latest = ProductVendorPrice::where('product_id', $product->id)
                ->where('vendor_id', $vendor->id)
                ->latest('id')
                ->first();

            if ($latest && (float) $latest->price === (float) $shopPrice->price) {
                continue;
            }

            ProductVendorPrice::create([
                'product_id' => $product->id,
                'vendor_id' => $vendor->id,
                'price' => $shopPrice->price,
                'added_by' => auth()->id(),
            ]);
        }
    }
}
