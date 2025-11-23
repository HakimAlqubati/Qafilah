<?php

namespace App\Filament\Resources\Districts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('city_id')
                    ->label(__('lang.city'))
                    ->relationship('city', 'name')
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
