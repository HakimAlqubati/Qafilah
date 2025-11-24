<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('lang.code'))
                    ->searchable(),
                TextColumn::make('phone_code')
                    ->label(__('lang.phone_code'))
                    ->searchable(),
                IconColumn::make('status')
                    ->label(__('lang.status'))
                    ->boolean(),
            ])
            ->filters([
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
