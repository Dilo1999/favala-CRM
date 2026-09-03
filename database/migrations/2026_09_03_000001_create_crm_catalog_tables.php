<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_options', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50); // lead_source, customer_type, lead_status, product_category, task_type, request_source, activity_outcome, query_source, query_type
            $table->string('value', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['group', 'value']);
        });

        Schema::create('atolls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('islands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atoll_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['atoll_id', 'name']);
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index('company_name');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // FAVD-######
            $table->string('legacy_code')->nullable();
            $table->string('description');
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->timestamps();

            $table->index(['description', 'category', 'brand']);
        });

        Schema::create('product_vendor_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2); // ex-GST cost
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'vendor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_vendor_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('islands');
        Schema::dropIfExists('atolls');
        Schema::dropIfExists('setting_options');
    }
};
