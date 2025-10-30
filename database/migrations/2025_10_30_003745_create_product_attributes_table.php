<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();

            // 🔗 العلاقة مع المنتج
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // 🔗 الخاصية العامة (attribute)
            $table->foreignId('attribute_id')
                  ->constrained('attributes')
                  ->cascadeOnDelete();

            // 🧩 القيمة المخزنة (إما نص أو رقم حسب نوع الخاصية)
            $table->string('value')->nullable();

            // 🕒 الزمنيات
            $table->timestamps();

            // منع تكرار نفس الخاصية لنفس المنتج
            $table->unique(['product_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
