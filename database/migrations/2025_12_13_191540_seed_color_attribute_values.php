<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // الحصول على ID الخاصية "color"
        $colorAttribute = DB::table('attributes')->where('code', 'color')->first();

        if (!$colorAttribute) {
            return; // إذا لم تكن الخاصية موجودة، لا تفعل شيء
        }

        // حذف جميع القيم الحالية للخاصية color
        DB::table('attribute_values')
            ->where('attribute_id', $colorAttribute->id)
            ->delete();

        // إضافة الألوان الأساسية مع أكوادها
        $colors = [
            // الألوان الأساسية
            ['value' => 'أحمر', 'code' => '#FF0000', 'sort_order' => 1],
            ['value' => 'أزرق', 'code' => '#0000FF', 'sort_order' => 2],
            ['value' => 'أخضر', 'code' => '#00FF00', 'sort_order' => 3],
            ['value' => 'أصفر', 'code' => '#FFFF00', 'sort_order' => 4],
            ['value' => 'برتقالي', 'code' => '#FFA500', 'sort_order' => 5],
            ['value' => 'بنفسجي', 'code' => '#800080', 'sort_order' => 6],
            ['value' => 'وردي', 'code' => '#FFC0CB', 'sort_order' => 7],
            ['value' => 'بني', 'code' => '#A52A2A', 'sort_order' => 8],

            // ألوان محايدة
            ['value' => 'أبيض', 'code' => '#FFFFFF', 'sort_order' => 9],
            ['value' => 'أسود', 'code' => '#000000', 'sort_order' => 10],
            ['value' => 'رمادي', 'code' => '#808080', 'sort_order' => 11],
            ['value' => 'رمادي فاتح', 'code' => '#D3D3D3', 'sort_order' => 12],
            ['value' => 'رمادي غامق', 'code' => '#A9A9A9', 'sort_order' => 13],

            // ألوان إضافية شائعة
            ['value' => 'سماوي', 'code' => '#87CEEB', 'sort_order' => 14],
            ['value' => 'فيروزي', 'code' => '#40E0D0', 'sort_order' => 15],
            ['value' => 'كحلي', 'code' => '#000080', 'sort_order' => 16],
            ['value' => 'ذهبي', 'code' => '#FFD700', 'sort_order' => 17],
            ['value' => 'فضي', 'code' => '#C0C0C0', 'sort_order' => 18],
            ['value' => 'زيتي', 'code' => '#808000', 'sort_order' => 19],
            ['value' => 'كريمي', 'code' => '#FFFDD0', 'sort_order' => 20],
            ['value' => 'بيج', 'code' => '#F5F5DC', 'sort_order' => 21],

            // ألوان داكنة
            ['value' => 'أحمر داكن', 'code' => '#8B0000', 'sort_order' => 22],
            ['value' => 'أزرق داكن', 'code' => '#00008B', 'sort_order' => 23],
            ['value' => 'أخضر داكن', 'code' => '#006400', 'sort_order' => 24],

            // ألوان فاتحة
            ['value' => 'أحمر فاتح', 'code' => '#FF6B6B', 'sort_order' => 25],
            ['value' => 'أزرق فاتح', 'code' => '#ADD8E6', 'sort_order' => 26],
            ['value' => 'أخضر فاتح', 'code' => '#90EE90', 'sort_order' => 27],

            // ألوان عصرية
            ['value' => 'نيلي', 'code' => '#4B0082', 'sort_order' => 28],
            ['value' => 'أرجواني', 'code' => '#DA70D6', 'sort_order' => 29],
            ['value' => 'مرجاني', 'code' => '#FF7F50', 'sort_order' => 30],
            ['value' => 'خوخي', 'code' => '#FFE5B4', 'sort_order' => 31],
            ['value' => 'ليموني', 'code' => '#FFFACD', 'sort_order' => 32],
        ];

        // إضافة الألوان إلى قاعدة البيانات
        $now = now();
        foreach ($colors as $color) {
            DB::table('attribute_values')->insert([
                'attribute_id' => $colorAttribute->id,
                'value' => $color['value'],
                'code' => $color['code'],
                'sort_order' => $color['sort_order'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // الحصول على ID الخاصية "color"
        $colorAttribute = DB::table('attributes')->where('code', 'color')->first();

        if (!$colorAttribute) {
            return;
        }

        // حذف جميع قيم الألوان المضافة
        DB::table('attribute_values')
            ->where('attribute_id', $colorAttribute->id)
            ->delete();
    }
};
