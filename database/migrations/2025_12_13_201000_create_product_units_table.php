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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();

            // معلومات التحويل
            $table->integer('package_size')->default(1)->comment('عدد القطع في الوحدة');
            $table->decimal('conversion_factor', 10, 4)->default(1.0000)->comment('معامل التحويل للوحدة الأساسية');

            // الوحدة الأساسية
            $table->boolean('is_base_unit')->default(false)->comment('هل هذه الوحدة الأساسية للمنتج');

            // هل يمكن البيع بهذه الوحدة
            $table->boolean('is_sellable')->default(true)->comment('هل يمكن بيع المنتج بهذه الوحدة');

            // الترتيب
            $table->integer('sort_order')->default(0);

            // الحالة
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: منتج واحد لا يمكن أن يكون له نفس الوحدة مرتين
            $table->unique(['product_id', 'unit_id']);

            // Indexes للأداء
            $table->index(['product_id', 'is_base_unit']);
            $table->index(['product_id', 'is_sellable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
