<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Pages;

use App\Filament\Merchant\Resources\ProductVendorSkus\ProductVendorSkuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductVendorSku extends CreateRecord
{
    protected static string $resource = ProductVendorSkuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // حفظ الوحدات يدوياً لأن الـ Repeater لا يستخدم relationship
        $units = $this->data['units'] ?? [];

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
