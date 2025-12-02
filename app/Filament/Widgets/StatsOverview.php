<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // يمكنك تعديل فترة التحديث التلقائي هنا
 
    protected function getStats(): array
    {
        return [
            // 1. Vendors
            Stat::make(__('lang.total_vendors'), Vendor::count())
                ->description(__('lang.active_vendors_desc'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            // 2. Products
            Stat::make(__('lang.total_products'), Product::count())
                ->description(__('lang.all_products_desc'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            // 3. Customers
            Stat::make(__('lang.total_customers'), Customer::count())
                ->description(__('lang.registered_customers_desc'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            // 4. Orders (Placeholder)
            Stat::make(__('lang.total_orders'), __('lang.coming_soon'))
                ->description(__('lang.processed_orders_desc'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('danger'),
        ];
    }
}