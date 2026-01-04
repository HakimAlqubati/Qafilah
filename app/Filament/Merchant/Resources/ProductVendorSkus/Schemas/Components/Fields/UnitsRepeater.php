<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\Product;
use App\Models\ProductUnit;
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

            // ✅ default state (مهم جدًا حتى يظهر بدون ضغط في أول عرض)
            ->default(function (callable $get) {
                $productId = $get('product_id');
                if (! $productId) return [];

                $units = ProductUnit::getAvailableUnitsForProduct((int) $productId);

                if ($units->count() === 1 && ($units->first()?->is_default ?? false)) {
                    return [[
                        'unit_id'    => $units->first()->id,
                        'is_default' => true,
                    ]];
                }

                return [];
            })

            // ✅ إذا صار state فارغ لأي سبب (خصوصًا بعد تغيير المنتج) عبّئه
            ->afterStateHydrated(function (Repeater $component, $state, callable $get) {
                $productId = $get('product_id');
                if (! $productId) return;

                if (! empty($state)) return;

                $units = ProductUnit::getAvailableUnitsForProduct((int) $productId);

                if ($units->count() === 1 && ($units->first()?->is_default ?? false)) {
                    $component->state([[
                        'unit_id'    => $units->first()->id,
                        'is_default' => true,
                    ]]);
                }
            })

            ->itemLabel(fn (array $state): ?string =>
                Unit::find($state['unit_id'] ?? null)?->name ?? __('lang.add_unit')
            )

            // ✅ لا تعتمد على defaultItems هنا
            ->defaultItems(0)

            ->addActionLabel(__('lang.add_unit'))
            ->reorderable(fn ($get) => ($pid = $get('product_id')) ? self::canManageMultipleUnits($pid) : true)
            ->reorderableWithButtons(fn ($get) => ($pid = $get('product_id')) ? self::canManageMultipleUnits($pid) : true)
            ->columns(3)
            ->key(fn ($get) => 'units_' . ($get('product_id') ?? 'new'))
            ->minItems(fn ($get) => ($pid = $get('product_id')) ? (self::isDefaultOnlyByAvailableUnits($pid) ? 1 : 0) : 0)
            ->maxItems(fn ($get) => ($pid = $get('product_id')) ? (self::isDefaultOnlyByAvailableUnits($pid) ? 1 : null) : null)
            ->deletable(fn ($get) => ($pid = $get('product_id')) ? self::canManageMultipleUnits($pid) : true)
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
                    // $showProductUnitsOnly = filter_var(
                    //     Setting::getSetting('merchant_show_product_units_only', false),
                    //     FILTER_VALIDATE_BOOLEAN
                    // );
                    //
                    // // If setting is disabled (false), show all active units
                    // if (!$showProductUnitsOnly) {
                    //     return Unit::active()->pluck('name', 'id');
                    // }

                    // Setting is enabled - show only product-related units
                    // Get product_id from parent form
                    $productId = $get('../../product_id');

                    // استخدام الدالة الجديدة من الموديل
                    $units =  ProductUnit::getAvailableUnitsForProduct($productId);

                    return $units->pluck('name', 'id')->toArray();
                })
                ->default(function ($get) {
                    $productId = $get('../../product_id');
                    if (! $productId) return null;

                    $units = ProductUnit::getAvailableUnitsForProduct((int) $productId);

                    return ($units->count() === 1 && ($units->first()?->is_default ?? false))
                        ? $units->first()->id
                        : null;
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

//            Toggle::make('is_default')
//                ->label(__('lang.is_default_unit'))
//                ->helperText(__('lang.is_default_unit_helper'))
//                ->inline(false)
//                ->columnSpan(1),

//            Select::make('status')
//                ->label(__('lang.status'))
//                ->options([
//                    'active' => __('lang.active'),
//                    'inactive' => __('lang.inactive'),
//                ])
//                ->default('active')
//                ->required()
//                ->columnSpan(1),

//            TextInput::make('sort_order')
//                ->label(__('lang.sort_order'))
//                ->helperText(__('lang.sort_order_helper'))
//                ->numeric()
//                ->default(0)
//                ->columnSpan(1),
        ];
    }

    /**
     * Check if proper non-default units exist
     */
    protected static function isNotDefaultUnitOnly($productId): bool
    {
        $units = ProductUnit::where('product_id', $productId)->with('unit')->get();

        if ($units->isEmpty()) {
            return false;
        }

        if ($units->count() > 1) {
            return true;
        }

        $unit = $units->first()->unit;
        if ($unit && $unit->is_default) {
            return false;
        }

        return true;
    }

    protected static function isDefaultOnlyByAvailableUnits($productId): bool
    {
        $units = ProductUnit::getAvailableUnitsForProduct((int) $productId);

        return $units->count() === 1 && (bool) ($units->first()?->is_default ?? false);
    }

    protected static function canManageMultipleUnits($productId): bool
    {
        // إذا default-only => لا إضافة/حذف/ترتيب
        return ! self::isDefaultOnlyByAvailableUnits($productId);
    }
}


