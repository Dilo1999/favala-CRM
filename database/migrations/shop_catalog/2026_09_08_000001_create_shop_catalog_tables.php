<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Targets the separate `shop_catalog` PostgreSQL database (see App\Models\ShopCatalog) —
     * this is a distinct support system from the main CRM catalog, so it gets its own
     * database and its own migration-history table.
     */
    public $connection = 'shop_catalog';

    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description');
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->timestamps();

            $table->index(['description', 'category', 'brand']);
        });

        Schema::create('shop_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['shop_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_prices');
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('shops');
    }
};
