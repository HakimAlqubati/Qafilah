<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([


                Tables\Columns\TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),



                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('lang.parent'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('attributeSet.name')
                    ->label(__('lang.attribute_set')),



                Tables\Columns\IconColumn::make('active')
                    ->label(__('lang.active'))
                    ->boolean()->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('lang.created'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('lang.updated'))
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('active')
                    ->label(__('lang.active'))
                    ->options([
                        1 => __('lang.active'),
                        0 => __('lang.inactive'),
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
