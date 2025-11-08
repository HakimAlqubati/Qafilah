<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make('Category Details')->columnSpanFull()
                ->schema([

                    // Row 1: Name & Slug
                    Grid::make(3)->columnSpanFull()
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->placeholder('e.g. Electronics')
                                ->required()
                                ->maxLength(150)
                                ->live(onBlur: true)
                                ->columnSpan(1),
                            Select::make('parent_id')
                                ->label('Parent Category')
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->columnSpan(1),
                            // Active toggle (full width)
                            Toggle::make('active')
                                ->label('Is Active?')
                                ->default(true)
                                ->inline(false) ,
                        ]),



                    // Description (full width)
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),




                ])
                ->columnSpanFull(),
        ]);
    }
}
