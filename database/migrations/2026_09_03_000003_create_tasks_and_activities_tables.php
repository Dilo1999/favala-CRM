<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Call, Meeting, Email, Site Visit
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deadline')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, completed
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_on')->nullable();
            $table->timestamps();

            $table->index(['status', 'deadline']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('outcome')->nullable();
            $table->text('details')->nullable();
            $table->string('status')->default('follow_up'); // follow_up, closed
            $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('tasks');
    }
};
