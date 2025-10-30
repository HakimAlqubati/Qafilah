<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        return;
        Schema::create('product_vendor_skus', function (Blueprint $table) {
            $table->id();

            // 🔗 العلاقات
            $table->foreignId('variant_id')
                  ->constrained('product_variants')
                  ->cascadeOnDelete();

            $table->foreignId('vendor_id')
                  ->constrained('vendors')
                  ->cascadeOnDelete();

            // 📦 تفاصيل العرض
            $table->string('vendor_sku')->nullable();   // SKU الخاص بالبائع
            $table->boolean('is_default_offer')->default(false); // هل هذا العرض الافتراضي في السوق؟
            $table->enum('status', ['available', 'out_of_stock', 'inactive'])->default('available');

            // 👥 تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // 🕒 الحذف المنطقي والزمنيات
            $table->softDeletes();
            $table->timestamps();

            // لا يمكن تكرار نفس البائع لنفس المتغير
            $table->unique(['variant_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_vendor_skus');
    }
};
