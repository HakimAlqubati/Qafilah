<?php

namespace App\Filament\Resources\CustomerLoyaltyWallets\RelationManagers;

use App\Models\LoyaltyTransaction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyTransactions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lang.loyalty_transactions');
    }

    public static function getModelLabel(): string
    {
        return __('lang.loyalty_transaction');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->loyaltyTransactions()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->label(__('lang.transaction_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LoyaltyTransaction::TYPE_EARNED => 'success',
                        LoyaltyTransaction::TYPE_REDEEMED => 'warning',
                        LoyaltyTransaction::TYPE_EARNED_REVERSED => 'danger',
                        LoyaltyTransaction::TYPE_REDEEMED_RESTORED => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => LoyaltyTransaction::TYPES[$state] ?? $state),

                TextColumn::make('points')
                    ->label(__('lang.points'))
                    ->weight('bold')
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? "+{$state}" : (string) $state))
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label(__('lang.order'))
                    ->default('-')
                    ->searchable(),

                TextColumn::make('description')
                    ->label(__('lang.description'))
                    ->wrap()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
