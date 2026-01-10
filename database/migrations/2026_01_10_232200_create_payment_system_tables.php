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
        // 1️⃣ جدول بوابات الدفع
        // ==========================================
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();

            // 📝 معلومات البوابة
            $table->string('name');
            $table->string('code')->unique()->index();

            // 🏷️ نوع البوابة
            $table->enum('type', [
                'electronic',   // دفع إلكتروني (API)
                'cash',         // دفع نقدي
                'transfer',     // تحويل بنكي
            ]);

            // 🔐 بيانات الاعتماد (مشفرة)
            $table->text('credentials')->nullable();

            // 📋 تعليمات الدفع
            $table->text('instructions')->nullable();

            // ⚙️ الإعدادات
            $table->boolean('is_active')->default(true);
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');

            // 🕒 الزمنيات
            $table->timestamps();
        });

        // ==========================================
        // 2️⃣ جدول معاملات الدفع
        // ==========================================
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // 🔗 العلاقات
            $table->foreignId('gateway_id')->constrained('payment_gateways')->cascadeOnDelete();
            $table->nullableMorphs('payable');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // 💰 بيانات المبلغ
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('YER');

            // 🔖 المراجع والإثباتات
            $table->string('reference_id')->nullable()->index();
            $table->string('proof_image')->nullable();

            // 📊 حالة المعاملة
            $table->enum('status', [
                'pending',      // في انتظار الدفع
                'paid',         // تم الدفع
                'failed',       // فشل الدفع
                'refunded',     // تم الاسترداد
                'reviewing',    // قيد المراجعة
            ])->default('pending')->index();

            // 📦 استجابة البوابة
            $table->json('gateway_response')->nullable();

            // 🕒 الزمنيات
            $table->timestamps();

            // 🔍 الفهارس
            $table->index(['gateway_id', 'status']);
            $table->index(['user_id', 'status']);
            // Note: nullableMorphs() already creates an index on payable_type and payable_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_gateways');
    }
};
