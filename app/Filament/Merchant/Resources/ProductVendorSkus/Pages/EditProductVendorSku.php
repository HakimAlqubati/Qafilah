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
        $record->load(['units']);

        // تحميل بيانات المنتج والتصنيفات
        if ($record->product_id) {
            $product = \App\Models\Product::with('category')->find($record->product_id);

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
        }

        // Load units
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

        return $data;
    }

    protected function afterSave(): void
    {
        $units = $this->data['units'] ?? [];

        // Delete old units and create new ones
        $this->record->units()->forceDelete();

        foreach ($units as $unitData) {
            $this->record->units()->create([
                'unit_id' => $unitData['unit_id'],
                'package_size' => $unitData['package_size'] ?? 1,
                'cost_price' => $unitData['cost_price'] ?? null,
                'selling_price' => $unitData['selling_price'],
                'stock' => $unitData['stock'] ?? 0,
                'moq' => $unitData['moq'] ?? 1,
                'is_default' => $unitData['is_default'] ?? true,
                'status' => $unitData['status'] ?? 'active',
                'sort_order' => $unitData['sort_order'] ?? 0,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
