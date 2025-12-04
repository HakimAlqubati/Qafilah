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
        Schema::create('product_vendor_sku_units', function (Blueprint $table) {
            $table->id();

            // 🔗 العلاقات الأساسية
            $table->foreignId('product_vendor_sku_id')
                ->constrained('product_vendor_skus')
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnDelete();

            // 📦 معلومات الوحدة
            $table->integer('package_size')
                ->comment('عدد القطع في هذه الوحدة - مثل 12 قطعة في العلبة');

            // 💰 التسعير
            $table->decimal('cost_price', 10, 2)->nullable()
                ->comment('سعر تكلفة الوحدة الكاملة');

            $table->decimal('selling_price', 10, 2)
                ->comment('سعر بيع الوحدة الكاملة');

            // 📊 المخزون والطلب
            $table->integer('stock')->default(0)
                ->comment('المخزون المتوفر بهذه الوحدة');

            $table->integer('moq')->default(1)
                ->comment('الحد الأدنى للطلب بهذه الوحدة');

            // ⚙️ الحالة والإعدادات
            $table->boolean('is_default')->default(false)
                ->comment('هل هذه الوحدة الافتراضية للعرض والبيع؟');

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->integer('sort_order')->default(0)
                ->comment('ترتيب العرض');

            // 👥 تتبع المستخدمين
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->nullOnDelete();

            // 🕒 الحذف المنطقي والطوابع الزمنية
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['product_vendor_sku_id', 'unit_id'], 'unique_sku_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vendor_sku_units');
    }
};
