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
        Schema::table('product_vendor_skus', function (Blueprint $table) {
            // إزالة الحقول المكررة - الآن في product_vendor_sku_units
            $table->dropColumn([
                'cost_price',
                'selling_price',
                'stock',
                'moq',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_vendor_skus', function (Blueprint $table) {
            // استعادة الحقول في حالة الرجوع
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('moq')->default(1);
        });
    }
};
