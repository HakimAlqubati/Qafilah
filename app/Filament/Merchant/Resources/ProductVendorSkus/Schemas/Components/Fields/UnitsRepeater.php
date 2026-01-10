<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\ProductUnit;
use App\Models\Unit;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class UnitsRepeater
{
    /**
     * Repeater للوحدات - يسمح للتاجر بإضافة/حذف الوحدات المرتبطة بالمنتج
     * @param string $currencySymbol رمز العملة لعرضه كـ prefix
     */
    public static function make(string $currencySymbol = ''): Repeater
    {
        return Repeater::make('units')
            ->label(__('lang.units'))
            ->columnSpanFull()

            // تفعيل الإضافة والحذف
            ->addable(true)
            ->deletable(true)
            ->reorderable(false)
            ->collapsible(true)

            // الحد الأدنى وحدة واحدة على الأقل
            ->minItems(1)
            ->defaultItems(0)

            // اسم زر الإضافة
            ->addActionLabel(__('lang.add_unit'))

            // label لكل عنصر
            ->itemLabel(function (array $state): ?string {
                if (empty($state['unit_id'])) {
                    return null;
                }
                $unit = Unit::find($state['unit_id']);
                return $unit?->name;
            })

            ->table([
                TableColumn::make(__('lang.unit'))->width('23%'),
                TableColumn::make(__('lang.selling_price'))->width('18%'),
                TableColumn::make(__('lang.cost_price'))->width('18%'),
                TableColumn::make(__('lang.moq_unit_helper'))->width('20%'),
                TableColumn::make(__('lang.stock'))->width('16%'),
            ])
            ->schema([
                // قائمة الوحدات المرتبطة بالمنتج
                Select::make('unit_id')
                    ->label(__('lang.unit'))
                    ->options(function ($get) {
                        $productId = $get('../../product_id');
                        if (!$productId) {
                            // إذا لم يكن هناك منتج، أظهر الوحدة الافتراضية فقط
                            return Unit::active()
                                ->where('is_default', true)
                                ->pluck('name', 'id');
                        }

                        // جلب الوحدات النشطة المرتبطة بالمنتج
                        $productUnits = ProductUnit::where('product_id', $productId)
                            ->active()
                            ->sellable()
                            ->with('unit')
                            ->get();

                        if ($productUnits->isEmpty()) {
                            // إذا لم توجد وحدات للمنتج، أظهر الوحدة الافتراضية
                            return Unit::active()
                                ->where('is_default', true)
                                ->pluck('name', 'id');
                        }

                        return $productUnits->pluck('unit.name', 'unit_id');
                    })
                    ->required()
                    ->live()
                    ->extraAlpineAttributes([
                        'style' => 'text-align: center; border: 2px solid #ccc;'
                    ])
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if (!$state) {
                            return;
                        }

                        $productId = $get('../../product_id');
                        if (!$productId) {
                            return;
                        }

                        // جلب الأسعار الافتراضية من ProductUnit
                        $productUnit = ProductUnit::where('product_id', $productId)
                            ->where('unit_id', $state)
                            ->first();

                        if ($productUnit) {
                            $set('selling_price', $productUnit->selling_price);
                            $set('cost_price', $productUnit->cost_price);
                        }
                    })
                    ->columnSpan(1),

                TextInput::make('selling_price')
                    ->label(__('lang.selling_price'))
                    ->numeric()
                    ->required()
                    ->prefix($currencySymbol)
                    ->extraAlpineAttributes([
                        'style' => 'text-align: center; border: 2px solid #ccc;'
                    ])
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('cost_price')
                    ->label(__('lang.cost_price'))
                    ->numeric()
                    ->nullable()
                    ->prefix($currencySymbol)
                    ->extraAlpineAttributes([
                        'style' => 'text-align: center; border: 2px solid #ccc;'
                    ])
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('moq')
                    ->label(__('lang.moq'))
                    ->numeric()
                    ->extraAlpineAttributes([
                        'style' => 'text-align: center; border: 2px solid #ccc;'
                    ])
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->columnSpan(1),

                TextInput::make('stock')
                    ->label(__('lang.stock'))
                    ->numeric()
                    ->extraAlpineAttributes([
                        'style' => 'text-align: center; border: 2px solid #ccc;'
                    ])
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->columnSpan(1),
            ]);
    }
}
