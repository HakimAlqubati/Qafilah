<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;

class AttributesFields
{
    /**
     * Get the dynamic attributes grid
     * Only visible in edit mode or for simple products in create mode
     */
    public static function make(): Grid
    {
        return Grid::make(2)
            ->visible(function ($get, $operation) {
                // إخفاء في وضع الإنشاء إذا كان المنتج له متغيرات
                // لأننا نستخدم CheckboxList بدلاً منها
                if ($operation === 'create') {
                    $productId = $get('product_id');
                    if (!$productId) {
                        return false;
                    }
                    $product = Product::with(['attributesDirect'])->find($productId);
                    $hasVariantAttributes = $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();
                    return !$hasVariantAttributes; // أخفي إذا كان له متغيرات
                }
                return true; // أظهر في وضع التعديل
            })
            ->schema(function ($get, $set) {
                $productId = $get('product_id');
                if (!$productId) {
                    return [];
                }

                $product = Product::with(['attributesDirect.values'])->find($productId);
                if (!$product) {
                    return [];
                }

                $sortedAttributes = $product->attributesDirect->sortBy('pivot.sort_order')->values();
                $components = [];

                // Filter only variant option attributes
                $variantAttributes = $sortedAttributes->filter(fn($attr) => $attr->pivot->is_variant_option)->values();

                foreach ($variantAttributes as $index => $attribute) {
                    // Determine if this attribute should be visible (disabled or hidden if previous not selected)
                    if ($index > 0) {
                        $prevAttrId = $variantAttributes[$index - 1]->id;
                        $prevValue = $get("attributes.{$prevAttrId}");
                        if (empty($prevValue)) {
                            break;
                        }
                    }

                    $components[] = self::createAttributeSelect($attribute, $variantAttributes, $index, $productId);
                }
                return $components;
            });
    }

    /**
     * Create a single attribute select field
     */
    private static function createAttributeSelect($attribute, $variantAttributes, int $index, int $productId): Select
    {
        return Select::make("attributes.{$attribute->id}")
            ->label($attribute->name)
            ->options(function ($get) use ($productId, $attribute, $variantAttributes, $index) {
                // Filter options based on previous selections
                $query = ProductVariant::where('product_id', $productId)->active();

                // Apply filters from previous attributes
                for ($i = 0; $i < $index; $i++) {
                    $prevAttr = $variantAttributes[$i];
                    $prevVal = $get("attributes.{$prevAttr->id}");
                    if ($prevVal) {
                        $query->whereHas('variantValues', function ($q) use ($prevVal) {
                            $q->where('attribute_value_id', $prevVal);
                        });
                    }
                }

                // Get valid attribute values for the current attribute from the filtered variants
                $validVariantIds = $query->pluck('id');

                return AttributeValue::where('attribute_id', $attribute->id)
                    ->whereHas('variants', function ($q) use ($validVariantIds) {
                        $q->whereIn('product_variants.id', $validVariantIds);
                    })
                    ->pluck('value', 'id');
            })
            ->required()
            ->disabled(fn($operation) => $operation === 'edit') // تعطيل في وضع التعديل
            ->live()
            ->afterStateUpdated(function ($set, $get) use ($variantAttributes, $index, $productId) {
                // Reset subsequent attributes
                for ($i = $index + 1; $i < $variantAttributes->count(); $i++) {
                    $nextAttr = $variantAttributes[$i];
                    $set("attributes.{$nextAttr->id}", null);
                }
                $set('variant_id', null);

                // Check if all attributes are selected
                $allSelected = true;
                $selectedAttributes = $get('attributes') ?? [];

                foreach ($variantAttributes as $attr) {
                    if (empty($selectedAttributes[$attr->id])) {
                        $allSelected = false;
                        break;
                    }
                }

                if ($allSelected) {
                    // Find the matching variant
                    $query = ProductVariant::where('product_id', $productId)->active();

                    foreach ($selectedAttributes as $attrId => $valId) {
                        if ($valId) {
                            $query->whereHas('variantValues', function ($q) use ($valId) {
                                $q->where('attribute_value_id', $valId);
                            });
                        }
                    }

                    $variant = $query->first();
                    if ($variant) {
                        $set('variant_id', $variant->id);
                    }
                }
            });
    }
}
