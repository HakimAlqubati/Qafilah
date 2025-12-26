<?php

namespace App\Services\DocsReports;

class BranchProposalsService
{
    public function getProposals(): array
    {
        return [
            'title' => 'التعديلات المطلوبة بعد إضافة نظام الفروع للتجار',
            'date' => now()->format('Y-m-d'),
            'sections' => [
                [
                    'title' => 'المنتجات',
                    'question' => 'هل المنتجات مشتركة بين الفروع أم منفصلة؟',
                    'smartSuggestion' => [
                        'title' => 'اقتراح ذكي',
                        'description' => 'يُفضل إضافة إعداد في لوحة التحكم يسمح لمسؤول المتجر باختيار طريقة إدارة المنتجات.',
                        'details' => [
                            'إنشاء إعداد: branch_product_mode (shared / separate)',
                            'يظهر في صفحة إعدادات التاجر',
                            'يتم تطبيق المنطق المناسب تلقائياً حسب الاختيار',
                            'مرونة كاملة للتاجر دون الحاجة لتعديل الكود',
                        ],
                    ],
                    'options' => [
                        [
                            'title' => 'مشتركة: جميع الفروع تعرض نفس المنتجات',
                            'steps' => [
                                [
                                    'action' => 'تعديل ProductVendorSku',
                                    'file' => 'app/Models/ProductVendorSku.php',
                                    'details' => 'إضافة حقل branch_id (nullable) للسماح للفرع بتخصيص السعر/المخزون',
                                ],
                                [
                                    'action' => 'تعديل MerchantPanel Query',
                                    'file' => 'app/Filament/Merchant/Resources/ProductVendorSkus',
                                    'details' => 'عرض منتجات الـ parent (المركز الرئيسي) + منتجات الفرع الحالي',
                                ],
                            ],
                        ],
                        [
                            'title' => 'منفصلة: كل فرع له منتجاته الخاصة',
                            'steps' => [
                                [
                                    'action' => 'لا تعديل مطلوب',
                                    'file' => '-',
                                    'details' => 'النظام الحالي يعمل بهذا الشكل. كل vendor_id (سواء رئيسي أو فرع) له منتجاته',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'المستخدمين',
                    'question' => 'هل موظف الفرع يرى فرعه فقط أم كل الفروع؟',
                    'smartSuggestion' => [
                        'title' => 'اقتراح ذكي',
                        'description' => 'إضافة صلاحية "إدارة جميع الفروع" يمكن تفعيلها لمستخدمين محددين.',
                        'details' => [
                            'إنشاء Permission: manage_all_branches',
                            'ربطها بنظام Spatie Permissions الموجود',
                            'موظفي المركز الرئيسي يحصلون عليها تلقائياً',
                            'يمكن منحها لأي مستخدم حسب الحاجة',
                        ],
                    ],
                    'options' => [
                        [
                            'title' => 'فرعه فقط (الوضع الحالي)',
                            'steps' => [
                                [
                                    'action' => 'التحقق من Middleware',
                                    'file' => 'app/Http/Middleware/CustomFilamentAuthenticate.php',
                                    'details' => 'حالياً يتحقق من vendor_id فقط - يعمل بشكل صحيح',
                                ],
                            ],
                        ],
                        [
                            'title' => 'موظف المركز يرى كل الفروع',
                            'steps' => [
                                [
                                    'action' => 'تعديل User Model',
                                    'file' => 'app/Models/User.php',
                                    'details' => 'إضافة method: canAccessBranch($branchId) يتحقق إذا كان المستخدم تابع للمركز الرئيسي',
                                ],
                                [
                                    'action' => 'إضافة Branch Switcher',
                                    'file' => 'resources/views/filament/partials/branch-switcher.blade.php',
                                    'details' => 'قائمة منسدلة في الـ header للتنقل بين الفروع',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'الطلبات',
                    'question' => 'كيف يتم ربط الطلب بفرع معين؟',
                    'smartSuggestion' => [
                        'title' => 'اقتراح ذكي',
                        'description' => 'إضافة إعداد branch_selection_mode يحدد طريقة اختيار الفرع عند الطلب.',
                        'details' => [
                            'إنشاء إعداد: branch_selection_mode (auto / manual / hybrid)',
                            'auto: النظام يختار أقرب فرع تلقائياً',
                            'manual: العميل يختار الفرع بنفسه',
                            'hybrid: اقتراح تلقائي مع إمكانية التغيير',
                        ],
                    ],
                    'options' => [
                        [
                            'title' => 'تلقائياً حسب موقع العميل',
                            'steps' => [
                                [
                                    'action' => 'Migration للطلبات',
                                    'file' => 'database/migrations/add_branch_id_to_orders.php',
                                    'details' => 'إضافة branch_id (nullable) في جدول orders',
                                ],
                                [
                                    'action' => 'إنشاء Service جديد',
                                    'file' => 'app/Services/NearestBranchService.php',
                                    'details' => 'method: findNearest($vendorId, $customerLat, $customerLng) يحسب المسافة ويرجع أقرب فرع',
                                ],
                            ],
                        ],
                        [
                            'title' => 'العميل يختار الفرع',
                            'steps' => [
                                [
                                    'action' => 'إضافة API Endpoint',
                                    'file' => 'routes/api.php',
                                    'details' => 'GET /vendors/{id}/branches - يرجع قائمة الفروع النشطة',
                                ],
                                [
                                    'action' => 'تعديل واجهة الطلب',
                                    'file' => 'Frontend (Mobile/Web)',
                                    'details' => 'إضافة dropdown لاختيار الفرع قبل إتمام الطلب',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'التوصيل',
                    'question' => 'من أي موقع يتم حساب تكلفة التوصيل؟',
                    'smartSuggestion' => [
                        'title' => 'اقتراح ذكي',
                        'description' => 'إضافة إعداد delivery_calculation_source لتحديد مصدر حساب التوصيل.',
                        'details' => [
                            'إنشاء إعداد: delivery_calculation_source (nearest_branch / main_office / selected_branch)',
                            'nearest_branch: حساب من أقرب فرع للعميل',
                            'main_office: حساب من المركز الرئيسي دائماً',
                            'selected_branch: حساب من الفرع المختار في الطلب',
                        ],
                    ],
                    'options' => [
                        [
                            'title' => 'من أقرب فرع للعميل',
                            'steps' => [
                                [
                                    'action' => 'تعديل calculateDeliveryCost',
                                    'file' => 'app/Models/Vendor.php',
                                    'details' => 'Method الحالي يستخدم lat/lng التاجر. نحتاج نمرر له branch_id أو نستخدم أقرب فرع تلقائياً',
                                ],
                                [
                                    'action' => 'استخدام موقع الفرع',
                                    'file' => 'app/Services/DeliveryCalculatorService.php',
                                    'details' => 'إنشاء service جديد يأخذ بعين الاعتبار مواقع الفروع',
                                ],
                            ],
                        ],
                        [
                            'title' => 'من المركز الرئيسي دائماً',
                            'steps' => [
                                [
                                    'action' => 'لا تعديل مطلوب',
                                    'file' => 'app/Models/Vendor.php',
                                    'details' => 'calculateDeliveryCost يستخدم lat/lng من التاجر الأصلي (يمكن استخدام parent إذا كان فرع)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
