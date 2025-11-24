<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('lang.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label(__('lang.code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('phone_code')
                    ->label(__('lang.phone_code'))
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Toggle::make('status')
                    ->label(__('lang.status'))
                    ->required()
                    ->default(true),
            ]);
    }
}
