<?php

namespace App\Filament\Resources\AttributeValues\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\{
    TextColumn,
    BadgeColumn,
    IconColumn,
    ToggleColumn
};
use App\Models\AttributeValue;

class AttributeValuesTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->columns([

                // 🔹 الخاصية المرتبطة
                TextColumn::make('attribute.name')
                    ->label(__('lang.attribute'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-sparkles')
                    ->description(fn($record) => $record->attribute?->code),

                // 🔹 القيمة
                TextColumn::make('value')
                    ->label(__('lang.value'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->copyable()
                    ->tooltip('Click to copy value'),

                // 🔹 نوع الإدخال من الخاصية
                BadgeColumn::make('attribute.input_type')
                    ->label(__('lang.type'))
                    ->colors([
                        'primary' => 'select',
                        'warning' => 'radio',
                        'info'    => 'text',
                        'success' => 'number',
                        'danger'  => 'boolean',
                        'gray'    => 'date',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state ?? '')),

                // 🔹 ترتيب العرض
                TextColumn::make('sort_order')
                    ->label(__('lang.type'))
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-o-bars-3')
                    ->badge()
                    ->color('gray'),

                // 🔹 الحالة (اختياري لو عندك حقل active)
                IconColumn::make('is_active')
                    ->label(__('lang.active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // 🔹 التاريخ
                TextColumn::make('created_at')
                    ->label(__('lang.created'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('lang.updated'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /* ============================================================
             | 🔍 الفلاتر
             |============================================================ */
            ->filters([
                Tables\Filters\SelectFilter::make('attribute_id')
                    ->label(__('lang.attribute'))
                    ->relationship('attribute', 'name')
                    ->searchable(),
            ])

            /* ============================================================
             | ⚙️ الأكشنات (Actions)
             |============================================================ */
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary'),
            ])

            /* ============================================================
             | 🧰 Toolbar / Bulk Actions
             |============================================================ */
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ]);
    }
}
