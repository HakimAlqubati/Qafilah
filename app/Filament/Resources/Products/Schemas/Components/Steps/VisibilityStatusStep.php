<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard\Step;

class VisibilityStatusStep
{
    /**
     * Create the Visibility & Status step
     */
    public static function make(): Step
    {
        return Step::make(__('lang.visibility_status'))
            ->icon('heroicon-o-eye')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('status')
                            ->label(__('lang.product_status'))
                            ->options(Product::statusOptions())
                            ->default(Product::$STATUSES['DRAFT'])
                            ->required()
                            ->native(false),

                        Toggle::make('is_featured')
                            ->label(__('lang.feature_on_homepage'))
                            ->default(false)
                            ->inline(false)
                            ->helperText(__('lang.feature_on_homepage_desc')),
                    ]),
            ]);
    }
}
