<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    protected ?string $heading = 'Product Distribution';

    protected static ?int $sort = 5;
 
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => [30, 50, 20],
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ],
                ],
            ],
            'labels' => ['Electronics', 'Clothing', 'Furniture'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
