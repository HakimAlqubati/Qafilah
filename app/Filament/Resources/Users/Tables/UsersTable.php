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
                    ->sortable(),

                // Name Column
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // Email Column
                TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                // Vendor Relationship Column
                // Uses dot notation to access the vendor name
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No Vendor')
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Vendor
                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),
                
                // Filter by Role (if you want to filter users by their role)
                SelectFilter::make('roles')
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