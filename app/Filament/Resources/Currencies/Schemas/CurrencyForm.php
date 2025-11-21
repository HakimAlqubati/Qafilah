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
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('symbol')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('rate')
                        ->required()
                        ->numeric(),
                    Toggle::make('is_default')
                        ->required(),
                    Toggle::make('is_active')
                        ->required(),
                ])
            ]);
    }
}
