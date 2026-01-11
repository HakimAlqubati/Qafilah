<?php

namespace App\Filament\Resources\PaymentTransactions\Tables;

use App\Filament\Resources\PaymentTransactions\PaymentTransactionResource;
use App\ValueObjects\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->columns([
                // 1. ID / UUID
                TextColumn::make('uuid')
                    ->label(__('lang.transaction_uuid'))
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 2. Gateway
                TextColumn::make('gateway.name')
                    ->label(__('lang.payment_gateway'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                // 3. User
                TextColumn::make('user.name')
                    ->label(__('lang.transaction_user'))
                    ->placeholder(__('lang.guest'))
                    ->searchable()
                    ->sortable(),

                // 3.5 Creator
                TextColumn::make('creator.name')
                    ->label(__('lang.created_by'))
                    ->placeholder(__('lang.system'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 4. Amount
                TextColumn::make('amount')
                    ->label(__('lang.transaction_amount'))
                    ->formatStateUsing(fn($state) => Money::make($state))->sortable()
                    ->weight('bold'),

                // 5. Status
                TextColumn::make('status')
                    ->label(__('lang.transaction_status'))
                    ->formatStateUsing(fn(string $state): string => PaymentTransactionResource::getStatusLabel($state))
                    ->badge()
                    ->color(fn(string $state): string => PaymentTransactionResource::getStatusBadgeColor($state))
                    ->sortable(),

                // 6. Reference ID
                TextColumn::make('reference_id')
                    ->label(__('lang.transaction_reference'))
                    ->searchable()
                    ->sortable(),

                // 7. Created At
                TextColumn::make('created_at')
                    ->label(__('lang.transaction_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gateway_id')
                    ->label(__('lang.payment_gateway'))
                    ->relationship('gateway', 'name'),

                SelectFilter::make('status')
                    ->label(__('lang.transaction_status'))
                    ->options([
                        'pending' => __('lang.pending'),
                        'paid' => __('lang.paid'),
                        'failed' => __('lang.failed'),
                        'refunded' => __('lang.refunded'),
                        'reviewing' => __('lang.reviewing'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
