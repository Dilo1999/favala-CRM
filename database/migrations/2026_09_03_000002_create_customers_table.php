<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('tin')->nullable();
            $table->foreignId('atoll_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('island_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('lead_source')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new'); // new, potential, not_qualified, customer
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_name', 'phone', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
