<?php

namespace App\Filament\Resources\MerchantLoyaltySettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MerchantLoyaltySettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('merchant.name')
                    ->label(__('lang.vendor'))
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('lang.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('earning_spend_amount')
                    ->label(__('lang.spend_amount_required'))
                    ->money()
                    ->sortable(),

                TextColumn::make('earning_reward_points')
                    ->label(__('lang.reward_points'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('redemption_points_block')
                    ->label(__('lang.redemption_block'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('redemption_discount_value')
                    ->label(__('lang.discount_value'))
                    ->money()
                    ->sortable(),

                TextColumn::make('min_points_to_redeem')
                    ->label(__('lang.min_points_to_redeem'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('lang.active_status'))
                    ->boolean()
                    ->trueLabel(__('lang.only_active'))
                    ->falseLabel(__('lang.only_inactive'))
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
