<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()->defaultSort('id', 'desc')
            ->columns([
                // ID Column
                TextColumn::make('id')
                    ->label(__('lang.id'))
                    ->sortable(),

                // Avatar Column
                ImageColumn::make('avatar')
                    ->label(__('lang.avatar'))
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->toggleable(),

                // Name Column
                TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),

                // Email Column
                TextColumn::make('email')
                    ->label(__('lang.email'))
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->copyable(),

                // Phone Column
                TextColumn::make('phone')
                    ->label(__('lang.phone'))
                    ->icon('heroicon-m-phone')
                    ->searchable()
                    ->toggleable(),

                // Status Column
                TextColumn::make('status')
                    ->label(__('lang.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        User::STATUS_ACTIVE => 'success',
                        User::STATUS_INACTIVE => 'warning',
                        User::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        User::STATUS_ACTIVE => __('lang.active'),
                        User::STATUS_INACTIVE => __('lang.inactive'),
                        User::STATUS_SUSPENDED => __('lang.suspended'),
                        default => $state,
                    })
                    ->sortable(),

                // Is Active Column
                IconColumn::make('is_active')
                    ->label(__('lang.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                // Vendor Relationship Column
                TextColumn::make('vendor.name')
                    ->label(__('lang.vendor'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('lang.no_vendor'))
                    ->toggleable(),

                // Last Login Column
                TextColumn::make('last_login_at')
                    ->label(__('lang.last_login_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Created At Column
                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Status
                SelectFilter::make('status')
                    ->label(__('lang.status'))
                    ->options([
                        User::STATUS_ACTIVE => __('lang.active'),
                        User::STATUS_INACTIVE => __('lang.inactive'),
                        User::STATUS_SUSPENDED => __('lang.suspended'),
                    ]),

                // Filter by Active Status
                TernaryFilter::make('is_active')
                    ->label(__('lang.is_active'))
                    ->placeholder(__('lang.all'))
                    ->trueLabel(__('lang.active'))
                    ->falseLabel(__('lang.inactive')),

                // Filter by Vendor
                SelectFilter::make('vendor')
                    ->label(__('lang.vendor'))
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                // Filter by Role
                SelectFilter::make('roles')
                    ->label(__('lang.roles'))
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                // Row Actions
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Bulk Actions (Checkboxes)
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
