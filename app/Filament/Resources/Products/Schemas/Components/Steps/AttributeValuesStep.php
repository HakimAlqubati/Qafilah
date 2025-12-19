<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Filament\Resources\Products\Schemas\Helpers\AttributeHelpers;
use App\Filament\Resources\Products\Schemas\Helpers\AttributeValueFieldBuilder;
use App\Models\Attribute;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;

class AttributeValuesStep
{
    /**
     * Create the Attribute Values step (Custom Attributes)
     */
    public static function make(): Step
    {
        return Step::make(__('lang.attribute_values'))
            ->icon('heroicon-o-rectangle-group')
            ->schema([
                Repeater::make('attributes')
                    ->label('')
                    ->relationship('attributes')
                    ->columns(12)
                    ->collapsed(false)
                    ->table([
                        TableColumn::make(__('lang.attribute'))->width(4),
                        TableColumn::make(__('lang.value'))->width(8),
                    ])
                    ->reorderable(false)
                    ->minItems(0)
                    ->defaultItems(0)
                    ->addActionLabel(__('lang.add_attribute'))
                    ->schema([
                        Select::make('attribute_id')
                            ->label(__('lang.attribute'))
                            ->columnSpan(4)
                            ->required()
                            ->distinct()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->options(fn(Get $get) => AttributeHelpers::getAvailableAttributesForProductOrSet($get('../../id')))
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('value', null);
                                if ($state) {
                                    $attr = Attribute::with('values')->find($state);
                                    if ($attr && $attr->isBoolean()) {
                                        $set('value', '0');
                                    }
                                }
                            }),

                        Grid::make()
                            ->columnSpan(8)
                            ->columns(12)
                            ->schema(fn(Get $get) => AttributeValueFieldBuilder::make($get('attribute_id'))),
                    ]),
            ])
            ->visible(function (Get $get, Component $component) {
                $productId = $get('id') ?? ($component->getRecord()?->id);
                if (!$productId) {
                    return false;
                }

                return \App\Models\Attribute::query()
                    ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                    ->where('pa.product_id', $productId)
                    ->where('attributes.active', true)
                    ->exists();
            })
            ->hiddenOn('create')
            ->hidden();
    }
}
