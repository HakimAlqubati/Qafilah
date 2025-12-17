<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\ProductVendorSku;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;

class MerchantProductsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('lang.products_added_over_time');
    }

    protected function getData(): array
    {
        $vendorId = Auth::user()->vendor_id;

        // Get data for last 12 months - count distinct products
        $data = ProductVendorSku::where('vendor_id', $vendorId)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(DISTINCT product_id) as count')
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
                    'label' => __('lang.products_added'),
                    'data' => $monthlyData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => [
                __('lang.jan'),
                __('lang.feb'),
                __('lang.mar'),
                __('lang.apr'),
                __('lang.may'),
                __('lang.jun'),
                __('lang.jul'),
                __('lang.aug'),
                __('lang.sep'),
                __('lang.oct'),
                __('lang.nov'),
                __('lang.dec')
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
