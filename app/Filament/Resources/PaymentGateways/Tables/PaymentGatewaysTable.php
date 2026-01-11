<?php

namespace App\Filament\Resources\PaymentGateways\Tables;

use App\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use App\Models\PaymentGateway;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentGatewaysTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->columns([
                // 1. ID
                TextColumn::make('id')
                    ->label(__('lang.id'))
                    ->sortable(),

                // 2. Name
                TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // 3. Code
                TextColumn::make('code')
                    ->label(__('lang.code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                // 4. Type
                TextColumn::make('type')
                    ->label(__('lang.gateway_type'))
                    ->formatStateUsing(fn(string $state): string => PaymentGatewayResource::getTypeLabel($state))
                    ->badge()
                    ->color(fn(string $state): string => PaymentGatewayResource::getTypeBadgeColor($state))
                    ->icon(fn(string $state): string => PaymentGatewayResource::getTypeIcon($state))
                    ->sortable(),

                // 5. Mode
                TextColumn::make('mode')
                    ->label(__('lang.gateway_mode'))
                    ->formatStateUsing(fn(string $state): string => PaymentGatewayResource::getModeLabel($state))
                    ->badge()
                    ->color(fn(string $state): string => PaymentGatewayResource::getModeBadgeColor($state))
                    ->sortable(),

                // 6. Is Active
                IconColumn::make('is_active')
                    ->label(__('lang.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->sortable(),

                // 7. Transactions Count
                TextColumn::make('transactions_count')
                    ->label(__('lang.transactions'))
                    ->counts('transactions')
                    ->default(0)
                    ->alignCenter()
                    ->sortable(),

                // 8. Created At
                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Type
                SelectFilter::make('type')
                    ->label(__('lang.gateway_type'))
                    ->options([
                        'electronic' => __('lang.gateway_type_electronic'),
                        'cash' => __('lang.gateway_type_cash'),
                        'transfer' => __('lang.gateway_type_transfer'),
                    ]),

                // Filter by Mode
                SelectFilter::make('mode')
                    ->label(__('lang.gateway_mode'))
                    ->options([
                        'sandbox' => __('lang.gateway_mode_sandbox'),
                        'live' => __('lang.gateway_mode_live'),
                    ]),

                // Filter by Status
                SelectFilter::make('is_active')
                    ->label(__('lang.status'))
                    ->options([
                        '1' => __('lang.active'),
                        '0' => __('lang.inactive'),
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
