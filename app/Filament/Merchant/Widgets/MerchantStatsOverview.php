<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\ProductVendorSku;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MerchantStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $vendorId = Auth::user()->vendor_id;

        // Get all vendor offers
        $allOffers = ProductVendorSku::where('vendor_id', $vendorId);

        // Count by status
        $totalProducts = $allOffers->count();
        $availableProducts = (clone $allOffers)->where('status', ProductVendorSku::$STATUSES['AVAILABLE'])->count();
        $outOfStock = (clone $allOffers)->where('status', ProductVendorSku::$STATUSES['OUT_OF_STOCK'])->count();

        // Calculate total stock value
        $stockValue = (clone $allOffers)
            ->where('status', ProductVendorSku::$STATUSES['AVAILABLE'])
            ->get()
            ->sum(function ($offer) {
                return $offer->stock * $offer->selling_price;
            });

        return [
            Stat::make(__('Total Products'), $totalProducts)
                ->description(__('Total product offers'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->chart([7, 12, 15, 18, 22, 25, $totalProducts]),

            Stat::make(__('Available'), $availableProducts)
                ->description(__('Products in stock'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 10, 12, 15, 18, 20, $availableProducts]),

            Stat::make(__('Out of Stock'), $outOfStock)
                ->description(__('Need restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : 'success')
                ->chart([2, 3, 1, 4, 2, 1, $outOfStock]),

            Stat::make(__('Stock Value'), number_format($stockValue, 2))
                ->description(__('Total inventory value'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->chart([1000, 1500, 2000, 2500, 3000, 3500, $stockValue]),
        ];
    }
}
