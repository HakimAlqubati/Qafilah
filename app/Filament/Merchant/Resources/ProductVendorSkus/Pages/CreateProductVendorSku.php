<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Pages;

use App\Filament\Merchant\Resources\ProductVendorSkus\ProductVendorSkuResource;
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
        $units = $data['units'] ?? [];

        // Remove non-model fields
        unset($data['main_category_id']);
        unset($data['sub_category_id']);
        unset($data['attributes']);
        unset($data['units']);

        // Check for duplicate before creating
        $exists = ProductVendorSku::where('vendor_id', $data['vendor_id'])
            ->where('product_id', $data['product_id'])
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

        // Create units from repeater
        foreach ($units as $unitData) {
            $record->units()->create([
                'unit_id' => $unitData['unit_id'],
                'package_size' => $unitData['package_size'] ?? 1,
                'cost_price' => $unitData['cost_price'] ?? null,
                'selling_price' => $unitData['selling_price'],
                'stock' => $unitData['stock'] ?? 0,
                'moq' => $unitData['moq'] ?? 1,
                'is_default' => true,
                'status' => 'active',
                'sort_order' => 0,
            ]);
        }

        return $record;
    }
}
