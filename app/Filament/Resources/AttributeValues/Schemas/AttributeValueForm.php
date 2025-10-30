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
                Section::make('🔹 Attribute Value Details')
                    ->description('Define a value that belongs to a specific attribute (e.g., Red, M, 128GB).')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('attribute_id')
                                    ->label('Attribute')
                                    ->options(Attribute::orderBy('name')->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->hint('Select which attribute this value belongs to.'),

                                TextInput::make('value')
                                    ->label('Value')
                                    ->placeholder('Example: Red, 128GB, Leather')
                                    ->required()
                                    ->autocapitalize('words')
                                    ->maxLength(100)
                                    ->hint('The actual display value for this option.'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->label('Sort Order')
                                    ->default(0)
                                    ->hint('Used to order values in dropdowns.')
                                    ->suffixIcon('heroicon-o-bars-3'),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->onIcon('heroicon-o-check-circle')
                                    ->offIcon('heroicon-o-x-circle')
                                    ->hint('Deactivate to hide this value without deleting.'),
                            ]),
                    ]),

            ]);
    }
}
