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
        $record->load(['variant.product.category', 'variant.values']);

        if ($record->variant) {
            $product = $record->variant->product;
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

            // Populate attributes
            $attributes = [];
            foreach ($record->variant->values as $value) {
                $attributes[$value->attribute_id] = $value->attribute_value_id;
            }
            $data['attributes'] = $attributes;
        }

        return $data;
    }

  protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
