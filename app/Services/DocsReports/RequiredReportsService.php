<?php

namespace App\Services\DocsReports;

class RequiredReportsService
{
    public function getReports(): array
    {
        return [
            'title' => 'التقارير المطلوبة للنظام',
            'date' => now()->format('Y-m-d'),
            'sections' => [
                [
                    'title' => 'تقرير المبيعات',
                    'question' => 'ما هي البيانات التي يجب تتبعها في المبيعات؟',
                    'icon' => 'chart-bar',
                    'priority' => 'high',
                    'description' => 'تقرير شامل لتتبع إجمالي المبيعات والإيرادات حسب الفترة الزمنية',
                    'details' => [
                        'الهدف' => 'تتبع إجمالي المبيعات وتحليل الأداء البيعي خلال فترات زمنية محددة',
                        'مصادر البيانات' => 'جدول orders, order_items',
                        'الحقول المستخدمة' => 'orders.total, orders.placed_at, orders.status, orders.vendor_id, orders.customer_id',
                    ],
                    'filters' => [
                        'التاريخ (من - إلى)',
                        'حالة الطلب (مكتمل، ملغي، قيد المعالجة)',
                        'البائع / المورد',
                        'العميل',
                        'المنتج أو الفئة',
                    ],
                    'outputs' => [
                        'إجمالي المبيعات (المبلغ الإجمالي)',
                        'عدد الطلبات',
                        'متوسط قيمة الطلب',
                        'نسبة التغيير مقارنة بالفترة السابقة',
                        'رسم بياني للمبيعات اليومية/الأسبوعية/الشهرية',
                    ],
                    'implementationSteps' => [
                        [
                            'action' => 'إنشاء Service للتقرير',
                            'file' => 'app/Services/Reports/SalesReportService.php',
                            'details' => 'إنشاء class يحتوي على methods لجلب البيانات وتجميعها حسب الفلاتر',
                        ],
                        [
                            'action' => 'إنشاء Controller',
                            'file' => 'app/Http/Controllers/Reports/SalesReportController.php',
                            'details' => 'واجهة API أو Web لعرض التقرير مع إمكانية التصدير',
                        ],
                        [
                            'action' => 'إنشاء View أو API Resource',
                            'file' => 'resources/views/reports/sales-report.blade.php',
                            'details' => 'واجهة عرض التقرير مع جداول ورسوم بيانية',
                        ],
                    ],
                ],
                [
                    'title' => 'تقرير أداء الموردين',
                    'question' => 'كيف نقيس أداء كل مورد/بائع؟',
                    'icon' => 'users',
                    'priority' => 'high',
                    'description' => 'تقييم شامل لأداء كل بائع من حيث المبيعات والمنتجات',
                    'details' => [
                        'الهدف' => 'تقييم أداء كل بائع وترتيبهم حسب المبيعات والنشاط',
                        'مصادر البيانات' => 'جدول vendors, orders, products, order_items',
                        'الحقول المستخدمة' => 'vendors.id, vendors.name, orders.total, orders.status, products count',
                    ],
                    'filters' => [
                        'الفترة الزمنية',
                        'الحالة (نشط/غير نشط)',
                        'المنطقة الجغرافية (المدينة/البلد)',
                        'نوع البائع (رئيسي/فرع)',
                    ],
                    'outputs' => [
                        'ترتيب البائعين حسب إجمالي المبيعات',
                        'عدد الطلبات لكل بائع',
                        'عدد المنتجات النشطة لكل بائع',
                        'معدل إلغاء الطلبات',
                        'متوسط قيمة الطلب لكل بائع',
                        'أفضل المنتجات مبيعاً لكل بائع',
                    ],
                    'implementationSteps' => [
                        [
                            'action' => 'إنشاء Service للتقرير',
                            'file' => 'app/Services/Reports/VendorPerformanceService.php',
                            'details' => 'class يجمع إحصائيات البائعين مع إمكانية الترتيب والفلترة',
                        ],
                        [
                            'action' => 'إضافة methods في Vendor Model',
                            'file' => 'app/Models/Vendor.php',
                            'details' => 'getTotalSalesAmount() موجود مسبقاً - إضافة المزيد من helpers',
                        ],
                        [
                            'action' => 'إنشاء Filament Widget',
                            'file' => 'app/Filament/Widgets/VendorPerformanceWidget.php',
                            'details' => 'عرض الإحصائيات في لوحة التحكم',
                        ],
                    ],
                ],
                [
                    'title' => 'تقرير أفضل المنتجات مبيعاً',
                    'question' => 'ما هي المنتجات الأكثر والأقل مبيعاً؟',
                    'icon' => 'star',
                    'priority' => 'high',
                    'description' => 'تحليل المنتجات لمعرفة الأفضل والأسوأ أداءً',
                    'details' => [
                        'الهدف' => 'معرفة المنتجات الأكثر مبيعاً والأقل مبيعاً لاتخاذ قرارات استراتيجية',
                        'مصادر البيانات' => 'جدول products, order_items, product_variants',
                        'الحقول المستخدمة' => 'products.id, products.name, order_items.quantity, order_items.total',
                    ],
                    'filters' => [
                        'الفترة الزمنية',
                        'الفئة (Category)',
                        'البائع (Vendor)',
                        'حالة المنتج (نشط/غير نشط)',
                        'عدد النتائج (Top 10, 20, 50)',
                    ],
                    'outputs' => [
                        'ترتيب المنتجات حسب الكمية المباعة',
                        'ترتيب المنتجات حسب الإيرادات',
                        'المنتجات الأقل مبيعاً (للمراجعة)',
                        'نسبة مساهمة كل منتج في إجمالي المبيعات',
                        'رسم بياني دائري لأفضل 10 منتجات',
                    ],
                    'implementationSteps' => [
                        [
                            'action' => 'استخدام Method الموجود',
                            'file' => 'app/Models/Product.php',
                            'details' => 'getTotalSoldQuantity() موجود مسبقاً - يمكن تحسينه',
                        ],
                        [
                            'action' => 'إنشاء Service للتقرير',
                            'file' => 'app/Services/Reports/TopProductsService.php',
                            'details' => 'class يرتب المنتجات حسب المبيعات مع فلاتر متعددة',
                        ],
                        [
                            'action' => 'إنشاء View مع Charts',
                            'file' => 'resources/views/reports/top-products.blade.php',
                            'details' => 'عرض جدول + رسم بياني للمنتجات الأفضل',
                        ],
                    ],
                ],
                [
                    'title' => 'تقرير العملاء',
                    'question' => 'كيف نحلل سلوك العملاء وقيمتهم؟',
                    'icon' => 'user-group',
                    'priority' => 'medium',
                    'description' => 'تحليل شامل لسلوك العملاء وقيمة كل عميل',
                    'details' => [
                        'الهدف' => 'فهم سلوك العملاء وتحديد العملاء الأكثر قيمة وتكرار الشراء',
                        'مصادر البيانات' => 'جدول customers, orders, customer_addresses',
                        'الحقول المستخدمة' => 'customers.id, customers.name, customers.credit_limit, orders.total, orders count',
                    ],
                    'filters' => [
                        'الفترة الزمنية',
                        'حالة العميل (نشط/غير نشط)',
                        'حد الائتمان (أعلى من، أقل من)',
                        'المنطقة الجغرافية',
                        'عدد الطلبات (أكثر من، أقل من)',
                    ],
                    'outputs' => [
                        'ترتيب العملاء حسب إجمالي المشتريات',
                        'عدد الطلبات لكل عميل',
                        'متوسط قيمة الطلب لكل عميل',
                        'الرصيد المستخدم من حد الائتمان',
                        'آخر تاريخ طلب',
                        'تكرار الشراء (الفترة بين الطلبات)',
                    ],
                    'implementationSteps' => [
                        [
                            'action' => 'استخدام Methods الموجودة',
                            'file' => 'app/Models/Customer.php',
                            'details' => 'getTotalOrdersAmount() و getOrdersCount() موجودة مسبقاً',
                        ],
                        [
                            'action' => 'إنشاء Service للتقرير',
                            'file' => 'app/Services/Reports/CustomerAnalyticsService.php',
                            'details' => 'class يجمع إحصائيات العملاء مع حساب CLV (Customer Lifetime Value)',
                        ],
                        [
                            'action' => 'إنشاء Filament Page',
                            'file' => 'app/Filament/Pages/CustomerAnalytics.php',
                            'details' => 'صفحة تحليلات العملاء في لوحة التحكم',
                        ],
                    ],
                ],
                [
                    'title' => 'تقرير المدفوعات والتحصيل',
                    'question' => 'كيف نتتبع حالة المدفوعات والمستحقات؟',
                    'icon' => 'credit-card',
                    'priority' => 'high',
                    'description' => 'تتبع شامل لحالة المدفوعات والمبالغ المستحقة',
                    'details' => [
                        'الهدف' => 'تتبع حالة المدفوعات ومعرفة المبالغ المحصلة والمستحقة',
                        'مصادر البيانات' => 'جدول payment_transactions, orders, payment_gateways',
                        'الحقول المستخدمة' => 'payment_transactions.amount, payment_transactions.status, payment_transactions.gateway_id',
                    ],
                    'filters' => [
                        'الفترة الزمنية',
                        'حالة الدفع (مدفوع، معلق، مرفوض، قيد المراجعة)',
                        'بوابة الدفع',
                        'العملة',
                        'العميل / البائع',
                    ],
                    'outputs' => [
                        'إجمالي المبالغ المحصلة',
                        'إجمالي المبالغ المستحقة (غير مدفوعة)',
                        'عدد المعاملات حسب الحالة',
                        'توزيع المدفوعات حسب بوابة الدفع',
                        'المعاملات الفاشلة والمرفوضة',
                        'تقرير الاسترداد (Refunds)',
                    ],
                    'implementationSteps' => [
                        [
                            'action' => 'إنشاء Service للتقرير',
                            'file' => 'app/Services/Reports/PaymentReportService.php',
                            'details' => 'class يجمع إحصائيات المدفوعات والتحصيل',
                        ],
                        [
                            'action' => 'استخدام Model Scopes الموجودة',
                            'file' => 'app/Models/PaymentTransaction.php',
                            'details' => 'الـ scopes موجودة: pending(), paid(), failed(), refunded()',
                        ],
                        [
                            'action' => 'إنشاء Dashboard Widget',
                            'file' => 'app/Filament/Widgets/PaymentSummaryWidget.php',
                            'details' => 'widget يعرض ملخص المدفوعات في الصفحة الرئيسية',
                        ],
                    ],
                ],
                [
                    'title' => 'تقارير إضافية مقترحة',
                    'question' => 'ما هي التقارير الأخرى التي قد تحتاجها؟',
                    'icon' => 'document-plus',
                    'priority' => 'low',
                    'description' => 'تقارير إضافية يمكن تطويرها حسب الحاجة',
                    'details' => [
                        'الهدف' => 'توفير تقارير متخصصة لحالات استخدام محددة',
                        'مصادر البيانات' => 'جميع جداول النظام',
                        'الحقول المستخدمة' => 'حسب نوع التقرير',
                    ],
                    'additionalReports' => [
                        [
                            'name' => 'تقرير الطلبات الملغاة',
                            'description' => 'تحليل أسباب الإلغاء ونسبتها',
                            'priority' => 'متوسط',
                        ],
                        [
                            'name' => 'تقرير المخزون',
                            'description' => 'متابعة المنتجات المتاحة والنافدة من المخزون',
                            'priority' => 'عالي',
                        ],
                        [
                            'name' => 'تقرير الشحن والتوصيل',
                            'description' => 'تتبع حالات الشحن وأوقات التوصيل',
                            'priority' => 'متوسط',
                        ],
                        [
                            'name' => 'تقرير التوزيع الجغرافي',
                            'description' => 'تحليل المبيعات حسب المدن والمناطق',
                            'priority' => 'منخفض',
                        ],
                        [
                            'name' => 'تقرير نمو المبيعات',
                            'description' => 'مقارنة نمو المبيعات على مر الزمن',
                            'priority' => 'عالي',
                        ],
                        [
                            'name' => 'تقرير الفئات الأكثر مبيعاً',
                            'description' => 'تحليل أداء الفئات المختلفة',
                            'priority' => 'متوسط',
                        ],
                    ],
                    'filters' => [],
                    'outputs' => [],
                    'implementationSteps' => [],
                ],
            ],
        ];
    }
}
