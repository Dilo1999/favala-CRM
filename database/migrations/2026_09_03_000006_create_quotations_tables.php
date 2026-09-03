<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('quotation_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // draft, sent

            $table->string('bill_to_name')->nullable();
            $table->string('bill_to_phone')->nullable();
            $table->string('bill_to_address')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('discount_type')->default('flat'); // flat, percent
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(8);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('total_profit', 12, 2)->default(0);
            $table->decimal('profit_margin', 6, 2)->default(0);

            $table->timestamps();

            $table->index(['status', 'quotation_date']);
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('markup_percent', 6, 2)->default(15);
            $table->string('discount_type')->default('flat');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
