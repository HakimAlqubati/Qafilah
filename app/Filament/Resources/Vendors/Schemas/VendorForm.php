<?php

// app/Filament/Resources/Vendors/Schemas/VendorForm.php 
// (or directly in VendorResource::form)

namespace App\Filament\Resources\Vendors\Schemas;

use App\Models\Vendor;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VendorForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make(__('lang.vendor_details'))
                    ->tabs([
                        // Tab 1: Basic Information
                        Tab::make(__('lang.basic_info'))
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                ComponentsGrid::make(3)
                                    ->schema([
                                        // 1. Name & Slug (Required)
                                        TextInput::make('name')
                                            ->label(__('lang.vendor_name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true) // Update slug on blur
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $set('slug', Str::slug($state)); // Auto-generate slug
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('slug')
                                            ->label(__('lang.url_slug'))
                                            ->required()
                                            ->unique(ignoreRecord: true) // Ensure unique slug
                                            ->maxLength(255),
                                    ]),

                                ComponentsGrid::make(2)
                                    ->schema([
                                        // 2. Contact Information
                                        TextInput::make('contact_person')
                                            ->label(__('lang.contact_person'))
                                            ->maxLength(255),

                                        TextInput::make('phone')
                                            ->label(__('lang.phone_number'))
                                            ->tel()
                                            ->maxLength(50),
                                    ]),

                                // 3. VAT ID (Unique)
                                TextInput::make('vat_id')
                                    ->label(__('lang.vat_id'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100),

                                // 4. Description
                                RichEditor::make('description')
                                    ->label(__('lang.detailed_description'))
                                    ->maxLength(65535) // Max TEXT length
                                    ->columnSpanFull(),
                            ]),

                        // Tab 2: Location & Delivery
                        Tab::make(__('lang.location_delivery'))
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                ComponentsGrid::make(2)
                                    ->schema([
                                        // Location
                                        TextInput::make('latitude')
                                            ->numeric()
                                            ->label(__('lang.latitude'))
                                            ->minValue(-90)
                                            ->maxValue(90)
                                            ->step(0.00000001) // 8 decimal places precision
                                            ->placeholder(__('lang.latitude_placeholder'))
                                            ->helperText(__('lang.latitude_helper')),
                                        TextInput::make('longitude')
                                            ->numeric()
                                            ->label(__('lang.longitude'))
                                            ->minValue(-180)
                                            ->maxValue(180)
                                            ->step(0.00000001) // 8 decimal places precision
                                            ->placeholder(__('lang.longitude_placeholder'))
                                            ->helperText(__('lang.longitude_helper')),
                                    ]),

                                ComponentsGrid::make(3)
                                    ->schema([
                                        // Delivery Settings
                                        TextInput::make('delivery_rate_per_km')
                                            ->label(__('lang.delivery_rate_km'))
                                            ->numeric()
                                            // Assuming SAR, or dynamic based on currency
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

                                // Delivery Time (مدة التوصيل)
                                ComponentsGrid::make(2)
                                    ->schema([
                                        TextInput::make('delivery_time_value')
                                            ->label(__('lang.delivery_time_value'))
                                            ->numeric()
                                            ->minValue(1)
                                            ->placeholder('1, 2, 3, 24, 48...')
                                            ->helperText(__('lang.delivery_time_helper')),

                                        Select::make('delivery_time_unit')
                                            ->label(__('lang.delivery_time_unit'))
                                            ->options(\App\Models\Vendor::getDeliveryTimeUnitOptions())
                                            ->default(\App\Models\Vendor::DELIVERY_TIME_UNIT_HOURS)
                                            ->native(false),
                                    ]),


                                // Currency & Commission
                                ComponentsGrid::make(2)
                                    ->schema([
                                        // Default Currency
                                        Select::make('default_currency_id')
                                            ->label(__('lang.default_currency'))
                                            ->relationship('defaultCurrency', 'name')
                                            ->searchable()
                                            ->preload(),

                                        // Referrer User (for Commission)
                                        Select::make('referrer_id')
                                            ->label(__('lang.referrer_user'))
                                            ->relationship('referrer', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->helperText(__('lang.referrer_helper'))
                                            ->nullable(),
                                    ]),

                            ]),

                        // Tab 3: Settings & Media
                        Tab::make(__('lang.settings_media'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                ComponentsGrid::make(2)
                                    ->schema([
                                        // 5. Status
                                        Select::make('status')
                                            ->label(__('lang.status'))
                                            ->required()
                                            ->default('active')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                                'pending' => 'Pending Review',
                                            ]),

                                        // 6. Email (Unique)
                                        TextInput::make('email')
                                            ->label(__('lang.email_address'))
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                    ]),

                                // 7. Logo Upload
                                FileUpload::make('logo_path')
                                    ->label(__('lang.vendor_logo'))
                                    ->disk('public')
                                    ->directory('vendors/logos')
                                    ->image()
                                    ->maxSize(500),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
