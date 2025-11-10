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
                Fieldset::make('Unit Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(), // full width
            ]);
    }
}
