<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $connection = 'shop_catalog';

    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('unit_of_measure');
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
