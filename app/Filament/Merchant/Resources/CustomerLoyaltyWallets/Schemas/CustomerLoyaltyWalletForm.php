<?php

namespace App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Schemas;

use App\Models\Currency;
use App\Models\MerchantLoyaltySetting;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CustomerLoyaltyWalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)->columnSpanFull()->schema([
                    Select::make('customer_id')
                        ->label(__('lang.customer'))
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    
                    Hidden::make('merchant_id')
                        ->default(fn () => auth()->user()?->vendor_id),
                    
                    TextInput::make('balance')
                        ->label(__('lang.wallet_balance'))
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, $state, ?Model $record) {
                            $merchantId = auth()->user()?->vendor_id ?? $record?->merchant_id;
                            $discountPerPoint = MerchantLoyaltySetting::where('merchant_id', $merchantId)->value('redemption_discount_value') ?? 0;
                            $set('monetary_value', number_format((int) $state * (float) $discountPerPoint, 2));
                        })
                        ->required(),

                    TextInput::make('monetary_value')
                        ->label(__('lang.points_monetary_value'))
                        ->disabled()
                        ->dehydrated(false)
                        ->prefix(fn (): string => Currency::getMerchantCurrencySymbol(auth()->user()?->vendor_id))
                        ->formatStateUsing(fn (?Model $record): string => number_format($record?->monetary_value ?? 0, 2)),
                ])
            ]);
    }
}
