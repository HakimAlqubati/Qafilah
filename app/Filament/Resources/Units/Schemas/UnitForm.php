<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('lang.unit_information'))
                    ->columns(3)
                    ->schema([
                        Grid::make()->columns(4)->columnSpanFull()->schema([

                            TextInput::make('name')
                                ->label(__('lang.name'))
                                ->required()->columnSpan(2)
                                ->maxLength(100),
                            Toggle::make('active')
                                ->label(__('lang.active'))
                                ->default(true)
                                ->inline(false),
                            Toggle::make('is_default')
                                ->label(__('lang.is_default_unit'))
                                ->default(false)
                                ->inline(false)
                        ]),
                        Textarea::make('description')
                            ->label(__('lang.description'))
                            ->rows(3)
                            ->columnSpanFull(),


                    ])
                    ->columnSpanFull(), // full width
            ]);
    }
}
