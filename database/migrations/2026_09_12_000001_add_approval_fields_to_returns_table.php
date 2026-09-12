<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->string('approval_status')->default('pending')->after('status'); // pending, approved, rejected — gates the Refunded transition
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // Returns already refunded before this workflow existed are treated as
        // already-approved, so the UI doesn't show completed refunds as pending.
        DB::table('returns')->where('status', 'refunded')->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approval_status', 'approved_at']);
        });
    }
};
