<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class VariantsUnitsRepeater
{
    /**
     * Get the variants units repeater for products with variants in create mode
     */
    public static function make(): Repeater
    {
        return Repeater::make('variants_units')
            ->label(__('lang.configure_variants'))
            ->columnSpanFull()
            ->collapsible()
            ->collapsed(false)
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->itemLabel(fn(array $state): ?string => $state['variant_label'] ?? __('lang.variant'))
            ->visible(function ($get, $operation) {
                $productId = $get('product_id');
                if (!$productId) {
                    return false;
                }
                $product = Product::with(['attributesDirect'])->find($productId);
                $hasVariants = $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();

                if ($operation === 'create') {
                    $selectedVariants = $get('selected_variants') ?? [];
                    return $hasVariants && !empty($selectedVariants);
                }

                if ($operation === 'edit') {
                    $variantsUnits = $get('variants_units') ?? [];
                    return $hasVariants && !empty($variantsUnits);
                }

                return false;
            })
            ->schema([
                Placeholder::make('variant_display')
                    ->label(__('lang.variant'))
                    ->content(fn($get) => $get('variant_label') ?? '-')
                    ->columnSpanFull(),

                Hidden::make('variant_id'),
                Hidden::make('variant_label'),
                Hidden::make('sku_id'),

                self::nestedUnitsRepeater(),
            ]);
    }

    /**
     * Get the nested units repeater
     */
    private static function nestedUnitsRepeater(): Repeater
    {
        return Repeater::make('units')
            ->label(__('lang.unit_prices'))
            ->columnSpanFull()
            ->collapsible()
            ->collapsed(false)
            ->default(function () {
                // اختيار الوحدة الافتراضية تلقائياً
                $defaultUnit = Unit::active()->where('is_default', true)->first();

                if ($defaultUnit) {
                    return [[
                        'unit_id' => $defaultUnit->id,
                        'package_size' => 1,
                        'moq' => 1,
                        'stock' => 0,
                        'status' => 'active',
                    ]];
                }

                return [];
            })
            ->itemLabel(
                fn(array $state): ?string =>
                Unit::find($state['unit_id'])?->name ?? __('lang.add_unit')
            )
            ->defaultItems(0)
            ->addActionLabel(__('lang.add_unit'))
            ->reorderable(true)
            ->reorderableWithButtons()
            ->columns(3)
            ->schema(self::getUnitFields());
    }

    /**
     * Get the unit fields schema
     */
    private static function getUnitFields(): array
    {
        return [
            Select::make('unit_id')
                ->label(__('lang.unit'))
                ->options(function ($get) {
                    $productId = $get('../../../../product_id');

                    // إذا المنتج له وحدات محددة، اعرضها
                    if ($productId) {
                        $productUnits = ProductUnit::getAvailableUnitsForProduct($productId);
                        if ($productUnits->isNotEmpty()) {
                            return $productUnits->pluck('name', 'id')->toArray();
                        }
                    }

                    // وإلا اعرض جميع الوحدات النشطة
                    return Unit::active()->pluck('name', 'id')->toArray();
                })
                ->default(fn() => Unit::active()->where('is_default', true)->first()?->id)
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->columnSpan(1),

            TextInput::make('package_size')
                ->label(__('lang.package_size'))
                ->helperText(__('lang.package_size_helper'))
                ->numeric()
                ->required()
                ->minValue(1)
                ->default(1)
                ->columnSpan(1),

            TextInput::make('moq')
                ->label(__('lang.moq'))
                ->helperText(__('lang.moq_unit_helper'))
                ->numeric()
                ->required()
                ->minValue(1)
                ->default(1)
                ->columnSpan(1),

            TextInput::make('cost_price')
                ->label(__('lang.unit_cost_price'))
                ->helperText(__('lang.unit_cost_price_helper'))
                ->numeric()
                ->nullable()
                ->columnSpan(1),

            TextInput::make('selling_price')
                ->label(__('lang.unit_selling_price'))
                ->helperText(__('lang.unit_selling_price_helper'))
                ->numeric()
                ->required()
                ->columnSpan(1),

            TextInput::make('stock')
                ->label(__('lang.unit_stock'))
                ->helperText(__('lang.unit_stock_helper'))
                ->numeric()
                ->required()
                ->default(0)
                ->minValue(0)
                ->columnSpan(1),

            Select::make('status')
                ->label(__('lang.status'))
                ->options([
                    'active' => __('lang.active'),
                    'inactive' => __('lang.inactive'),
                ])
                ->default('active')
                ->required()
                ->columnSpan(1),

            TextInput::make('sort_order')
                ->label(__('lang.sort_order'))
                ->helperText(__('lang.sort_order_helper'))
                ->numeric()
                ->default(0)
                ->columnSpan(1),
        ];
    }
}
