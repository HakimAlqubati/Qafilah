<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label(__('lang.id'))
                    ->sortable()
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label(__('lang.type'))
                    ->limit(50)
                    ->toggleable(),

                \Filament\Tables\Columns\IconColumn::make('active')->alignCenter()
                    ->label(__('lang.active'))
                    ->boolean(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label(__('lang.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('lang.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
