<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->defaultSort('id', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label(__('lang.id'))
                    ->sortable()
                    ->alignCenter()
                    ->toggleable()
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label(__('lang.description'))
                    ->limit(30)
                    ->searchable()
                    ->tooltip(fn($record) => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\ToggleColumn::make('active')
                    ->alignCenter()
                    ->label(__('lang.active')),

                \Filament\Tables\Columns\ToggleColumn::make('is_default')
                    ->alignCenter()
                    ->label(__('lang.is_default_unit'))
                    ->toggleable(isToggledHiddenByDefault: false)

                    ->updateStateUsing(function ($record, $state) {
                        try {
                            $record->update(['is_default' => $state]);
                            Notification::make()
                                ->title(__('lang.success'))
                                ->body(__('lang.done'))
                                ->success()
                                ->duration(500)
                                ->send();
                            return $state;
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('lang.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                            $record->refresh(); // جلب القيمة الفعلية من قاعدة البيانات
                            return $record->is_default;
                        }
                    })
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label(__('lang.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('lang.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
