<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            // Guards the invoice-adjustment logic against double-applying (or
            // never reversing) the financial effect when status is toggled.
            $table->timestamp('refund_applied_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropColumn('refund_applied_at');
        });
    }
};
