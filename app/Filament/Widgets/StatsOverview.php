<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Vendors', Vendor::count())
                ->description('Active vendors')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Total Products', Product::count())
                ->description('All products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Total Customers', 0)
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Total Orders', 0)
                ->description('Processed orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('danger'),
        ];
    }
}
