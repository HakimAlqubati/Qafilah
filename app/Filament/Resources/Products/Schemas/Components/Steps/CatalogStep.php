<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;

class CatalogStep
{
    /**
     * Create the Catalog step
     */
    public static function make(): Step
    {
        return Step::make(__('lang.catalog'))
            ->icon('heroicon-o-tag')
            ->schema([
                Section::make(__('lang.categorization'))
                    ->columns(3)
                    ->schema([
                        Select::make('category_id')
                            ->label(__('lang.category'))
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('brand_id')
                            ->label(__('lang.brand'))
                            ->relationship('brand', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
