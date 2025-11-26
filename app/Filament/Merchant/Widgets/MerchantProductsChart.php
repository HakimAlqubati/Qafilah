<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\ProductVendorSku;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MerchantProductsChart extends ChartWidget
{
    protected ?string $heading = 'Products Added Over Time';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $vendorId = Auth::user()->vendor_id;

        // Get data for last 12 months
        $data = ProductVendorSku::where('vendor_id', $vendorId)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill in missing months with 0
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Products Added'),
                    'data' => $monthlyData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => [
                __('Jan'),
                __('Feb'),
                __('Mar'),
                __('Apr'),
                __('May'),
                __('Jun'),
                __('Jul'),
                __('Aug'),
                __('Sep'),
                __('Oct'),
                __('Nov'),
                __('Dec')
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
