<?php

namespace App\Filament\Resources\CustomerLoyaltyWallets\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class CustomerLoyaltyWalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('customer_id')
                    ->label(__('lang.customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Select::make('merchant_id')
                    ->label(__('lang.vendor'))
                    ->relationship('merchant', 'store_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                TextInput::make('balance')
                    ->label(__('lang.wallet_balance'))
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }
}
