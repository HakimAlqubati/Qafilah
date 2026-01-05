<?php

namespace App\Services\DocsReports;

class DefaultUnitReportService
{
    public function getReport(): array
    {
        return [
            'title' => 'تقرير: إضافة حقل الوحدة الافتراضية',
            'date' => '2026-01-03',
            'summary' => 'إضافة حقل is_default لتحديد وحدة افتراضية على مستوى النظام',

            'sections' => [
                [
                    'title' => 'ما هي الميزة؟',
                    'icon' => 'lightbulb',
                    'content' => 'تم إضافة حقل is_default لجدول الوحدات (units) لتحديد وحدة افتراضية على مستوى النظام. عندما يقوم التاجر بإنشاء SKU لمنتج ليس له وحدات محددة مسبقاً في جدول product_units، النظام يستخدم هذه الوحدة الافتراضية تلقائياً.',
                ],
                [
                    'title' => 'الملفات المُعدَّلة',
                    'icon' => 'code',
                    'files' => [
                        [
                            'category' => 'Migration',
                            'items' => [
                                [
                                    'file' => 'database/migrations/2026_01_03_192333_add_is_default_to_units_table.php',
                                    'change' => 'إضافة حقل is_default من نوع boolean بقيمة افتراضية false',
                                ],
                            ],
                        ],
                        [
                            'category' => 'Models',
                            'items' => [
                                [
                                    'file' => 'app/Models/Unit.php',
                                    'change' => 'إضافة is_default للـ $fillable و cast للـ boolean',
                                ],
                                [
                                    'file' => 'app/Models/ProductUnit.php',
                                    'change' => 'إضافة makeDefaultForProduct() و getAvailableUnitsForProduct() لاستخدام الوحدة الافتراضية',
                                ],
                            ],
                        ],
                        [
                            'category' => 'Filament Components',
                            'items' => [
                                [
                                    'file' => 'UnitsRepeater.php',
                                    'change' => 'التحقق من is_default لتحديد سلوك الـ repeater (إظهار/إخفاء)',
                                ],
                                [
                                    'file' => 'ProductVendorSkusTable.php',
                                    'change' => 'استخدام الوحدة الافتراضية لعرض السعر في الجدول',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'كيف تعمل الميزة؟',
                    'icon' => 'flow',
                    'steps' => [
                        'عند إنشاء SKU جديد، النظام يتحقق من وحدات المنتج في product_units',
                        'إذا لم يكن للمنتج وحدات محددة، يستدعي ProductUnit::getAvailableUnitsForProduct()',
                        'هذه الدالة تستدعي makeDefaultForProduct() التي تجلب الوحدة المحددة كـ is_default = true',
                        'يتم إنشاء ProductUnit "وهمي" (بدون حفظ) لاستخدامه في النموذج',
                        'عند الحفظ، يتم إنشاء ProductVendorSkuUnit بهذه الوحدة الافتراضية',
                    ],
                ],
                [
                    'title' => 'الغرض من الميزة',
                    'icon' => 'target',
                    'points' => [
                        'تسهيل عملية إضافة المنتجات للتاجر بدون الحاجة لتعريف وحدات مسبقاً',
                        'ضمان وجود وحدة واحدة على الأقل لكل SKU',
                        'توحيد تجربة المستخدم عند إنشاء منتجات جديدة',
                    ],
                ],
            ],
        ];
    }
}
