<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('invoice_date');
            $table->date('expiry_date')->nullable();

            $table->string('bill_to_name')->nullable();
            $table->string('bill_to_phone')->nullable();
            $table->string('bill_to_address')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('discount_type')->default('flat');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(8);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, partial, paid

            $table->timestamps();

            $table->index(['payment_status', 'invoice_date']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('rate', 12, 2)->default(0);
            $table->string('discount_type')->default('flat');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
