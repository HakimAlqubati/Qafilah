<?php

namespace App\Filament\Resources\PaymentTransactions\Schemas;

use App\Models\Currency;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        $defaultCurrency = Currency::where('is_default', true)->first();
        $defaultCurrencyCode = $defaultCurrency?->code ?? 'USD';
        $defaultCurrencySymbol = $defaultCurrency?->symbol ?? '$';

        return $schema
            ->components([
                // 1. Basic Information Section
                Section::make(__('lang.transaction_basic_info'))
                    ->description(__('lang.transaction_details_desc'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('gateway_id')
                                    ->label(__('lang.payment_gateway'))
                                    ->relationship('gateway', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('amount')
                                    ->label(__('lang.transaction_amount'))
                                    ->numeric()
                                    ->required()
                                    ->prefix(function ($get) use ($defaultCurrencySymbol) {
                                        $currencyCode = $get('currency');
                                        if (!$currencyCode) return $defaultCurrencySymbol;

                                        return Currency::where('code', $currencyCode)->first()?->symbol ?? $defaultCurrencySymbol;
                                    })
                                    ->live(),

                                Select::make('currency')
                                    ->label(__('lang.transaction_currency'))
                                    ->options(Currency::active()->pluck('code', 'code'))
                                    ->required()
                                    ->default($defaultCurrencyCode)
                                    ->live()
                                    ->native(false),

                                Select::make('status')
                                    ->label(__('lang.transaction_status'))
                                    ->options([
                                        'pending' => __('lang.pending'),
                                        'paid' => __('lang.paid'),
                                        'failed' => __('lang.failed'),
                                        'refunded' => __('lang.refunded'),
                                        'reviewing' => __('lang.reviewing'),
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->native(false),

                                TextInput::make('uuid')
                                    ->label(__('lang.transaction_uuid'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn($record) => filled($record)),

                                TextInput::make('created_at')
                                    ->label(__('lang.transaction_date'))
                                    ->disabled()
                                    ->visible(fn($record) => filled($record)),
                            ]),
                    ])
                    ->collapsible(),

                // 2. Related Information Section
                Section::make(__('lang.transaction_related_info'))
                    ->icon('heroicon-o-link')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('lang.transaction_user'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make('created_by')
                                    ->label(__('lang.created_by'))
                                    ->relationship('creator', 'name')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn($record) => filled($record)),

                                TextInput::make('reference_id')
                                    ->label(__('lang.transaction_reference'))
                                    ->placeholder('TXN-123456')
                                    ->maxLength(255),

                                TextInput::make('payable_type')
                                    ->label(__('lang.transaction_payable_type'))
                                    ->placeholder('App\Models\Order')
                                    ->maxLength(255),

                                TextInput::make('payable_id')
                                    ->label(__('lang.transaction_payable_id'))
                                    ->numeric(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // 3. Proof and Response Section
                Grid::make(2)
                    ->schema([
                        Section::make(__('lang.transaction_proof'))
                            ->icon('heroicon-o-camera')
                            ->schema([
                                FileUpload::make('proof_image')
                                    ->label(__('lang.transaction_proof'))
                                    ->image()
                                    ->directory('payments/proofs')
                                    ->disk('public')
                                    ->visibility('public'),
                            ])
                            ->columnSpan(1)
                            ->collapsible(),

                        Section::make(__('lang.transaction_gateway_response'))
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                KeyValue::make('gateway_response')
                                    ->label(__('lang.transaction_gateway_response'))
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columnSpan(1)
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ]);
    }
}
