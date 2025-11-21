<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CustomersChart;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\ProductsChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\WelcomeHero;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getTitle(): string | Htmlable
    {
        return '';
    }
    public function getColumns(): int|array
    {
        return 2;
    }
    public function getWidgets(): array
    {
        return [
            // WelcomeHero::class,
            \App\Filament\Widgets\StatsOverview::class,
            ProductsChart::class,
            CustomersChart::class,
            RevenueChart::class,
            OrdersChart::class,
        ];
    }
}
