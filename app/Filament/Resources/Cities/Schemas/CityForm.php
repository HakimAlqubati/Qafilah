<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('country_id')
                    ->label(__('lang.country'))
                    ->relationship('country', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label(__('lang.name'))
                    ->required()
                    ->maxLength(255),
                Toggle::make('status')
                    ->label(__('lang.status'))
                    ->required()
                    ->default(true),
            ]);
    }
}
