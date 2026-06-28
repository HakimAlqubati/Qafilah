<?php

namespace App\Filament\Resources\CustomerLoyaltyWallets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CustomerLoyaltyWalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->withSum(['loyaltyTransactions as used_points' => fn ($q) => $q->where('type', 'redeemed')], 'points'))
            ->columns([
                TextColumn::make('customer.name')
                    ->label(__('lang.customer'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('merchant.name')
                    ->label(__('lang.vendor'))
                    ->searchable()
                    ->sortable()
                    ->hidden(fn () => filament()->getCurrentPanel()->getId() === 'merchant'),
                
                TextColumn::make('used_points')
                    ->label(__('lang.used_points'))
                    ->state(fn ($record) => abs($record->used_points ?? 0))
                    ->sortable(),
                
                TextColumn::make('balance')
                    ->label(__('lang.wallet_balance'))
                    // ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('lang.updated_at'))
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
