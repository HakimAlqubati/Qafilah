<?php

namespace App\Filament\Resources\Vendors\Schemas\Components\Tabs;

use App\Models\Vendor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;

class LocationDeliveryTab
{
    /**
     * Create the Location & Delivery tab
     */
    public static function make(): Tab
    {
        return Tab::make(__('lang.location_delivery'))
            ->icon('heroicon-o-map-pin')
            ->schema([
                // Location
                Grid::make(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->numeric()
                            ->label(__('lang.latitude'))
                            ->minValue(-90)
                            ->maxValue(90)
                            ->step(0.00000001)
                            ->placeholder(__('lang.latitude_placeholder'))
                            ->helperText(__('lang.latitude_helper')),

                        TextInput::make('longitude')
                            ->numeric()
                            ->label(__('lang.longitude'))
                            ->minValue(-180)
                            ->maxValue(180)
                            ->step(0.00000001)
                            ->placeholder(__('lang.longitude_placeholder'))
                            ->helperText(__('lang.longitude_helper')),
                    ]),

                // Delivery Settings
                Grid::make(3)
                    ->schema([
                        TextInput::make('delivery_rate_per_km')
                            ->label(__('lang.delivery_rate_km'))
                            ->numeric()
                            ->default(0),

                        TextInput::make('min_delivery_charge')
                            ->label(__('lang.min_delivery_charge'))
                            ->numeric()
                            ->default(0),

                        TextInput::make('max_delivery_distance')
                            ->label(__('lang.max_distance_km'))
                            ->numeric()
                            ->suffix('KM'),
                    ]),

                // Delivery Time
                Grid::make(2)
                    ->schema([
                        TextInput::make('delivery_time_value')
                            ->label(__('lang.delivery_time_value'))
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('1, 2, 3, 24, 48...')
                            ->helperText(__('lang.delivery_time_helper')),

                        Select::make('delivery_time_unit')
                            ->label(__('lang.delivery_time_unit'))
                            ->options(Vendor::getDeliveryTimeUnitOptions())
                            ->default(Vendor::DELIVERY_TIME_UNIT_HOURS)
                            ->native(false),
                    ]),

                // Currency & Commission
                Grid::make(2)
                    ->schema([
                        Select::make('default_currency_id')
                            ->label(__('lang.default_currency'))
                            ->relationship('defaultCurrency', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('referrer_id')
                            ->label(__('lang.referrer_user'))
                            ->relationship('referrer', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('lang.referrer_helper'))
                            ->nullable(),
                    ]),
            ]);
    }
}
