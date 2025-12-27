<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Unit;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class UnitsRepeater
{
    /**
     * Get the units repeater for simple products (no variants) or edit mode
     */
    public static function make(): Repeater
    {
        return Repeater::make('units')
            ->label(__('lang.unit_prices'))
            ->columnSpanFull()
            ->collapsible()
            ->collapsed(false)
            ->itemLabel(
                fn(array $state): ?string =>
                Unit::find($state['unit_id'])?->name ?? __('lang.add_unit')
            )
            ->defaultItems(0)
            ->addActionLabel(__('lang.add_unit'))
            ->reorderable(true)
            ->reorderableWithButtons()
            ->columns(3)
            ->visible(function ($get, $operation) {
                $productId = $get('product_id');
                if (!$productId) {
                    return true; // Show by default when no product selected
                }
                $product = Product::with(['attributesDirect'])->find($productId);
                $hasVariants = $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();

                // Show for simple products (no variants) OR in edit mode
                return !$hasVariants || $operation === 'edit';
            })
            ->schema(self::getUnitFields());
    }

    /**
     * Get the unit fields schema
     */
    public static function getUnitFields(): array
    {
        return [
            Select::make('unit_id')
                ->label(__('lang.unit'))
                ->options(function ($get) {
                    // Check if we should show only product-related units
                    // getSetting returns string '1' or '0' from DB, default to false (show all units)
                    $showProductUnitsOnly = filter_var(
                        Setting::getSetting('merchant_show_product_units_only', false),
                        FILTER_VALIDATE_BOOLEAN
                    );

                    // If setting is disabled (false), show all active units
                    if (!$showProductUnitsOnly) {
                        return Unit::active()->pluck('name', 'id');
                    }

                    // Setting is enabled - show only product-related units
                    // Get product_id from parent form
                    $productId = $get('../../product_id');
                    if (!$productId) {
                        return Unit::active()->pluck('name', 'id');
                    }

                    // Get only units associated with this product
                    $product = Product::with(['units.unit'])->find($productId);
                    if (!$product || $product->units->isEmpty()) {
                        // Fallback to all active units if no product units defined
                        return Unit::active()->pluck('name', 'id');
                    }

                    return $product->units
                        ->where('status', 'active')
                        ->mapWithKeys(fn($pu) => [$pu->unit_id => $pu->unit?->name ?? '-'])
                        ->filter()
                        ->toArray();
                })
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

            Toggle::make('is_default')
                ->label(__('lang.is_default_unit'))
                ->helperText(__('lang.is_default_unit_helper'))
                ->inline(false)
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
