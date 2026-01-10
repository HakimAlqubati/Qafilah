<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make()->columnSpanFull()->schema([
                    TextInput::make('name')
                        ->label(__('lang.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('lang.code'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('symbol')
                        ->label(__('lang.symbol'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('rate')
                        ->label(__('lang.exchange_rate'))
                        ->required()
                        ->numeric(),
                    Toggle::make('is_default')
                        ->label(__('lang.default_currency'))
                        ->required(),
                    Toggle::make('is_active')
                        ->label(__('lang.is_active'))
                        ->required(),
                ])
            ]);
    }
}
