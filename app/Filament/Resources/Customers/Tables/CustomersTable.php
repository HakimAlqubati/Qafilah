<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label(__('lang.code'))
                    ->searchable()
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label(__('lang.customer_name'))
                    ->searchable()
                    ->sortable()->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('contact_person')
                    ->label(__('lang.contact_person'))
                    ->searchable()
                    ->sortable()->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('phone')
                    ->label(__('lang.phone'))
                    ->searchable()
                    ->sortable()->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('credit_limit')
                    ->label(__('lang.credit_limit'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('lang.status'))
                    ->boolean()->alignCenter(),

                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
