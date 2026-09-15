<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The vendor a line was priced/sold at was already known at invoice-creation
     * time (Invoices\Create / Quotation::convertToInvoice) but never persisted —
     * needed now to restore the correct vendor's stock quantity on a return.
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};
