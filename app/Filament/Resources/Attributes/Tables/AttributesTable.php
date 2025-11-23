<?php

namespace App\Filament\Resources\Attributes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Models\Attribute; // تأكد من استيراد نموذج الخاصية

class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Name (الاسم الظاهر)
                TextColumn::make('name')
                    ->label(__('lang.name'))
                    ->searchable()
                    ->sortable(),

                // 2. Code (الكود التقني)
                TextColumn::make('code')
                    ->label(__('lang.code'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // يمكن إخفاؤه افتراضياً

                // 3. Input Type (نوع الإدخال)
                TextColumn::make('input_type')
                    ->label(__('lang.type'))
                    ->badge() // لعرضها كشارة ملونة
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => Attribute::inputTypeOptions()[$state] ?? $state), // ترجمة الكود إلى اسم مفهوم

                // 4. Is Required (هل هو مطلوب)
                IconColumn::make('is_required')
                    ->label(__('lang.type'))->alignCenter()
                    ->boolean()
                    ->sortable(),

                // 5. Active (الحالة - هل الخاصية نشطة)
                IconColumn::make('active')
                    ->label(__('lang.active'))->alignCenter()
                    ->boolean()
                    ->sortable(),

                // 6. Values Count (عدد القيم المتاحة - Color: 5, Size: 3)
                TextColumn::make('values_count')
                    ->label(__('lang.value'))->alignCenter()
                    ->counts('values') // يعتمد على العلاقة `values()` في النموذج
                    ->sortable(),

                // 7. Timestamps
                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                // 1. فلتر حسب نوع الإدخال
                SelectFilter::make('input_type')
                    ->label(__('lang.type'))
                    ->options(Attribute::inputTypeOptions()),

                // 2. فلتر للخصائص المطلوبة/غير المطلوبة
                TernaryFilter::make('is_required')
                    ->label(__('lang.type'))
                    ->trueLabel(__('lang.type'))
                    ->falseLabel(__('lang.type')),

                // 3. فلتر للحالة (نشط/غير نشط)
                TernaryFilter::make('active')
                    ->label(__('lang.status'))
                    ->trueLabel(__('lang.active'))
                    ->falseLabel(__('lang.inactive')),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc'); // ترتيب افتراضي
    }
}
