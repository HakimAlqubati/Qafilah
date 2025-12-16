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
            // جعل variant_id اختياري للمنتجات البسيطة
            $table->foreignId('variant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_vendor_skus', function (Blueprint $table) {
            // إرجاع variant_id إلى required
            $table->foreignId('variant_id')->nullable(false)->change();
        });
    }
};
