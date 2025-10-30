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
        return $table
            ->columns([

                // 🔹 الخاصية المرتبطة
                TextColumn::make('attribute.name')
                    ->label('Attribute')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-sparkles')
                    ->description(fn ($record) => $record->attribute?->code),

                // 🔹 القيمة
                TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->copyable()
                    ->tooltip('Click to copy value'),

                // 🔹 نوع الإدخال من الخاصية
                BadgeColumn::make('attribute.input_type')
                    ->label('Type')
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
                    ->label('Order')
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-o-bars-3')
                    ->badge()
                    ->color('gray'),

                // 🔹 الحالة (اختياري لو عندك حقل active)
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // 🔹 التاريخ
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /* ============================================================
             | 🔍 الفلاتر
             |============================================================ */
            ->filters([
                Tables\Filters\SelectFilter::make('attribute_id')
                    ->label('Attribute')
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
