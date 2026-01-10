<?php

namespace App\Services\DocsReports;

class DefaultUnitReportService
{
    public function getReport(): array
    {
        return [
            'title' => 'تقرير: حقل الوحدة الافتراضية (is_default)',
            'date' => '2026-01-03',
            'summary' => 'وحدة تُختار تلقائياً عند إنشاء منتج/SKU جديد - غير إجباري',

            'sections' => [
                [
                    'title' => 'ما هي الميزة؟',
                    'icon' => 'lightbulb',
                    'content' => 'عند إضافة منتج جديد (من الأدمن) أو إنشاء SKU جديد (من التاجر)، يتم اختيار الوحدة المحددة كـ is_default تلقائياً. التاجر/الأدمن حر في تغييرها أو إضافة وحدات أخرى.',
                ],
                [
                    'title' => 'الملفات المُعدَّلة',
                    'icon' => 'code',
                    'files' => [
                        [
                            'category' => 'Migration',
                            'items' => [
                                [
                                    'file' => 'add_is_default_to_units_table.php',
                                    'change' => 'إضافة حقل is_default boolean (افتراضي: false)',
                                ],
                            ],
                        ],
                        [
                            'category' => 'Models',
                            'items' => [
                                [
                                    'file' => 'Unit.php',
                                    'change' => 'إضافة is_default للـ $fillable و cast',
                                ],
                            ],
                        ],
                        [
                            'category' => 'Filament (التاجر)',
                            'items' => [
                                [
                                    'file' => 'UnitsRepeater.php',
                                    'change' => 'اختيار الوحدة الافتراضية تلقائياً عند إنشاء SKU',
                                ],
                                [
                                    'file' => 'VariantsUnitsRepeater.php',
                                    'change' => 'نفس المنطق للمنتجات ذات المتغيرات',
                                ],
                            ],
                        ],
                        [
                            'category' => 'Filament (الأدمن)',
                            'items' => [
                                [
                                    'file' => 'ProductUnitsStep.php',
                                    'change' => 'اختيار الوحدة الافتراضية تلقائياً عند إضافة منتج',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'الفائدة',
                    'icon' => 'target',
                    'points' => [
                        'تسهيل وتسريع عملية الإضافة',
                        'توحيد الوحدة المستخدمة افتراضياً',
                        'غير إجباري - يمكن التغيير بحرية',
                    ],
                ],
            ],
        ];
    }
}
