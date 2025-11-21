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
        Schema::create('product_vendor_skus', function (Blueprint $table) {
            $table->id();

            // 🔗 العلاقات
            $table->foreignId('variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            // � الأسعار والعملة
            $table->decimal('cost_price', 10, 2)->nullable(); // سعر التكلفة
            $table->decimal('selling_price', 10, 2);          // سعر البيع
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            // 📦 المخزون والعرض
            $table->string('vendor_sku')->nullable();   // SKU الخاص بالبائع
            $table->integer('stock')->nullable()->default(0);       // الكمية المتوفرة
            $table->integer('moq')->nullable()->default(1);         // أقل كمية للطلب
            $table->boolean('is_default_offer')->default(false); // هل هذا العرض الافتراضي؟
            $table->enum('status', ['available', 'out_of_stock', 'inactive'])->default('available');

            // 👥 تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // 🕒 الحذف المنطقي والزمنيات
            $table->softDeletes();
            $table->timestamps();

            // لا يمكن تكرار نفس البائع لنفس المتغير بنفس العملة
            $table->unique(['variant_id', 'vendor_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vendor_skus_table_v2');
    }
};
