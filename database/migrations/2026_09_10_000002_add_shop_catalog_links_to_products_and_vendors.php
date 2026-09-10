<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives the sync between this CRM's products/vendors and the separate Shop
 * Catalog database (App\Services\ShopCatalogSync) a stable key to match on.
 * It previously matched by `products.code` / `vendors.company_name` — fields
 * the very same save actions let a user edit — so renaming either one broke
 * the link silently and could leave a duplicate re-created from the
 * now-orphaned Shop Catalog side on the next sync.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Not a real foreign key: shop_catalog is a separate database connection.
            $table->unsignedBigInteger('shop_catalog_product_id')->nullable()->unique()->after('code');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_catalog_shop_id')->nullable()->unique()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('shop_catalog_product_id');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('shop_catalog_shop_id');
        });
    }
};
