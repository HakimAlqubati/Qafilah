<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVendorSku;
use Filament\Forms\Components\CheckboxList;

class VariantsCheckboxList
{
    /**
     * Get the variants checkbox list for multi-variant selection
     * Only visible in create mode for products with variants
     */
    public static function make(): CheckboxList
    {
        return CheckboxList::make('selected_variants')
            ->label(__('lang.select_variants'))
            ->helperText(__('lang.select_variants_helper'))
            ->options(function ($get) {
                $productId = $get('product_id');
                if (!$productId) {
                    return [];
                }

                $product = Product::with(['attributesDirect'])->find($productId);
                $hasVariantAttributes = $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();

                if (!$hasVariantAttributes) {
                    return [];
                }

                // Get all variants with their attribute values
                $variants = ProductVariant::with(['values.attribute', 'values.attributeValue'])
                    ->where('product_id', $productId)
                    ->active()
                    ->get();

                // Get already added variants for this vendor
                $vendorId = auth()->user()->vendor_id ?? 0;
                $existingVariantIds = ProductVendorSku::where('vendor_id', $vendorId)
                    ->where('product_id', $productId)
                    ->whereNotNull('variant_id')
                    ->pluck('variant_id')
                    ->toArray();

                return $variants->mapWithKeys(function ($variant) use ($existingVariantIds) {
                    $label = $variant->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(' | ');
                    $isAdded = in_array($variant->id, $existingVariantIds);
                    if ($isAdded) {
                        $label .= ' ✓ (' . __('lang.already_added') . ')';
                    }
                    return [$variant->id => $label];
                })->toArray();
            })
            ->columns(2)
            ->gridDirection('row')
            ->rules([]) // Disable default 'in' validation - we handle validation in handleRecordCreation
            ->live()
            ->afterStateUpdated(function ($state, $set, $get) {
                // تحديث repeater المتغيرات عند تغيير الاختيارات
                $selectedVariantIds = $state ?? [];
                $productId = $get('product_id');

                if (empty($selectedVariantIds) || !$productId) {
                    $set('variants_units', []);
                    return;
                }

                // جلب المتغيرات المختارة مع بياناتها
                $variants = ProductVariant::with(['values.attribute', 'values.attributeValue'])
                    ->whereIn('id', $selectedVariantIds)
                    ->get();

                // جلب الوحدات الافتراضية للمنتج
                $product = Product::with(['units.unit'])->find($productId);
                $defaultUnits = $product?->units->map(fn($pu) => [
                    'unit_id' => $pu->unit_id,
                    'package_size' => $pu->package_size,
                    'cost_price' => $pu->cost_price,
                    'selling_price' => $pu->selling_price,
                    'moq' => 1,
                    'stock' => 0,
                    'is_default' => $pu->is_base_unit,
                    'status' => 'active',
                    'sort_order' => $pu->sort_order,
                ])->toArray() ?? [];

                // بناء مصفوفة variants_units
                $variantsUnits = [];
                foreach ($variants as $variant) {
                    $label = $variant->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(' | ');
                    $variantsUnits[] = [
                        'variant_id' => $variant->id,
                        'variant_label' => $label ?: $variant->sku,
                        'units' => $defaultUnits,
                    ];
                }

                $set('variants_units', $variantsUnits);
            })
            ->visible(function ($get, $operation) {
                if ($operation !== 'create') {
                    return false;
                }
                $productId = $get('product_id');
                if (!$productId) {
                    return false;
                }
                $product = Product::with(['attributesDirect'])->find($productId);
                return $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();
            });
    }
}
