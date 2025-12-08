<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * المرحلة الأولى: دمج جدول product_set_attributes في product_attributes
     * - إضافة الأعمدة الجديدة
     * - نقل البيانات الموجودة
     */
    public function up(): void
    {
        // 1. إضافة الأعمدة الجديدة إلى product_attributes
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->boolean('is_variant_option')->default(false)->after('value');
            $table->unsignedInteger('sort_order')->nullable()->after('is_variant_option');
        });

        // 2. نقل البيانات من product_set_attributes إلى product_attributes
        $existingData = DB::table('product_set_attributes')->get();

        foreach ($existingData as $row) {
            // التحقق من وجود السجل مسبقاً
            $exists = DB::table('product_attributes')
                ->where('product_id', $row->product_id)
                ->where('attribute_id', $row->attribute_id)
                ->exists();

            if ($exists) {
                // تحديث السجل الموجود بالبيانات الإضافية
                DB::table('product_attributes')
                    ->where('product_id', $row->product_id)
                    ->where('attribute_id', $row->attribute_id)
                    ->update([
                        'is_variant_option' => $row->is_variant_option ?? false,
                        'sort_order' => $row->sort_order,
                    ]);
            } else {
                // إنشاء سجل جديد
                DB::table('product_attributes')->insert([
                    'product_id' => $row->product_id,
                    'attribute_id' => $row->attribute_id,
                    'value' => null,
                    'is_variant_option' => $row->is_variant_option ?? false,
                    'sort_order' => $row->sort_order,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropColumn(['is_variant_option', 'sort_order']);
        });
    }
};
