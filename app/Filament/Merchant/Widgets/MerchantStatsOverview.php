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

        // Count unique products (distinct product_id)
        $totalProducts = (clone $allOffers)->distinct('product_id')->count('product_id');
        $totalSkus = (clone $allOffers)->count();

        // Count available unique products
        $availableProducts = (clone $allOffers)
            ->where('status', ProductVendorSku::$STATUSES['AVAILABLE'])
            ->distinct('product_id')
            ->count('product_id');

        // Count out of stock unique products
        $outOfStock = (clone $allOffers)
            ->where('status', ProductVendorSku::$STATUSES['OUT_OF_STOCK'])
            ->distinct('product_id')
            ->count('product_id');

        // Calculate total stock value
        $stockValue = (clone $allOffers)
            ->where('status', ProductVendorSku::$STATUSES['AVAILABLE'])
            ->get()
            ->sum(function ($offer) {
                return $offer->stock * $offer->selling_price;
            });

        return [
            Stat::make(__('lang.total_products'), $totalProducts)
                ->description($totalSkus . ' ' . __('lang.total_skus_variants'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->chart([7, 12, 15, 18, 22, 25, $totalProducts]),

            Stat::make(__('lang.available'), $availableProducts)
                ->description(__('lang.products_in_stock'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 10, 12, 15, 18, 20, $availableProducts]),

            Stat::make(__('lang.out_of_stock'), $outOfStock)
                ->description(__('lang.need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : 'success')
                ->chart([2, 3, 1, 4, 2, 1, $outOfStock]),

            Stat::make(__('lang.stock_value'), number_format($stockValue, 2))
                ->description(__('lang.total_inventory_value'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->chart([1000, 1500, 2000, 2500, 3000, 3500, $stockValue]),
        ];
    }
}
