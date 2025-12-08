<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('lang.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label(__('lang.customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vendor.name')
                    ->label(__('lang.vendor'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('lang.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_CONFIRMED => 'info',
                        Order::STATUS_PROCESSING => 'primary',
                        Order::STATUS_SHIPPED => 'info',
                        Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        Order::STATUS_RETURNED => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::STATUSES[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('lang.payment_status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::PAYMENT_PENDING => 'warning',
                        Order::PAYMENT_PARTIAL => 'info',
                        Order::PAYMENT_PAID => 'success',
                        Order::PAYMENT_REFUNDED => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::PAYMENT_STATUSES[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label(__('lang.items'))
                    ->counts('items')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('total')
                    ->label(__('lang.total'))
                    ->money('YER')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('placed_at')
                    ->label(__('lang.placed_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('lang.status'))
                    ->options(Order::STATUSES)
                    ->multiple(),

                SelectFilter::make('payment_status')
                    ->label(__('lang.payment_status'))
                    ->options(Order::PAYMENT_STATUSES)
                    ->multiple(),

                SelectFilter::make('customer_id')
                    ->label(__('lang.customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('vendor_id')
                    ->label(__('lang.vendor'))
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

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
