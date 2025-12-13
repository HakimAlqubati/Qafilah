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
                                            ->placeholder('مثال: 15.3694')
                                            ->helperText('يجب أن تكون القيمة بين -90 و 90'),
                                        TextInput::make('longitude')
                                            ->numeric()
                                            ->label(__('lang.longitude'))
                                            ->minValue(-180)
                                            ->maxValue(180)
                                            ->step(0.00000001) // 8 decimal places precision
                                            ->placeholder('مثال: 44.1910')
                                            ->helperText('يجب أن تكون القيمة بين -180 و 180'),
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

                                // Default Currency
                                Select::make('default_currency_id')
                                    ->label(__('lang.default_currency'))
                                    ->relationship('defaultCurrency', 'name')
                                    ->searchable()
                                    ->preload(),
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
