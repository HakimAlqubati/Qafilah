<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use App\Models\Category;
use Illuminate\Contracts\Support\Htmlable;

class ProductsChart extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'Product Distribution';

    protected static ?int $sort = 5;

    public function getHeading(): string | Htmlable | null
    {
        return __('lang.product_distribution');
    }


    protected function getData(): array
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        $labels = $categories->pluck('name')->toArray();
        $data = $categories->pluck('products_count')->toArray();

        $backgroundColors = [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 205, 86)',
        ];

        while (count($backgroundColors) < count($labels)) {
            $backgroundColors[] = 'rgb(75, 192, 192)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($labels)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
