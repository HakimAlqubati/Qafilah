<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // العلاقات الرئيسية
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();

            // تفاصيل المنتج
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // الحالة
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->boolean('is_featured')->default(false);

            // تتبع المستخدمين (إن وُجدت علاقة users)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // الطوابع الزمنية + الحذف المنطقي
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
