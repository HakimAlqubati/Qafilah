<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue per Month';
    protected static ?int $sort = 3;
    public function getHeading(): string | Htmlable | null
    {
        return __('lang.revenue_per_month');
    }


    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => [2400, 1500, 3200, 4500, 2100, 3800, 5200, 4100, 3900, 5600, 6100, 7500],
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
