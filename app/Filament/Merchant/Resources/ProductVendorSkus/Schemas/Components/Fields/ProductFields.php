<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Helpers\SkuGenerator;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\ProductVendorSku;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

class ProductFields
{
    /**
     * Get the product select field
     */
    public static function productSelect(): Select
    {
        return Select::make('product_id')
            ->label(__('lang.product'))
            ->options(function ($get) {
                $subCategoryId = $get('sub_category_id');
                $mainCategoryId = $get('main_category_id');

                return Product::whereIn('category_id', [
                    $subCategoryId,
                    $mainCategoryId
                ])
                    ->pluck('name', 'id');
            })
            ->live()
            ->afterStateUpdated(function ($set, $get) {
                $set('variant_id', null);
                $set('attributes', []);

                $productId = $get('product_id');
                if (! $productId) {
                    $set('units', []);
                    return;
                }

                $product = Product::with(['attributesDirect'])->find($productId);
                $hasVariantAttributes = $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();

                // Generate unique vendor_sku
                $vendorId = auth()->user()->vendor_id ?? 0;
                $uniqueSku = SkuGenerator::generate($productId, $vendorId);
                $set('vendor_sku', $uniqueSku);

                if (! $hasVariantAttributes) {
                    $variant = ProductVariant::where('product_id', $productId)->active()->first();
                    if ($variant) {
                        $set('variant_id', $variant->id);
                    }
                }

                // Set units with the product's unit
                $productUnit = ProductUnit::where('product_id', $productId)->first();
                if ($productUnit) {
                    $set('units', [[
                        'unit_id' => $productUnit->unit_id,
                        'package_size' => 1,
                        'moq' => 1,
                        'stock' => 0,
                    ]]);
                }
            })

            ->required();
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
//    public static function currencySelect(): Select
//    {
//        return Select::make('currency_id')
//            ->label(__('lang.currency'))
//            ->options(Currency::active()->pluck('code', 'id'))
//            ->default(fn() => Currency::default()->first()?->id)
//            ->searchable()
//            ->preload()
//            ->required()
//            ->visible(fn($get) => filled($get('product_id')))
//            ->validationMessages([
//                'unique' => __('This product variant is already added with this currency.'),
//            ])
//            ->rules([
//                function (Get $get, Component $component) {
//                    return function (string $attribute, $value, \Closure $fail) use ($get, $component) {
//                        $productId = $get('product_id');
//                        $variantId = $get('variant_id');
//                        $vendorId = $get('vendor_id');
//
//                        if (!$productId || !$vendorId) {
//                            return;
//                        }
//
//                        $query = ProductVendorSku::where('product_id', $productId)
//                            ->where('vendor_id', $vendorId)
//                            ->where('currency_id', $value);
//
//                        // إذا كان هناك متغير محدد، نتحقق منه أيضاً
//                        if ($variantId) {
//                            $query->where('variant_id', $variantId);
//                        } else {
//                            $query->whereNull('variant_id');
//                        }
//
//                        // Ignore current record if editing
//                        $record = $component->getRecord();
//                        if ($record) {
//                            $query->where('id', '!=', $record->id);
//                        }
//
//                        if ($query->exists()) {
//                            $fail(__('This product is already added with this currency.'));
//                        }
//                    };
//                },
//            ]);
//    }

    /**
     * Get the hidden vendor_id field
     */
    public static function vendorIdHidden(): Hidden
    {
        return Hidden::make('vendor_id')
            ->default(fn() => auth()->user()->vendor_id);
    }

    /**
     * Get the hidden variant_id field
     */
    public static function variantIdHidden(): Hidden
    {
        return Hidden::make('variant_id')
            ->dehydrated();
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
            self::variantIdHidden(),
            self::vendorSku(),
            self::currencySelect(),
            self::vendorIdHidden(),
            self::productIdHidden(),
        ];
    }
}
