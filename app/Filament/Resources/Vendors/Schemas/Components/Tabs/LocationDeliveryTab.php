<?php

namespace App\Filament\Resources\Vendors\Schemas\Components\Tabs;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\District;
use App\Models\Setting;
use App\Models\Vendor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;

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
                // ═══════════════════════════════════════════════════════════
                // 📍 قسم الموقع الجغرافي
                // ═══════════════════════════════════════════════════════════
                Section::make(__('lang.address_location'))
                    ->icon('heroicon-o-map')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('country_id')
                                    ->label(__('lang.country'))
                                    ->options(Country::pluck('name', 'id'))
                                    ->default(fn() => Setting::getSetting('default_country_id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn(\Filament\Schemas\Components\Utilities\Set $set) => $set('city_id', null)),

                                Select::make('city_id')
                                    ->label(__('lang.city'))
                                    ->options(fn(Get $get) => City::where('country_id', $get('country_id'))->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn(\Filament\Schemas\Components\Utilities\Set $set) => $set('district_id', null)),

                                Select::make('district_id')
                                    ->label(__('lang.district'))
                                    ->options(fn(Get $get) => District::where('city_id', $get('city_id'))->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                            ]),

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
                    ]),

                // ═══════════════════════════════════════════════════════════
                // 🚚 قسم التوصيل
                // ═══════════════════════════════════════════════════════════
                Section::make(__('lang.delivery_settings'))
                    ->icon('heroicon-o-truck')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('delivery_rate_per_km')
                                    ->label(__('lang.delivery_rate_km'))
                                    ->numeric()
                                    ->default(0)
                                    ->prefix(fn() => Currency::default()->first()?->symbol ?? 'SAR'),

                                TextInput::make('min_delivery_charge')
                                    ->label(__('lang.min_delivery_charge'))
                                    ->numeric()
                                    ->default(0)
                                    ->prefix(fn() => Currency::default()->first()?->symbol ?? 'SAR'),

                                TextInput::make('max_delivery_distance')
                                    ->label(__('lang.max_distance_km'))
                                    ->numeric()
                                    ->suffix('KM'),
                            ]),

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
                    ])
                    ->hidden()
                    ,

                // ═══════════════════════════════════════════════════════════
                // 🚚 سياسة الشحن
                // ═══════════════════════════════════════════════════════════
                Section::make(__('lang.shipping_policy'))
                    ->icon('heroicon-o-truck')
                    ->relationship('shippingPolicy')
                    ->collapsible()
                    ->schema([
                        Radio::make('is_free')
                            ->label(__('lang.shipping_type'))
                            ->boolean(__('lang.free'), __('lang.paid'))
                            ->inline()
                            ->default(0)
                            ->live(),

                        Radio::make('charge_type')
                            ->label(__('lang.charge_type'))
                            ->options([
                                'fixed' => __('lang.fixed'),
                                'variable' => __('lang.variable'),
                            ])
                            ->live()
                            ->visible(fn (Get $get): bool => ! (bool) $get('is_free')),

                        TextInput::make('fixed_amount')
                            ->label(__('lang.fixed_amount'))
                            ->numeric()
                            ->prefix(fn() => Currency::default()->first()?->symbol ?? 'SAR')
                            ->required(fn (Get $get): bool => $get('charge_type') === 'fixed')
                            ->visible(fn (Get $get): bool => ! (bool) $get('is_free') && $get('charge_type') === 'fixed'),

                        TextInput::make('per_km_rate')
                            ->label(__('lang.per_km_rate'))
                            ->numeric()
                            ->suffix('KM')
                            ->required(fn (Get $get): bool => $get('charge_type') === 'variable')
                            ->visible(fn (Get $get): bool => ! (bool) $get('is_free') && $get('charge_type') === 'variable'),

                        TextInput::make('min_order_amount')
                            ->label(__('lang.min_order_amount'))
                            ->numeric()
                            ->prefix(fn() => Currency::default()->first()?->symbol ?? 'SAR'),
                    ]),

                // ═══════════════════════════════════════════════════════════
                // 💰 قسم العملة والعمولة
                // ═══════════════════════════════════════════════════════════
                Section::make(__('lang.currency_commission'))
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('default_currency_id')
                                    ->label(__('lang.default_currency'))
                                    ->relationship('defaultCurrency', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(fn() => Currency::default()->first()?->id)
                                    ->disabled()
                                    ->helperText(__('lang.default_currency_helper')),

                                Select::make('referrer_id')
                                    ->label(__('lang.referrer_user'))
                                    ->relationship('referrer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('lang.referrer_helper'))
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
