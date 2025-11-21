<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\Vendor;
use Filament\Tables\Enums\FiltersLayout;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->columns([
                // 1. ID Column (Primary Key)
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                // 2. Name Column (Most important for search/identification)
                TextColumn::make('name')
                    ->label('Vendor Name')
                    ->searchable() // Enables text search
                    ->sortable(),

                // 3. Status Column (Visual filtering/status check)
                IconColumn::make('status')
                    ->label('Status')
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })->alignCenter()
                    ->sortable()
                    ->icon(fn(string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        'pending' => 'heroicon-o-clock',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                // 4. Email Column
                TextColumn::make('email')
                    ->label('Email')
                    ->copyable() // Allows quick copy on click
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Hidden by default to keep table clean

                // 5. Default Currency
                TextColumn::make('defaultCurrency.name')
                    ->label('Currency')
                    ->sortable()
                    ->toggleable(),

                // 6. Delivery Rate
                TextColumn::make('delivery_rate_per_km')
                    ->label('Delivery Rate')
                    ->money(fn($record) => $record->defaultCurrency?->code ?? 'SAR')
                    ->sortable()
                    ->toggleable(),

                // 5. Products Count (Performance improved with withCount in query)
                // TextColumn::make('products_count')
                //     ->label('Products')
                //     ->counts('products') // Uses the withCount applied in getEloquentQuery
                //     ->sortable(),

                // 6. Creator (Auditing - Requires Eager Loading 'creator')
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 7. Created At (Audit)
                TextColumn::make('created_at')
                    ->label('Creation Date')
                    ->dateTime()
                    ->sortable(),
            ])->filtersFormColumns(4)
            ->filters([
                // Filter 1: Status Filter
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending Review',
                    ])
                    ->label('Filter by Status'),

                // Filter 2: Soft Deletes Filter
                TrashedFilter::make(),
            ], FiltersLayout::Modal)
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Actions for Soft Deletes (Restoration and Force Delete)
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            // Default table ordering
            ->defaultSort('id', 'desc');
    }
}
