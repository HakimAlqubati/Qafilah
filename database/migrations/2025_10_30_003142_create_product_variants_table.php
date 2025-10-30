<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            // 🔗 العلاقة مع المنتج الأساسي
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // 🧩 تفاصيل المتغير
            $table->string('master_sku')->unique();            // SKU فريد لكل متغير
            $table->string('barcode')->nullable();             // باركود (اختياري)
            $table->decimal('weight', 10, 2)->nullable();      // الوزن (اختياري)
            $table->json('dimensions')->nullable();            // الأبعاد (الطول/العرض/الارتفاع)

            // ⚙️ الحالة
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->boolean('is_default')->default(false);

            // 👥 تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // 🕒 الحذف المنطقي والزمنيات
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
