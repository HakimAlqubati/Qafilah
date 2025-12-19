<?php

namespace App\Filament\Resources\Products\Schemas\Helpers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\Schema as DBSchema;

class AttributeHelpers
{
    /**
     * Get available attributes for product (direct binding via product_set_attributes)
     */
    public static function getAvailableAttributesForProductOrSet(?int $productId): array
    {
        if ($productId) {
            $attributes = Attribute::query()
                ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                ->where('pa.product_id', $productId)
                ->where('attributes.active', true)
                ->orderBy('pa.sort_order')
                ->orderBy('attributes.name')
                ->select('attributes.id', 'attributes.name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            if (!empty($attributes)) {
                return $attributes;
            }
        }

        return [];
    }

    /**
     * Get variant attributes for product (with is_variant_option = true)
     */
    public static function getVariantAttributesForProductOrSet(?int $productId)
    {
        if ($productId) {
            $attrs = Attribute::query()
                ->with('values')
                ->select('attributes.*', 'pa.sort_order', 'pa.is_variant_option')
                ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                ->where('pa.product_id', $productId)
                ->where('attributes.active', true)
                ->where('pa.is_variant_option', true)
                ->get()
                ->filter(fn(Attribute $a) => $a->isChoiceType())
                ->sortBy(fn($a) => $a->sort_order ?? PHP_INT_MAX)
                ->values();

            if ($attrs->isNotEmpty()) {
                return $attrs;
            }
        }
        return [];
    }

    /**
     * Build choice options from AttributeValue
     */
    public static function buildChoiceOptionsFromValues(Attribute $attribute): array
    {
        return AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->when(
                DBSchema::hasColumn('attribute_values', 'is_active'),
                fn($q) => $q->where('is_active', true)
            )
            ->orderByRaw('COALESCE(sort_order, 999999), value asc')
            ->pluck('value', 'id')
            ->toArray();
    }
}
