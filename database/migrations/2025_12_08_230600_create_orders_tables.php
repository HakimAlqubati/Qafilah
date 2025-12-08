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
        // ==========================================
        // 1️⃣ جدول الطلبات الرئيسي
        // ==========================================
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            // 🔗 العلاقات
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();

            // 📊 الحالات
            $table->enum('status', [
                'pending',      // قيد الانتظار
                'confirmed',    // تم التأكيد
                'processing',   // قيد المعالجة
                'shipped',      // تم الشحن
                'delivered',    // تم التوصيل
                'completed',    // مكتمل
                'cancelled',    // ملغي
                'returned',     // مرتجع
            ])->default('pending');

            $table->enum('payment_status', [
                'pending',      // في انتظار الدفع
                'partial',      // دفع جزئي
                'paid',         // مدفوع
                'refunded',     // تم الاسترداد
            ])->default('pending');

            $table->enum('shipping_status', [
                'pending',      // في انتظار الشحن
                'preparing',    // قيد التجهيز
                'shipped',      // تم الشحن
                'in_transit',   // في الطريق
                'delivered',    // تم التوصيل
            ])->default('pending');

            // 💰 المبالغ
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // 📍 العناوين
            $table->foreignId('shipping_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->foreignId('billing_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();

            // 📝 ملاحظات
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // 📅 التواريخ
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // 👥 تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // 🕒 الزمنيات والحذف المنطقي
            $table->timestamps();
            $table->softDeletes();

            // 🔍 الفهارس
            $table->index(['status', 'created_at']);
            $table->index(['customer_id', 'status']);
            $table->index(['vendor_id', 'status']);
        });

        // ==========================================
        // 2️⃣ جدول بنود الطلب
        // ==========================================
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // 🔗 علاقات المنتج
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('product_vendor_sku_id')->nullable()->constrained('product_vendor_skus')->nullOnDelete();
            $table->foreignId('product_vendor_sku_unit_id')->nullable()->constrained('product_vendor_sku_units')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();

            // 📦 بيانات المنتج (محفوظة وقت الطلب)
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('package_size')->default(1);

            // 🔢 الكمية والأسعار
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // 📝 ملاحظات
            $table->text('notes')->nullable();

            $table->timestamps();

            // 🔍 الفهارس
            $table->index(['order_id', 'product_id']);
        });

        // ==========================================
        // 3️⃣ جدول سجل حالات الطلب
        // ==========================================
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->string('status');
            $table->text('comment')->nullable();

            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // 🔍 الفهارس
            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
