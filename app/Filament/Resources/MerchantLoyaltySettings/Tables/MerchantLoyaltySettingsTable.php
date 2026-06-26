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
                    ->label('Merchant')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('earning_spend_amount')
                    ->label('Spend Amount Required')
                    ->money()
                    ->sortable(),

                TextColumn::make('earning_reward_points')
                    ->label('Reward Points')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('redemption_points_block')
                    ->label('Redemption Block')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('redemption_discount_value')
                    ->label('Discount Value')
                    ->money()
                    ->sortable(),

                TextColumn::make('min_points_to_redeem')
                    ->label('Min. Points to Redeem')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
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
