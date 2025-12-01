<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductVendorSkusTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variant_details')
                    ->label(__('lang.variant'))
                    ->state(function ($record) {
                        return $record->variant?->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(', ');
                    }),

                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('lang.images'))
                    ->collection('default')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText()
                    ->size(40)
                    ->alignCenter(),

                TextColumn::make('vendor_sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('selling_price')
                    ->label('Price')
                    ->money(fn($record) => $record->currency->code ?? 'USD')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'out_of_stock' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
