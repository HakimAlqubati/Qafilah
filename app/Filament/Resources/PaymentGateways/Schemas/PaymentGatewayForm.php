<?php

namespace App\Filament\Resources\PaymentGateways\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PaymentGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic Information Section
                Section::make(__('lang.basic_information'))
                    ->description(__('lang.gateway_basic_info_desc'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('lang.gateway_name'))
                                    ->placeholder(__('lang.gateway_name_placeholder'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdatedJs('$set("code", $state.trim().replace(/[\s_]+/g, "-").replace(/[^\p{L}\d-]/gu, "").toLowerCase())')
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label(__('lang.gateway_code'))
                                    ->placeholder(__('lang.gateway_code_placeholder'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->helperText(__('lang.gateway_code_helper')),

                                Select::make('type')
                                    ->label(__('lang.gateway_type'))
                                    ->options([
                                        'electronic' => __('lang.gateway_type_electronic'),
                                        'cash' => __('lang.gateway_type_cash'),
                                        'transfer' => __('lang.gateway_type_transfer'),
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->helperText(__('lang.gateway_type_helper')),

                                Select::make('mode')
                                    ->label(__('lang.gateway_mode'))
                                    ->options([
                                        'sandbox' => __('lang.gateway_mode_sandbox'),
                                        'live' => __('lang.gateway_mode_live'),
                                    ])
                                    ->default('sandbox')
                                    ->required()
                                    ->native(false),

                                Toggle::make('is_active')
                                    ->label(__('lang.is_active'))
                                    ->helperText(__('lang.gateway_active_helper'))
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible(),

                // API Credentials Section (for Electronic gateways)
                Section::make(__('lang.gateway_credentials'))
                    ->description(__('lang.gateway_credentials_desc'))
                    ->icon('heroicon-o-key')
                    ->schema([
                        KeyValue::make('credentials')
                            ->label(__('lang.gateway_credentials'))
                            ->keyLabel(__('lang.gateway_credential_key'))
                            ->valueLabel(__('lang.gateway_credential_value'))
                            ->addActionLabel(__('lang.add_credential'))
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($get) => $get('type') === 'electronic')
                    ->collapsible()
                    ->collapsed(),

                // Instructions Section (for Transfer & Cash gateways)
                Section::make(__('lang.gateway_instructions'))
                    ->description(__('lang.gateway_instructions_desc'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('instructions')
                            ->label(__('lang.gateway_instructions'))
                            ->placeholder(__('lang.gateway_instructions_placeholder'))
                            ->rows(5)
                            ->columnSpanFull()
                            ->helperText(__('lang.gateway_instructions_helper')),
                    ])
                    ->visible(fn($get) => in_array($get('type'), ['transfer', 'cash']))
                    ->collapsible(),
            ]);
    }
}
