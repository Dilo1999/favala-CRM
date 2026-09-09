<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\ShopCatalog\Product as ShopProduct;
use App\Models\ShopCatalog\Shop;
use App\Models\ShopCatalog\ShopProductPrice;
use App\Models\Vendor;

/**
 * Keeps the local `products` catalog in step with the external Shop Catalog
 * system (a separate database, App\Models\ShopCatalog\*). A Shop Catalog
 * product is matched to a local one by `code`, materializing it if it
 * doesn't exist yet, and its prices are mirrored into our own
 * `product_vendor_prices` (one local Vendor per shop, matched by name).
 */
class ShopCatalogSync
{
    /**
     * Materialize one Shop Catalog product locally (if not already) and sync
     * its current prices. Idempotent — safe to call repeatedly.
     */
    public function syncProduct(ShopProduct $shopProduct): Product
    {
        $product = Product::firstOrCreate(
            ['code' => $shopProduct->code],
            [
                'description' => $shopProduct->description,
                'category' => $shopProduct->category,
                'brand' => $shopProduct->brand,
            ]
        );

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

        return $product;
    }

    /** Sync every Shop Catalog product into the local catalog. Returns how many were touched. */
    public function syncAll(): int
    {
        $shopProducts = ShopProduct::with('prices.shop')->get();

        foreach ($shopProducts as $shopProduct) {
            $this->syncProduct($shopProduct);
        }

        return $shopProducts->count();
    }

    /**
     * The reverse direction: when a local product's details are edited here
     * (Products page), push those details back to its Shop Catalog source (if
     * it has one — matched by `code`, the same key used to sync it in), so the
     * two stay consistent instead of only being kept in sync one-way. A no-op
     * for products that never came from the Shop Catalog in the first place.
     */
    public function pushProductDetails(Product $product): void
    {
        $shopProduct = ShopProduct::where('code', $product->code)->first();

        if (! $shopProduct) {
            return;
        }

        $shopProduct->update([
            'description' => $product->description,
            'category' => $product->category,
            'brand' => $product->brand,
        ]);
    }

    /**
     * Reverse direction for prices: when a vendor price is entered/edited on
     * the Products > Update Prices page, and that vendor is actually one of
     * the shops mirrored in from the Shop Catalog (matched by name) pricing a
     * product that also came from there (matched by code), push the new price
     * back to that shop's record too. No-op for a plain local vendor/product.
     */
    public function pushProductPrice(Product $product, Vendor $vendor, float $price): void
    {
        $shopProduct = ShopProduct::where('code', $product->code)->first();
        $shop = Shop::where('name', $vendor->company_name)->first();

        if (! $shopProduct || ! $shop) {
            return;
        }

        ShopProductPrice::updateOrCreate(
            ['shop_id' => $shop->id, 'product_id' => $shopProduct->id],
            ['price' => $price]
        );
    }
}
