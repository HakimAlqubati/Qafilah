<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

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

                // Name Column
                TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),

                // Email Column
                TextColumn::make('email')
                    ->label(__('lang.email'))
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                // Vendor Relationship Column
                // Uses dot notation to access the vendor name
                TextColumn::make('vendor.name')
                    ->label(__('lang.vendor'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('lang.no_vendor'))
                    ->toggleable(),

                // Roles Column (Spatie)
                // TextColumn::make('roles.name')
                //     ->badge()
                //     ->label('Roles')
                //     ->separator(',')
                //     ->color(fn (string $state): string => match ($state) {
                //         'admin', 'super_admin' => 'danger',
                //         'manager' => 'warning',
                //         default => 'success',
                //     }),

                // Created At Column
                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Vendor
                SelectFilter::make('vendor')
                    ->label(__('lang.vendor'))
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                // Filter by Role (if you want to filter users by their role)
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
