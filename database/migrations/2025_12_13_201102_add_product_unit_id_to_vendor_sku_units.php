<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_vendor_sku_units', function (Blueprint $table) {
            // إضافة ربط مع ProductUnit
            $table->foreignId('product_unit_id')
                ->nullable()
                ->after('product_vendor_sku_id')
                ->constrained('product_units')
                ->restrictOnDelete();

            // Index للأداء
            $table->index('product_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_vendor_sku_units', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropIndex(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });
    }
};
