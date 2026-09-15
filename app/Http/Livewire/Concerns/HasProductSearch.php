<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\ShopCatalog\Product as ShopProduct;
use App\Models\Vendor;
use App\Services\CrmTestProductsClient;
use App\Services\ShopCatalogSync;
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
 * PHP, not a SQL union. A product carried by several vendors/shops gets one
 * result row per vendor/shop (not just the cheapest) so every price is visible
 * and individually selectable, not silently hidden behind a "best price" pick.
 *
 * Each result's `key` encodes both the product and (when priced) the specific
 * vendor/shop it came from: "p{id}" or "p{id}v{vendorId}" for the main
 * catalog, "s{id}" or "s{id}h{shopId}" for the Shop Catalog — because the id
 * sequences of the two catalogs collide (both start at 1).
 *
 * Host component must implement `pickProduct(int $index, string $key): void`,
 * which should resolve the key via `resolveProductSelection()` (or the
 * simpler `resolveProductId()` when the vendor/price doesn't matter) before
 * doing anything else, then call closeProductSearch().
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

        $catalogProducts = Product::query()
            ->with('prices.vendor')
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get();

        // Quantity only exists for Source: CRM products (Shop Catalog carries
        // none) and is tracked per vendor, so a result priced at a specific
        // vendor shows that vendor's own quantity, not the product's total.
        // Fetched once for every matched CRM product here (not per price row,
        // not per keystroke-triggered result), rather than one HTTP call each.
        $crmTestProducts = app(CrmTestProductsClient::class);
        $crmProductIds = $catalogProducts->filter(fn (Product $p) => ! $p->shop_catalog_product_id)->pluck('id')->all();
        $vendorQtyByProduct = $crmTestProducts->vendorQuantitiesForProducts($crmProductIds);
        $totalQtyByProduct = $crmProductIds ? $crmTestProducts->all() : collect();

        $catalog = $catalogProducts->flatMap(function (Product $product) use ($vendorQtyByProduct, $totalQtyByProduct) {
            $prices = $product->currentPrices(); // one row per vendor, most recent price

            $source = $product->source_label;
            $isCrm = ! $product->shop_catalog_product_id;

            if ($prices->isEmpty()) {
                return [(object) [
                    'key' => "p{$product->id}",
                    'description' => $product->description,
                    'code' => $product->code,
                    'origin' => null,
                    'source' => $source,
                    'quantity' => $isCrm ? (int) ($totalQtyByProduct->get($product->id)['quantity'] ?? 0) : null,
                ]];
            }

            $vendorQuantities = $vendorQtyByProduct->get($product->id, collect());

            return $prices->map(fn (ProductVendorPrice $price) => (object) [
                'key' => "p{$product->id}v{$price->vendor_id}",
                'description' => $product->description,
                'code' => $product->code,
                'origin' => "via {$price->vendor->company_name} — ".number_format($price->price, 2),
                'source' => $source,
                'quantity' => $isCrm ? (int) $vendorQuantities->get($price->vendor_id, 0) : null,
            ]);
        });

        // A product picked from the Shop Catalog once already gets copied into
        // our own `products` table (see resolveProductSelection() below) so it
        // stays usable forever after — but the original Shop Catalog row never
        // goes away, so without this it would show up as duplicate results.
        // Once we have a local copy, that's the one to show.
        $localCodes = $catalogProducts->pluck('code')->filter()->map(fn ($code) => mb_strtolower($code))->all();

        $shopMatches = ShopProduct::query()
            ->with('prices.shop')
            ->where(fn ($q) => $q->where('description', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('description')
            ->limit(20)
            ->get()
            ->reject(fn (ShopProduct $product) => $product->code && in_array(mb_strtolower($product->code), $localCodes, true))
            ->flatMap(function (ShopProduct $product) {
                if ($product->prices->isEmpty()) {
                    return [(object) [
                        'key' => "s{$product->id}",
                        'description' => $product->description,
                        'code' => $product->code,
                        'origin' => null,
                        'source' => 'Shop Catalog',
                        'quantity' => null, // Not tracked for Shop Catalog products.
                    ]];
                }

                return $product->prices->map(fn ($price) => (object) [
                    'key' => "s{$product->id}h{$price->shop_id}",
                    'description' => $product->description,
                    'code' => $product->code,
                    'origin' => "via {$price->shop->name} — ".number_format($price->price, 2),
                    'source' => 'Shop Catalog',
                    'quantity' => null,
                ]);
            });

        return $catalog->concat($shopMatches)
            ->sortBy('description')
            ->take(20)
            ->values();
    }

    /**
     * Resolve an opaque search-result key back to a real `products.id`, when
     * the specific vendor/shop it was priced at doesn't matter to the caller
     * (e.g. Deals, which doesn't track a vendor per line item). Prefer
     * resolveProductSelection() when the caller needs the price too.
     */
    protected function resolveProductId(string $key): int
    {
        return $this->resolveProductSelection($key)->product_id;
    }

    /**
     * Resolve a search-result key into everything a line item needs: the real
     * `products.id`, the specific vendor it was priced at (if any), and that
     * vendor's current price. A shop-catalog pick ("s{id}" / "s{id}h{shopId}")
     * is materialized into the main `products` table (matched by code) at this
     * point — the moment it's actually used in a transaction — rather than
     * during search, which stays read-only. Materializing also mirrors every
     * shop's price into our own product_vendor_prices (see
     * ShopCatalogSync::syncProduct()), which is what makes the vendor lookup
     * below work immediately for a product picked for the very first time.
     */
    protected function resolveProductSelection(string $key): object
    {
        preg_match('/^([ps])(\d+)(?:[vh](\d+))?$/', $key, $m);

        $prefix = $m[1] ?? 'p';
        $id = isset($m[2]) ? (int) $m[2] : 0;
        $subId = (isset($m[3]) && $m[3] !== '') ? (int) $m[3] : null;

        if ($prefix === 'p') {
            $productId = $id;
            $vendorId = $subId;
        } else {
            $shopProduct = ShopProduct::with('prices.shop')->findOrFail($id);
            $productId = app(ShopCatalogSync::class)->syncProduct($shopProduct)->id;

            $vendorId = null;
            if ($subId) {
                $shop = $shopProduct->prices->firstWhere('shop_id', $subId)?->shop;
                $vendorId = $shop ? Vendor::where('company_name', $shop->name)->value('id') : null;
            }
        }

        $price = $vendorId
            ? ProductVendorPrice::where('product_id', $productId)->where('vendor_id', $vendorId)->latest('id')->value('price')
            : null;

        return (object) [
            'product_id' => $productId,
            'vendor_id' => $vendorId,
            'price' => $price !== null ? (float) $price : null,
        ];
    }
}
