<?php

namespace App\Filament\Resources\Districts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class DistrictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('city.name')
                    ->label(__('lang.city'))
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('city.country.name')
                    ->label(__('lang.country'))
                    ->searchable(),
                \Filament\Tables\Columns\IconColumn::make('status')
                    ->label(__('lang.status'))
                    ->boolean(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('city')
                    ->label(__('lang.city'))
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label(__('lang.status'))
                    ->options([
                        true => __('lang.active'),
                        false => __('lang.inactive'),
                    ]),
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
