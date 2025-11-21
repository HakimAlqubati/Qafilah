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
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable()->alignCenter(),
                TextColumn::make('symbol')->alignCenter()
                    ->searchable(),
                TextColumn::make('rate')->alignCenter()
                    ->numeric() ,
                IconColumn::make('is_default')
                    ->boolean()->alignCenter(),
                IconColumn::make('is_active')
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
