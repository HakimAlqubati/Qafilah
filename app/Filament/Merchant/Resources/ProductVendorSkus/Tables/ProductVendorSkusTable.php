<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductVendorSkusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
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
