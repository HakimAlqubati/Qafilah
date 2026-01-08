<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\AttributesFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\CategoryFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\ProductFields;
use Filament\Schemas\Components\Section;

class ProductInfoStep
{
    /**
     * Create the Product Information step
     */
    public static function make(): Section
    {
        return Section::make(__('lang.product_information'))
            ->icon('heroicon-o-information-circle')
            ->columnSpanFull()
            ->columns(2)
            ->schema(self::getSchema());
    }

    /**
     * Get the step schema
     */
    private static function getSchema(): array
    {
        return [
            // 0. Quick Product Search (global)
            ProductFields::productSearch(),

            // 1. Category Fields (Main & Sub)
            ...CategoryFields::make(),

            // 2. Product Selection (filtered by category)
            ProductFields::productSelect()->columnSpan(1),
            ProductFields::vendorSku()->columnSpan(1),

            // 3. Dynamic Attributes Grid
            AttributesFields::make(),

            // 4. Hidden Fields

            ProductFields::vendorIdHidden(),
            ProductFields::productIdHidden(),
        ];
    }
}
