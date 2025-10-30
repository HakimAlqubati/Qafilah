<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeSet;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        /* ============================================================
         | 🔄 إعادة تهيئة الجداول (اختياري عند التطوير)
         |============================================================ */
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        Attribute::truncate();
        AttributeValue::truncate();
        AttributeSet::truncate();
        DB::table('attribute_set_attributes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /* ============================================================
         | 1️⃣ إنشاء بعض الخصائص (Attributes)
         |============================================================ */
        $attributesData = [
            ['code' => 'color', 'name' => 'Color', 'input_type' => 'radio', 'is_required' => true],
            ['code' => 'size', 'name' => 'Size', 'input_type' => 'select', 'is_required' => true],
            ['code' => 'brand', 'name' => 'Brand', 'input_type' => 'text', 'is_required' => false],
            ['code' => 'storage', 'name' => 'Storage', 'input_type' => 'select', 'is_required' => false],
            ['code' => 'ram', 'name' => 'RAM', 'input_type' => 'select', 'is_required' => false],
            ['code' => 'material', 'name' => 'Material', 'input_type' => 'text', 'is_required' => false],
            ['code' => 'warranty', 'name' => 'Warranty', 'input_type' => 'number', 'is_required' => false],
        ];

        $attributes = collect($attributesData)->map(fn($a) => Attribute::create($a));

        /* ============================================================
         | 1.1️⃣ إنشاء قيم الخصائص (Attribute Values)
         |============================================================ */
        $valuesMap = [
            'color'    => ['Red', 'Blue', 'Green', 'Black', 'White'],
            'size'     => ['XS', 'S', 'M', 'L', 'XL'],
            'brand'    => ['Nike', 'Adidas', 'Samsung', 'Apple', 'Dell'],
            'storage'  => ['64GB', '128GB', '256GB', '512GB'],
            'ram'      => ['4GB', '8GB', '16GB', '32GB'],
            'material' => ['Cotton', 'Polyester', 'Leather', 'Plastic'],
            'warranty' => ['1', '2', '3'],
        ];

        foreach ($valuesMap as $code => $values) {
            $attribute = Attribute::where('code', $code)->first();
            if ($attribute) {
                foreach ($values as $i => $val) {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $val,
                        'sort_order' => $i + 1,
                    ]);
                }
            }
        }

        /* ============================================================
         | 2️⃣ إنشاء Attribute Sets وربطها بالخصائص
         |============================================================ */
        $sets = [
            'Clothing Attributes' => ['color', 'size', 'brand', 'material'],
            'Electronics Attributes' => ['brand', 'storage', 'ram', 'warranty'],
            'Shoes Attributes' => ['color', 'size', 'brand'],
        ];

        foreach ($sets as $setName => $attributeCodes) {
            $set = AttributeSet::create([
                'name' => $setName,
                'description' => "Attribute set for {$setName}",
                'active' => true,
            ]);

            foreach ($attributeCodes as $code) {
                $attr = Attribute::where('code', $code)->first();
                if ($attr) {
                    DB::table('attribute_set_attributes')->insert([
                        'attribute_set_id' => $set->id,
                        'attribute_id' => $attr->id,
                        'is_required' => $attr->is_required,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        /* ============================================================
         | 3️⃣ إنشاء Categories وربطها بقوالب الخصائص
         |============================================================ */
        $categories = [
            ['name' => 'Clothing', 'attribute_set' => 'Clothing Attributes'],
            ['name' => 'Electronics', 'attribute_set' => 'Electronics Attributes'],
            ['name' => 'Shoes', 'attribute_set' => 'Shoes Attributes'],
        ];

        foreach ($categories as $cat) {
            $set = AttributeSet::where('name', $cat['attribute_set'])->first();
            Category::create([
                'name' => $cat['name'],
                'parent_id' => null,
                'attribute_set_id' => $set?->id,
                'active' => true,
            ]);
        }

        $this->command->info('✅ CatalogSeeder completed successfully with attribute values!');
    }
}
