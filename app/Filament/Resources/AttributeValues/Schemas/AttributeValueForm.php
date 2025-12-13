<?php

namespace App\Filament\Resources\AttributeValues\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{

    TextInput,
    Select,
    Textarea,
    Toggle
};
use App\Models\Attribute;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class AttributeValueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('lang.attribute_value_details'))
                    ->description(__('lang.attribute_value_def'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('attribute_id')
                                    ->label(__('lang.attribute'))
                                    ->options(Attribute::orderBy('name')->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->hint(__('lang.select_attribute_belong')),

                                TextInput::make('value')
                                    ->label(__('lang.value'))
                                    ->placeholder(__('lang.example_values'))
                                    ->required()
                                    ->autocapitalize('words')
                                    ->maxLength(100)
                                    ->hint(__('lang.display_value')),

                                TextInput::make('code')
                                    ->label(__('lang.code'))
                                    ->placeholder(__('lang.code_placeholder'))
                                    ->maxLength(100)
                                    ->hint(__('lang.code_helper')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->label(__('lang.sort_order'))
                                    ->default(0)
                                    ->hint(__('lang.sort_order_desc'))
                                    ->suffixIcon('heroicon-o-bars-3'),

                                Toggle::make('is_active')
                                    ->label(__('lang.active'))
                                    ->default(true)
                                    ->onIcon('heroicon-o-check-circle')
                                    ->offIcon('heroicon-o-x-circle')
                                    ->hint(__('lang.deactivate_desc')),
                            ]),
                    ]),

            ]);
    }
}
