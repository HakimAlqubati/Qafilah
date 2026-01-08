<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\ProductUnit;
use App\Models\Unit;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class UnitsRepeater
{
    /**
     * Repeater يعمل كهيكل داخلي، لكن يظهر كحقول عادية
     */
    public static function make(): Repeater
    {
        return Repeater::make('units')
            ->label('  ') // بدون label
            ->columnSpanFull()

            // إخفاء كل أزرار التحكم
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->collapsible(false)

            // عنصر واحد فقط
            ->minItems(1)
            ->maxItems(1)
            ->defaultItems(1)

            // الوحدة الافتراضية من المنتج
            ->default(function ($get) {
                $productId = $get('product_id');
                if (!$productId) {
                    // استخدم الوحدة الافتراضية من النظام
                    $defaultUnit = Unit::active()->where('is_default', true)->first();
                    return $defaultUnit ? [['unit_id' => $defaultUnit->id]] : [];
                }

                $productUnit = ProductUnit::where('product_id', $productId)->first();
                return $productUnit ? [['unit_id' => $productUnit->unit_id]] : [];
            })

            // بدون label لكل عنصر
            ->itemLabel(null)

            // تخطيط أفقي للحقول
            ->columns(2)

            ->schema([
                // الوحدة مخفية - تُجلب تلقائياً
                Hidden::make('unit_id'),

                TextInput::make('selling_price')
                    ->label(__('lang.selling_price'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('cost_price')
                    ->label(__('lang.cost_price'))
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('moq')
                    ->label(__('lang.moq'))
                    ->helperText(__('lang.moq_unit_helper'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->columnSpan(1),

                TextInput::make('stock')
                    ->label(__('lang.stock'))
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->columnSpan(1),
            ]);
    }
}
