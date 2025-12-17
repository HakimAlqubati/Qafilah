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
        $selectedVariants = $data['selected_variants'] ?? [];
        $units = $data['units'] ?? [];

        // Remove non-model fields
        unset($data['selected_variants']);
        unset($data['main_category_id']);
        unset($data['sub_category_id']);
        unset($data['attributes']);

        // إذا تم اختيار متغيرات متعددة
        if (!empty($selectedVariants) && count($selectedVariants) > 0) {
            $createdRecords = [];
            $vendorId = $data['vendor_id'];
            $productId = $data['product_id'];

            foreach ($selectedVariants as $variantId) {
                // Check if already exists
                $exists = ProductVendorSku::where('vendor_id', $vendorId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->where('currency_id', $data['currency_id'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Generate unique SKU for each variant
                $uniqueSku = ProductVendorSkuForm::generateUniqueVendorSku($productId, $vendorId);

                $recordData = array_merge($data, [
                    'variant_id' => $variantId,
                    'vendor_sku' => $uniqueSku,
                ]);

                // Remove units from main data as we'll handle it separately
                unset($recordData['units']);

                $record = static::getModel()::create($recordData);

                // Create units for each record
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

                $createdRecords[] = $record;
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

        // حالة المنتج البسيط (بدون متغيرات متعددة)
        unset($data['units']);
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
