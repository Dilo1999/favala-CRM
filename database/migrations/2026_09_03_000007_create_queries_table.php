<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->string('source')->default('manual'); // deal, quotation, manual
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('status')->default('new'); // new, negotiating, completed, dead
            $table->boolean('follow_up')->default(false);
            $table->string('query_source')->nullable();
            $table->string('query_type')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'follow_up']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queries');
    }
};
