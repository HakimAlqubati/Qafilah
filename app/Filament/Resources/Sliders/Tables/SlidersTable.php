<?php

namespace App\Filament\Resources\Sliders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('lang.image'))
                    ->collection('image'),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->label(__('lang.title'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label(__('lang.is_active'))
                    ->boolean()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('lang.sort_order'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
