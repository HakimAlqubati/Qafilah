<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Helpers\SkuGenerator;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ProductFields
{
    /**
     * Get the product search field (global search)
     * عند البحث يتم تعبئة: الفئات + المنتج + الأسعار
     */
    public static function productSearch(): Select
    {
        return Select::make('product_search')
            ->label(__('lang.search_product'))
            ->searchable()
            ->getSearchResultsUsing(function (string $search) {
                return Product::where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->limit(20)
                    ->pluck('name', 'id');
            })
            ->getOptionLabelUsing(fn($value) => Product::find($value)?->name)
            ->live()
            ->afterStateUpdated(function ($set, $state) {
                if (! $state) {
                    return;
                }

                $product = Product::with('category')->find($state);
                if (! $product) {
                    return;
                }

                // Set product_id
                $set('product_id', $product->id);

                // Set categories
                if ($product->category) {
                    if ($product->category->parent_id) {
                        $set('main_category_id', $product->category->parent_id);
                        $set('sub_category_id', $product->category->id);
                    } else {
                        $set('main_category_id', $product->category->id);
                        $set('sub_category_id', null);
                    }
                }

                // Generate unique vendor_sku
                $vendorId = auth()->user()->vendor_id ?? 0;
                $uniqueSku = SkuGenerator::generate($product->id, $vendorId);
                $set('vendor_sku', $uniqueSku);

                // Set units with the product's unit and prices
                self::setProductUnits($set, $product->id);

                // Clear this field after use
                $set('product_search', null);
            })
            ->dehydrated(false)
            ->columnSpanFull()
            ->placeholder(__('lang.search_product_placeholder'));
    }

    /**
     * Get the product select field (filtered by category)
     * يظهر المنتجات المفلترة حسب الفئة
     */
    public static function productSelect(): Select
    {
        return Select::make('product_id')
            ->label(__('lang.product'))
            ->searchable()
            ->options(function ($get, $state) {
                $subCategoryId = $get('sub_category_id');
                $mainCategoryId = $get('main_category_id');

                $categoryIds = array_filter([$subCategoryId, $mainCategoryId]);

                // Get products from selected categories
                $query = Product::query();

                if (!empty($categoryIds)) {
                    $query->whereIn('category_id', $categoryIds);
                }

                // Always include the currently selected product
                if ($state) {
                    $query->orWhere('id', $state);
                }

                return $query->pluck('name', 'id');
            })
            // يعرض اسم المنتج حتى لو لم يكن في الخيارات المبدئية
            ->getOptionLabelUsing(fn($value) => Product::find($value)?->name)
            ->live()
            ->afterStateUpdated(function ($set, $get) {
                $set('attributes', []);
                $set('product_search', null); // Clear search field

                $productId = $get('product_id');
                if (! $productId) {
                    $set('units', []);
                    return;
                }

                // Generate unique vendor_sku
                $vendorId = auth()->user()->vendor_id ?? 0;
                $uniqueSku = SkuGenerator::generate($productId, $vendorId);
                $set('vendor_sku', $uniqueSku);

                // Set units with the product's unit and prices
                self::setProductUnits($set, $productId);
            })
            ->required();
    }

    /**
     * Set units for the selected product
     */
    private static function setProductUnits($set, int $productId): void
    {
        $productUnit = ProductUnit::where('product_id', $productId)->first();
        if ($productUnit) {
            $set('units', [[
                'unit_id' => $productUnit->unit_id,
                'selling_price' => $productUnit->selling_price,
                'cost_price' => $productUnit->cost_price,
                'package_size' => $productUnit->package_size ?? 1,
                'moq' => 1,
                'stock' => 0,
            ]]);
        } else {
            // Use system default unit (no prices)
            $defaultUnit = Unit::active()->where('is_default', true)->first();
            if ($defaultUnit) {
                $set('units', [[
                    'unit_id' => $defaultUnit->id,
                    'selling_price' => null,
                    'cost_price' => null,
                    'package_size' => 1,
                    'moq' => 1,
                    'stock' => 0,
                ]]);
            }
        }
    }

    /**
     * Get the vendor SKU text input field
     */
    public static function vendorSku(): TextInput
    {
        return TextInput::make('vendor_sku')
            ->label(__('lang.vendor_sku'))
            ->helperText(__('lang.vendor_sku_helper'))
            ->maxLength(255)
            ->required()
            ->visible(fn($get) => filled($get('product_id')));
    }

    /**
     * Get the currency select field
     */
    public static function currencySelect(): Hidden
    {
        return Hidden::make('currency_id')
            ->default(fn() => Currency::default()->value('id'))
            ->dehydrated(true)
            ->required();
    }

    /**
     * Get the hidden vendor_id field
     */
    public static function vendorIdHidden(): Hidden
    {
        return Hidden::make('vendor_id')
            ->default(fn() => auth()->user()->vendor_id);
    }

    /**
     * Get the hidden product_id field
     */
    public static function productIdHidden(): Hidden
    {
        return Hidden::make('product_id')
            ->dehydrated();
    }

    /**
     * Get all product-related fields
     */
    public static function make(): array
    {
        return [
            self::productSelect(),
            self::vendorSku(),
            self::currencySelect(),
            self::vendorIdHidden(),
            self::productIdHidden(),
        ];
    }
}
