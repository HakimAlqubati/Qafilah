<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('lang.unit_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('lang.name'))
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__('lang.description'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('active')
                            ->label(__('lang.active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(), // full width
            ]);
    }
}
