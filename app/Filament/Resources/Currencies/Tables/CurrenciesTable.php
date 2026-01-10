<?php

namespace App\Filament\Resources\Currencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable(),
                TextColumn::make('code')
                    ->label(__('lang.code'))
                    ->searchable()->alignCenter(),
                TextColumn::make('symbol')->alignCenter()
                    ->label(__('lang.symbol'))
                    ->searchable(),
                TextColumn::make('rate')->alignCenter()
                    ->label(__('lang.exchange_rate'))
                    ->numeric(),
                IconColumn::make('is_default')
                    ->label(__('lang.is_currency_default'))
                    ->boolean()->alignCenter(),
                IconColumn::make('is_active')
                    ->label(__('lang.active'))
                    ->boolean()->alignCenter(),
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
