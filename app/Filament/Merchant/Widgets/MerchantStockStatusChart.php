<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\ProductVendorSku;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MerchantStockStatusChart extends ChartWidget
{
    protected ?string $heading = 'Stock Status Distribution';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $vendorId = Auth::user()->vendor_id;

        // Count products by status
        $available = ProductVendorSku::where('vendor_id', $vendorId)
            ->where('status', ProductVendorSku::$STATUSES['AVAILABLE'])
            ->count();

        $outOfStock = ProductVendorSku::where('vendor_id', $vendorId)
            ->where('status', ProductVendorSku::$STATUSES['OUT_OF_STOCK'])
            ->count();

        $inactive = ProductVendorSku::where('vendor_id', $vendorId)
            ->where('status', ProductVendorSku::$STATUSES['INACTIVE'])
            ->count();

        return [
            'datasets' => [
                [
                    'label' => __('Products'),
                    'data' => [$available, $outOfStock, $inactive],
                    'backgroundColor' => [
                        '#10b981', // green for available
                        '#ef4444', // red for out of stock
                        '#6b7280', // gray for inactive
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                __('Available') . " ({$available})",
                __('Out of Stock') . " ({$outOfStock})",
                __('Inactive') . " ({$inactive})",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
