<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Targets the separate `crm_test` database — a temporary sandbox that mirrors
     * "Source: CRM" rows out of the main `products` table so they can be fetched
     * back in via an API call instead of a direct DB read (see CrmTestProductsClient).
     */
    public $connection = 'crm_test';

    public function up(): void
    {
        Schema::create('crm_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_product_id')->unique();
            $table->string('code')->unique();
            $table->string('legacy_code')->nullable();
            $table->string('description');
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit_of_measure', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_products');
    }
};
