<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;

class MerchantOrdersChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('lang.orders_by_status') ?? 'Orders by Status';
    }

    protected function getData(): array
    {
        $vendorId = Auth::user()->vendor_id;

        // Query orders grouped by status
        $ordersData = Order::where('vendor_id', $vendorId)
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_amount')
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statuses = [
            Order::STATUS_PENDING => __('lang.pending') ?? 'Pending',
            Order::STATUS_CONFIRMED => __('lang.confirmed') ?? 'Confirmed',
            Order::STATUS_PROCESSING => __('lang.processing') ?? 'Processing',
            Order::STATUS_SHIPPED => __('lang.shipped') ?? 'Shipped',
            Order::STATUS_DELIVERED => __('lang.delivered') ?? 'Delivered',
            Order::STATUS_COMPLETED => __('lang.completed') ?? 'Completed',
            Order::STATUS_CANCELLED => __('lang.cancelled') ?? 'Cancelled',
            Order::STATUS_RETURNED => __('lang.returned') ?? 'Returned',
        ];

        $labels = [];
        $counts = [];
        $amounts = [];

        foreach ($statuses as $statusCode => $statusLabel) {
            $labels[] = $statusLabel;
            
            $row = $ordersData->get($statusCode);
            $counts[] = $row ? (int) $row->count : 0;
            $amounts[] = $row ? (float) $row->total_amount : 0.0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('lang.orders_count') ?? 'Orders Count',
                    'data' => $counts,
                    'backgroundColor' => '#f59e0b', // Amber
                    'borderColor' => '#d97706',
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('lang.total_revenue') ?? 'Total Revenue',
                    'data' => $amounts,
                    'backgroundColor' => '#10b981', // Green
                    'borderColor' => '#059669',
                    'borderWidth' => 1,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => __('lang.orders_count') ?? 'Orders Count',
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => __('lang.total_revenue') ?? 'Total Revenue',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
