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
            Fieldset::make(__('lang.category_details'))->columnSpanFull()
                ->schema([

                    // Row 1: Name & Slug
                    Grid::make(3)->columnSpanFull()
                        ->schema([
                            TextInput::make('name')
                                ->label(__('lang.name'))
                                ->placeholder(__('lang.example_category'))
                                ->required()
                                ->maxLength(150)
                                ->live(onBlur: true)
                                ->columnSpan(1),
                            Select::make('parent_id')
                                ->label(__('lang.parent_category'))
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->columnSpan(1),
                            // Active toggle (full width)
                            Toggle::make('active')
                                ->label(__('lang.is_active'))
                                ->default(true)
                                ->inline(false),
                        ]),



                    // Description (full width)
                    Textarea::make('description')
                        ->label(__('lang.description'))
                        ->rows(4)
                        ->columnSpanFull(),




                ])
                ->columnSpanFull(),
        ]);
    }
}
