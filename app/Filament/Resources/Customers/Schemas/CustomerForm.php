<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->columnSpanFull()->skippable()
                    ->steps([
                        // Step 1: Company Info
                        Step::make('company_info')
                            ->label(__('lang.company_info'))
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('lang.customer_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label(__('lang.code'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generated'),

                                TextInput::make('vat_number')
                                    ->label(__('lang.vat_number'))
                                    ->maxLength(255),

                                TextInput::make('commercial_register')
                                    ->label(__('lang.commercial_register'))
                                    ->maxLength(255),
                            ])->columns(2),

                        // Step 2: Contact Info
                        Step::make('contact_info')
                            ->label(__('lang.contact_info'))
                            ->icon('heroicon-o-phone')
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label(__('lang.contact_person'))
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label(__('lang.phone'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label(__('lang.email'))
                                    ->email()
                                    ->maxLength(255),
                            ])->columns(2),

                        // Step 3: Financial Info
                        Step::make('financial_info')
                            ->label(__('lang.financial_info'))
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextInput::make('credit_limit')
                                    ->label(__('lang.credit_limit'))
                                    ->numeric()
                                    ->prefix('SAR')
                                    ->default(0),

                                TextInput::make('payment_terms')
                                    ->label(__('lang.payment_terms'))
                                    ->maxLength(255),

                                Toggle::make('is_active')
                                    ->label(__('lang.active'))
                                    ->default(true),
                            ])->columns(2),

                        // Step 4: Addresses
                        Step::make('addresses')
                            ->label(__('lang.addresses'))
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Repeater::make('addresses')
                                    ->relationship()
                                    ->label(__('lang.addresses'))
                                    ->schema([
                                        Select::make('type')
                                            ->label(__('lang.address_type'))
                                            ->options([
                                                'general' => __('lang.general'),
                                                'shipping' => __('lang.shipping'),
                                                'billing' => __('lang.billing'),
                                            ])
                                            ->default('general')
                                            ->required(),

                                        Select::make('city_id')
                                            ->label(__('lang.city'))
                                            ->relationship('city', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Select::make('district_id')
                                            ->label(__('lang.district'))
                                            ->relationship('district', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Textarea::make('address')
                                            ->label(__('lang.address'))
                                            ->columnSpanFull(),

                                        Toggle::make('is_default')
                                            ->label(__('lang.is_default')),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                            ]),
                    ])
            ]);
    }
}
