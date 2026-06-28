<?php

namespace App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

class CustomerLoyaltyWalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
          Grid::make(2)->columnSpanFull()->schema([
                  Select::make('customer_id')
                    ->label(__('lang.customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                \Filament\Forms\Components\Hidden::make('merchant_id')
                    ->default(fn () => auth()->user()?->vendor_id),
                
                TextInput::make('balance')
                    ->label(__('lang.wallet_balance'))
                    ->numeric()
                    ->default(0)
                    ->required(),
          ])
            ]);
    }
}
