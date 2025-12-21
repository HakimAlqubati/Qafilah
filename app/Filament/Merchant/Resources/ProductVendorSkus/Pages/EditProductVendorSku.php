<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Pages;

use App\Filament\Merchant\Resources\ProductVendorSkus\ProductVendorSkuResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProductVendorSku extends EditRecord
{
    protected static string $resource = ProductVendorSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load(['variant.product.category', 'variant.values', 'units']);

        // تحميل بيانات المنتج والتصنيفات
        $product = null;

        if ($record->product_id) {
            $product = \App\Models\Product::with('category')->find($record->product_id);
        } elseif ($record->variant) {
            $product = $record->variant->product;
        }

        if ($product) {
            $data['product_id'] = $product->id;

            if ($product->category) {
                if ($product->category->parent_id) {
                    $data['sub_category_id'] = $product->category->id;
                    $data['main_category_id'] = $product->category->parent_id;
                } else {
                    $data['main_category_id'] = $product->category->id;
                }
            }
        }

        // Get all ProductVendorSku records for this product and vendor
        $allSkus = \App\Models\ProductVendorSku::with(['variant.values.attribute', 'variant.values.attributeValue', 'units'])
            ->where('vendor_id', $record->vendor_id)
            ->where('product_id', $record->product_id)
            ->get();

        $hasVariants = $allSkus->filter(fn($sku) => $sku->variant_id !== null)->isNotEmpty();

        if ($hasVariants) {
            $variantsUnits = [];
            foreach ($allSkus->filter(fn($sku) => $sku->variant_id !== null) as $sku) {
                $label = $sku->variant?->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(' | ');
                $variantsUnits[] = [
                    'variant_id' => $sku->variant_id,
                    'variant_label' => $label ?: $sku->variant?->sku,
                    'sku_id' => $sku->id, // Store the SKU id for update
                    'units' => $sku->units->map(fn($unit) => [
                        'unit_id' => $unit->unit_id,
                        'package_size' => $unit->package_size,
                        'cost_price' => $unit->cost_price,
                        'selling_price' => $unit->selling_price,
                        'stock' => $unit->stock,
                        'moq' => $unit->moq,
                        'is_default' => $unit->is_default,
                        'status' => $unit->status,
                        'sort_order' => $unit->sort_order,
                    ])->toArray(),
                ];
            }
            $data['variants_units'] = $variantsUnits;
            $data['units'] = []; // Clear simple units
        } else {
            // Simple product - load units from the first SKU
            $data['units'] = $record->units->map(fn($unit) => [
                'unit_id' => $unit->unit_id,
                'package_size' => $unit->package_size,
                'cost_price' => $unit->cost_price,
                'selling_price' => $unit->selling_price,
                'stock' => $unit->stock,
                'moq' => $unit->moq,
                'is_default' => $unit->is_default,
                'status' => $unit->status,
                'sort_order' => $unit->sort_order,
            ])->toArray();
        }
        if ($record->variant_id && $record->variant) {
            $attributes = [];
            foreach ($record->variant->values as $value) {
                $attributes[$value->attribute_id] = $value->attribute_value_id;
            }
            $data['attributes'] = $attributes;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $variantsUnits = $this->data['variants_units'] ?? [];
        $units = $this->data['units'] ?? [];

        if (!empty($variantsUnits)) {
            foreach ($variantsUnits as $variantData) {
                $skuId = $variantData['sku_id'] ?? null;
                $variantUnits = $variantData['units'] ?? [];

                if ($skuId) {
                    $sku = \App\Models\ProductVendorSku::find($skuId);
                    if ($sku) {
                        $sku->units()->forceDelete();

                        foreach ($variantUnits as $unitData) {
                            $sku->units()->create([
                                'unit_id' => $unitData['unit_id'],
                                'package_size' => $unitData['package_size'] ?? 1,
                                'cost_price' => $unitData['cost_price'] ?? null,
                                'selling_price' => $unitData['selling_price'],
                                'stock' => $unitData['stock'] ?? 0,
                                'moq' => $unitData['moq'] ?? 1,
                                'is_default' => $unitData['is_default'] ?? false,
                                'status' => $unitData['status'] ?? 'active',
                                'sort_order' => $unitData['sort_order'] ?? 0,
                            ]);
                        }
                    }
                }
            }
        } else {
            $this->record->units()->forceDelete();

            foreach ($units as $unitData) {
                $this->record->units()->create([
                    'unit_id' => $unitData['unit_id'],
                    'package_size' => $unitData['package_size'] ?? 1,
                    'cost_price' => $unitData['cost_price'] ?? null,
                    'selling_price' => $unitData['selling_price'],
                    'stock' => $unitData['stock'] ?? 0,
                    'moq' => $unitData['moq'] ?? 1,
                    'is_default' => $unitData['is_default'] ?? false,
                    'status' => $unitData['status'] ?? 'active',
                    'sort_order' => $unitData['sort_order'] ?? 0,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
