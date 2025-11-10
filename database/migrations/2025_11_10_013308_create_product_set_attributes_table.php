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
        Schema::create('product_set_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();

            // اختياري: هل هذه السمة ستظهر كخيار للمتغيرات أم مجرد وصف للمنتج؟
            $table->boolean('is_variant_option')->default(true);

            // اختياري: للترتيب في الواجهات
            $table->unsignedInteger('sort_order')->nullable();

            $table->timestamps();

            // منع التكرار لنفس السمة على نفس المنتج
            $table->unique(['product_id', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_set_attributes');
    }
};
