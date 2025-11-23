<?php

namespace App\Filament\Resources\Vendors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $recordTitleAttribute = 'vendor_sku';

    public function table(Table $table): Table
    {
        return $table->striped()
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vendor_sku')
                    ->label('Variant SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variant_attributes')
                    ->label('Attributes')
                    ->state(function ($record) {
                        // جلب جميع الخصائص وقيمها
                        return $record->variant->variantValues
                            ->map(function ($attributeValue) {
                                // عرض اسم الخاصية: القيمة
                                return $attributeValue->attribute->name . ': ' . $attributeValue->value;
                            })
                            ->implode(', ');
                    })
                    ->wrap()
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('selling_price')
                    ->label('Price')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('cost_price')
                    ->label('Cost Price')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->icon(fn(string $state): string => match ($state) {
                        'available' => 'heroicon-o-check-circle',
                        'out_of_stock' => 'heroicon-o-x-circle',
                        'inactive' => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'out_of_stock' => 'danger',
                        'inactive' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->modifyQueryUsing(function ($query) {
                // Eager load relationships to prevent N+1 query problem
                return $query->with([
                    'variant.product',
                    'variant.variantValues.attribute'
                ]);
            })
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                // EditAction::make(),
                // DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('vendor_sku')
                    ->required()
                    ->maxLength(255),

                TextInput::make('selling_price')
                    ->numeric()
                    ->required(),

                TextInput::make('stock')
                    ->numeric()
                    ->required(),

                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'out_of_stock' => 'Out of Stock',
                        'inactive' => 'Inactive',
                    ])
                    ->required(),
            ]);
    }
}
