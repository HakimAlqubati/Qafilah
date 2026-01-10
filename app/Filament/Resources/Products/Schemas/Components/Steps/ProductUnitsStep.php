<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Models\Currency;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard\Step;

class ProductUnitsStep
{
    /**
     * Create the Product Units step
     */
    public static function make(): Step
    {
        $defaultCurrency = Currency::default()->first();
        $currencyName = $defaultCurrency?->name ?? '';
        $currencySymbol = $defaultCurrency?->symbol ?? '';

        return Step::make(__('lang.product_units'))
            ->icon('heroicon-o-cube')
            ->schema([
                // ملاحظة العملة الافتراضية
                Placeholder::make('currency_note')
                    ->label(__('lang.default_currency'))
                    ->content(fn() => __('lang.prices_in_default_currency', ['currency' => $currencyName]))
                    ->columnSpanFull(),

                Repeater::make('units')
                    ->relationship('units')
                    ->label(__('lang.product_units'))
                    ->columnSpanFull()
                    ->minItems(1)
                    ->columns(4)
                    ->defaultItems(1)

                    ->table([
                        TableColumn::make(__('lang.unit'))->width('25%'),
                        TableColumn::make(__('lang.package_size'))->width('25%'),
                        TableColumn::make(__('lang.selling_price'))->width('25%'),
                        TableColumn::make(__('lang.cost_price'))->width('25%'),
                    ])
                    ->addActionLabel(__('lang.add_product_unit'))
                    ->schema([
                        Select::make('unit_id')
                            ->label(__('lang.unit'))
                            ->relationship('unit', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->distinct()
                            ->default(fn() => \App\Models\Unit::where('is_default', true)->first()?->id),

                        TextInput::make('package_size')
                            ->label(__('lang.package_size'))
                            ->numeric()
                            ->extraInputAttributes(['style' => 'text-align: center;'])
                            ->default(1)
                            ->minValue(1)
                            ->required(),

                        TextInput::make('selling_price')
                            ->label(__('lang.selling_price'))
                            ->numeric()
                            ->prefix($currencySymbol)
                            ->extraInputAttributes(['style' => 'text-align: center;'])
                            ->minValue(0)->required()
                            ->step(0.01),

                        TextInput::make('cost_price')
                            ->label(__('lang.cost_price'))
                            ->numeric()
                            ->prefix($currencySymbol)
                            ->extraInputAttributes(['style' => 'text-align: center;'])
                            ->minValue(0)->required()
                            ->step(0.01),
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(
                        fn(array $state): ?string =>
                        \App\Models\Unit::find($state['unit_id'] ?? null)?->name ?? 'Unit'
                    ),
            ]);
    }
}
