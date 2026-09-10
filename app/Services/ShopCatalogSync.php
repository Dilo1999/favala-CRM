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
 * product/shop is matched to a local one by the stable
 * `shop_catalog_product_id` / `shop_catalog_shop_id` link column (falling
 * back to `code` / `company_name` only the first time, to pick up records
 * that already existed before that link column did). Matching by the stable
 * id — rather than by the editable code/name themselves, as this used to —
 * means renaming a product's code or a vendor's name here no longer breaks
 * the link or causes a duplicate to be re-created from the Shop Catalog side
 * on the next sync.
 */
class ShopCatalogSync
{
    /**
     * Mirror one Shop Catalog shop into the main CRM's Vendor list. Shop
     * Catalog is the source of truth, so this is an update-or-create on every
     * call, not a one-time seed — called both directly (the Shops admin page
     * pushes on every save) and indirectly (syncProduct() below needs a
     * vendor to attach each price to).
     */
    public function syncShop(Shop $shop): Vendor
    {
        $vendor = Vendor::where('shop_catalog_shop_id', $shop->id)->first()
            ?? Vendor::whereNull('shop_catalog_shop_id')->where('company_name', $shop->name)->first();

        $attributes = [
            'company_name' => $shop->name,
            'contact_person' => $shop->contact_person,
            'phone' => $shop->phone,
            'location' => $shop->location,
            'shop_catalog_shop_id' => $shop->id,
        ];

        if ($vendor) {
            $vendor->update($attributes);

            return $vendor;
        }

        return Vendor::create($attributes);
    }

    /**
     * Materialize one Shop Catalog product locally (if not already) and sync
     * its current prices. Idempotent — safe to call repeatedly.
     */
    public function syncProduct(ShopProduct $shopProduct): Product
    {
        $product = Product::where('shop_catalog_product_id', $shopProduct->id)->first()
            ?? Product::whereNull('shop_catalog_product_id')->where('code', $shopProduct->code)->first();

        $attributes = [
            'code' => $shopProduct->code,
            'description' => $shopProduct->description,
            'category' => $shopProduct->category,
            'brand' => $shopProduct->brand,
            'shop_catalog_product_id' => $shopProduct->id,
        ];

        if ($product) {
            $product->update($attributes);
        } else {
            $product = Product::create($attributes);
        }

        foreach ($shopProduct->prices as $shopPrice) {
            if (! $shopPrice->shop) {
                continue;
            }

            $vendor = $this->syncShop($shopPrice->shop);

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
     * Sync every Shop Catalog shop into the Vendor list. Returns how many were
     * touched. Shops\Index::save() already pushes on every create/edit, but a
     * shop saved before that existed (or edited directly in the database)
     * would otherwise never get a matching Vendor — mirrors syncAll() above,
     * called the same way (on the CRM Vendors page mount).
     */
    public function syncAllShops(): int
    {
        $shops = Shop::all();

        foreach ($shops as $shop) {
            $this->syncShop($shop);
        }

        return $shops->count();
    }

    /**
     * The reverse direction: when a local product's details are edited here
     * (Products page), push those details back to its Shop Catalog source (if
     * it has one — matched by `shop_catalog_product_id`, so renaming the code
     * here doesn't drop the link), so the two stay consistent instead of only
     * being kept in sync one-way. A no-op for products that never came from
     * the Shop Catalog in the first place.
     */
    public function pushProductDetails(Product $product): void
    {
        if (! $product->shop_catalog_product_id) {
            return;
        }

        $shopProduct = ShopProduct::find($product->shop_catalog_product_id);

        if (! $shopProduct) {
            return;
        }

        $shopProduct->update([
            'code' => $product->code,
            'description' => $product->description,
            'category' => $product->category,
            'brand' => $product->brand,
        ]);
    }

    /**
     * The reverse direction for shops: when a vendor's details are edited here
     * (Vendors page), push those details back to its Shop Catalog source (if
     * it has one — matched by `shop_catalog_shop_id`, so renaming the vendor
     * here doesn't drop the link), so the two stay consistent instead of only
     * being kept in sync one-way. A no-op for a vendor that never came from
     * the Shop Catalog in the first place.
     */
    public function pushShopDetails(Vendor $vendor): void
    {
        if (! $vendor->shop_catalog_shop_id) {
            return;
        }

        $shop = Shop::find($vendor->shop_catalog_shop_id);

        if (! $shop) {
            return;
        }

        $shop->update([
            'name' => $vendor->company_name,
            'contact_person' => $vendor->contact_person,
            'phone' => $vendor->phone,
            'location' => $vendor->location,
        ]);
    }

    /**
     * Reverse direction for prices: when a vendor price is entered/edited on
     * the Products > Update Prices page, and that vendor is actually one of
     * the shops mirrored in from the Shop Catalog (matched by the stable link
     * id) pricing a product that also came from there, push the new price
     * back to that shop's record too. No-op for a plain local vendor/product.
     */
    public function pushProductPrice(Product $product, Vendor $vendor, float $price): void
    {
        if (! $product->shop_catalog_product_id || ! $vendor->shop_catalog_shop_id) {
            return;
        }

        ShopProductPrice::updateOrCreate(
            ['shop_id' => $vendor->shop_catalog_shop_id, 'product_id' => $product->shop_catalog_product_id],
            ['price' => $price]
        );
    }
}
