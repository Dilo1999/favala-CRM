<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20); // company, staff
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('period', 20); // day, week, month, year
            $table->date('period_start');

            $table->decimal('sales', 12, 2)->default(0);
            $table->unsignedInteger('quotations')->default(0);
            $table->unsignedInteger('deals')->default(0);
            $table->unsignedInteger('meetings')->default(0);
            $table->unsignedInteger('calls')->default(0);
            $table->unsignedInteger('site_visits')->default(0);
            $table->unsignedInteger('new_leads')->default(0);

            $table->timestamps();

            $table->unique(['scope', 'user_id', 'period', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
