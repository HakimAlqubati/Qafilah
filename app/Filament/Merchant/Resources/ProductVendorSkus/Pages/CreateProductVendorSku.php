<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Pages;

use App\Filament\Merchant\Resources\ProductVendorSkus\ProductVendorSkuResource;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\ProductVendorSkuForm;
use App\Models\ProductVendorSku;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProductVendorSku extends CreateRecord
{
    protected static string $resource = ProductVendorSkuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $variantsUnits = $data['variants_units'] ?? [];
        $units = $data['units'] ?? [];

        // Remove non-model fields
        unset($data['selected_variants']);
        unset($data['variants_units']);
        unset($data['main_category_id']);
        unset($data['sub_category_id']);
        unset($data['attributes']);
        unset($data['units']);

        // === حالة المنتجات ذات المتغيرات (كل متغير له وحدات مستقلة) ===
        if (!empty($variantsUnits)) {
            $createdRecords = [];
            $vendorId = $data['vendor_id'];
            $productId = $data['product_id'];
            $skippedCount = 0;

            foreach ($variantsUnits as $variantData) {
                $variantId = (int) $variantData['variant_id'];
                $variantUnits = $variantData['units'] ?? [];

                // Check if already exists
                $exists = ProductVendorSku::where('vendor_id', $vendorId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->where('currency_id', $data['currency_id'])
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // Generate unique SKU for each variant
                $uniqueSku = ProductVendorSkuForm::generateUniqueVendorSku($productId, $vendorId);

                $recordData = array_merge($data, [
                    'variant_id' => $variantId,
                    'vendor_sku' => $uniqueSku,
                ]);

                try {
                    $record = static::getModel()::create($recordData);

                    // Create units for this specific variant
                    foreach ($variantUnits as $unitData) {
                        $record->units()->create([
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

                    $createdRecords[] = $record;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $skippedCount++;
                    continue;
                }
            }

            // If all variants were skipped (already exist)
            if (empty($createdRecords)) {
                Notification::make()
                    ->title(__('lang.duplicate_product'))
                    ->body(__('lang.all_variants_already_exist'))
                    ->warning()
                    ->send();

                $this->halt();
            }

            // Notification for bulk creation
            if (count($createdRecords) > 1) {
                Notification::make()
                    ->title(__('lang.products_added_successfully'))
                    ->body(__('lang.variants_added_count', ['count' => count($createdRecords)]))
                    ->success()
                    ->send();
            }

            // Return the first record (Filament expects a single model)
            return $createdRecords[0] ?? static::getModel()::make();
        }

        // === حالة المنتج البسيط (بدون متغيرات) ===
        // Check for duplicate before creating
        $exists = ProductVendorSku::where('vendor_id', $data['vendor_id'])
            ->where('product_id', $data['product_id'])
            ->where('variant_id', $data['variant_id'] ?? null)
            ->where('currency_id', $data['currency_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title(__('lang.duplicate_product'))
                ->body(__('lang.product_already_exists'))
                ->danger()
                ->send();

            $this->halt();
        }

        $record = static::getModel()::create($data);

        // Create units
        foreach ($units as $unitData) {
            $record->units()->create([
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

        return $record;
    }
}
