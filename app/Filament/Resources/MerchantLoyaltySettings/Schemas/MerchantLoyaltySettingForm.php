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
                Section::make(__('lang.general_settings'))
                    ->description(__('lang.manage_active_status_and_merchant_assignment'))
                    ->schema([
                        Select::make('merchant_id')
                            ->relationship('merchant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label(__('lang.vendor'))
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label(__('lang.active'))
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Section::make(__('lang.earning_rules'))
                    ->description(__('lang.define_how_customers_earn_points_based_on_their_spend'))
                    ->schema([
                        TextInput::make('earning_spend_amount')
                            ->label(__('lang.spend_amount_required'))
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->helperText(__('lang.the_amount_a_customer_needs_to_spend_to_earn_the_reward_points')),

                        TextInput::make('earning_reward_points')
                            ->label(__('lang.reward_points_earned'))
                            ->numeric()
                            ->integer()
                            ->required()
                            ->helperText(__('lang.points_earned_for_every_multiple_of_the_spend_amount')),
                    ])->columns(2),

                Section::make(__('lang.redemption_rules'))
                    ->description(__('lang.define_how_customers_can_redeem_their_points'))
                    ->schema([
                        TextInput::make('min_points_to_redeem')
                            ->label(__('lang.minimum_points_to_redeem'))
                            ->numeric()
                            ->integer()
                            ->required()
                            ->helperText(__('lang.the_minimum_points_balance_required_before_redemption_is_allowed')),

                        TextInput::make('redemption_points_block')
                            ->label(__('lang.redemption_points_block'))
                            ->numeric()
                            ->integer()
                            ->required()
                            ->helperText(__('lang.points_must_be_redeemed_in_multiples_of_this_block_size')),

                        TextInput::make('redemption_discount_value')
                            ->label(__('lang.discount_value_per_block'))
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->helperText(__('lang.the_monetary_discount_applied_per_block_of_redeemed_points')),
                    ])->columns(3),
            ]);
    }
}
