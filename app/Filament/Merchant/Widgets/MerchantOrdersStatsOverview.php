<?php

namespace App\Filament\Merchant\Widgets;

use App\Filament\Merchant\Resources\Orders\MerchantOrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MerchantOrdersStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $vendorId = Auth::user()->vendor_id;

        $orders = Order::where('vendor_id', $vendorId);

        $totalOrders = (clone $orders)->count();
        $pendingOrders = (clone $orders)->where('status', Order::STATUS_PENDING)->count();
        $completedOrders = (clone $orders)->where('status', Order::STATUS_COMPLETED)->count();
        
        $totalRevenue = (clone $orders)
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->sum('total');

        return [
            Stat::make(__('lang.total_orders') ?? 'Total Orders', $totalOrders)
                ->description(__('lang.all_time_orders') ?? 'All time orders')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->url(MerchantOrderResource::getUrl())
                ->chart([3, 5, 10, 15, 20, 25, $totalOrders]),

            Stat::make(__('lang.pending_orders') ?? 'Pending Orders', $pendingOrders)
                ->description(__('lang.needs_processing') ?? 'Needs processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(MerchantOrderResource::getUrl() . '?tableFilters[status][value]=' . Order::STATUS_PENDING)
                ->chart([1, 2, 4, 3, 5, 2, $pendingOrders]),

            Stat::make(__('lang.completed_orders') ?? 'Completed Orders', $completedOrders)
                ->description(__('lang.successfully_delivered') ?? 'Successfully delivered')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(MerchantOrderResource::getUrl() . '?tableFilters[status][value]=' . Order::STATUS_COMPLETED)
                ->chart([2, 3, 6, 12, 15, 20, $completedOrders]),

            Stat::make(__('lang.total_revenue') ?? 'Total Revenue', number_format($totalRevenue, 2))
                ->description(__('lang.from_completed_orders') ?? 'From completed orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([100, 300, 500, 1000, 1500, 2000, $totalRevenue]),
        ];
    }
}
