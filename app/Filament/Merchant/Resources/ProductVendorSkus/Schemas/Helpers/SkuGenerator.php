<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Helpers;

use App\Models\ProductVendorSku;

class SkuGenerator
{
    /**
     * Generate a unique vendor SKU
     * Format: VND{vendorId}-PRD{productId}-{randomSuffix}
     */
    public static function generate(int $productId, int $vendorId): string
    {
        do {
            $randomSuffix = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $sku = "VND{$vendorId}-PRD{$productId}-{$randomSuffix}";
        } while (ProductVendorSku::where('vendor_sku', $sku)->exists());

        return $sku;
    }
}
