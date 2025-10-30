<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();

            // 🔗 العلاقات
            $table->foreignId('variant_id')
                  ->constrained('product_variants')
                  ->cascadeOnDelete();

            $table->foreignId('attribute_id')
                  ->constrained('attributes')
                  ->cascadeOnDelete();

            $table->foreignId('attribute_value_id')
                  ->constrained('attribute_values')
                  ->cascadeOnDelete();

            $table->timestamps();

            // منع تكرار نفس الخاصية للمتغير الواحد
            $table->unique(['variant_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
    }
};
