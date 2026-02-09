<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class CustomersChart extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'New Customers';

    protected static ?int $sort = 4;
    protected ?string $maxHeight = '400px';

    public function getHeading(): string | Htmlable | null
    {
        return __('lang.new_customers');
    }

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('lang.new_customers'),
                    'data' => [12, 19, 3, 5, 2, 3, 10, 15, 20, 25, 30, 45],
                    'borderColor' => '#FF6384',
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
