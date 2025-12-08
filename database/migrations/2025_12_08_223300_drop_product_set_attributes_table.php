<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * المرحلة الثانية: حذف الجدول القديم product_set_attributes
     */
    public function up(): void
    {
        Schema::dropIfExists('product_set_attributes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_set_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();
            $table->boolean('is_variant_option')->default(true);
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_id']);
        });
    }
};
