<?php

namespace App\Filament\Resources\MerchantLoyaltySettings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

class MerchantLoyaltySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Settings')
                    ->description('Manage active status and merchant assignment.')
                    ->schema([
                        Select::make('merchant_id')
                            ->relationship('merchant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Merchant')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Section::make('Earning Rules')
                    ->description('Define how customers earn points based on their spend.')
                    ->schema([
                        TextInput::make('earning_spend_amount')
                            ->label('Spend Amount Required')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->helperText('The amount a customer needs to spend to earn the reward points.'),

                        TextInput::make('earning_reward_points')
                            ->label('Reward Points Earned')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->helperText('Points earned for every multiple of the spend amount.'),
                    ])->columns(2),

                Section::make('Redemption Rules')
                    ->description('Define how customers can redeem their points.')
                    ->schema([
                        TextInput::make('min_points_to_redeem')
                            ->label('Minimum Points to Redeem')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->helperText('The minimum points balance required before redemption is allowed.'),

                        TextInput::make('redemption_points_block')
                            ->label('Redemption Points Block')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->helperText('Points must be redeemed in multiples of this block size (e.g., 100).'),

                        TextInput::make('redemption_discount_value')
                            ->label('Discount Value per Block')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->helperText('The monetary discount applied per block of redeemed points.'),
                    ])->columns(3),
            ]);
    }
}
