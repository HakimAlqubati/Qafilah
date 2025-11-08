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
                    ->label('Attribute Name')
                    ->searchable()
                    ->sortable(),

                // 2. Code (الكود التقني)
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // يمكن إخفاؤه افتراضياً

                // 3. Input Type (نوع الإدخال)
                TextColumn::make('input_type')
                    ->label('Input Type')
                    ->badge() // لعرضها كشارة ملونة
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Attribute::inputTypeOptions()[$state] ?? $state), // ترجمة الكود إلى اسم مفهوم

                // 4. Is Required (هل هو مطلوب)
                IconColumn::make('is_required')
                    ->label('Required?')->alignCenter()
                    ->boolean()
                    ->sortable(),

                // 5. Active (الحالة - هل الخاصية نشطة)
                IconColumn::make('active')
                    ->label('Active')->alignCenter()
                    ->boolean()
                    ->sortable(),
                
                // 6. Values Count (عدد القيم المتاحة - Color: 5, Size: 3)
                TextColumn::make('values_count')
                    ->label('Values Count')->alignCenter()
                    ->counts('values') // يعتمد على العلاقة `values()` في النموذج
                    ->sortable(),
                
                // 7. Timestamps
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                // 1. فلتر حسب نوع الإدخال
                SelectFilter::make('input_type')
                    ->label('Filter by Input Type')
                    ->options(Attribute::inputTypeOptions()),

                // 2. فلتر للخصائص المطلوبة/غير المطلوبة
                TernaryFilter::make('is_required')
                    ->label('Required Status')
                    ->trueLabel('Required')
                    ->falseLabel('Not Required'),

                // 3. فلتر للحالة (نشط/غير نشط)
                TernaryFilter::make('active')
                    ->label('Activity Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
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