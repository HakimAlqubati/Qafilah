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
            // إضافة variant_id لربط السعر بمتغير معين
            $table->foreignId('variant_id')
                ->nullable()
                ->after('product_unit_id')
                ->constrained('product_variants')
                ->nullOnDelete();

            // Index للأداء
            $table->index('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_vendor_sku_units', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropIndex(['variant_id']);
            $table->dropColumn('variant_id');
        });
    }
};
