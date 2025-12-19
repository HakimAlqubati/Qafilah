<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\AttributesFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\CategoryFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\ProductFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\VariantsCheckboxList;
use Filament\Schemas\Components\Wizard\Step;

class ProductInfoStep
{
    /**
     * Create the Product Information step
     */
    public static function make(): Step
    {
        return Step::make('info')
            ->label(__('lang.product_information'))
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
            // 1. Category Fields (Main & Sub)
            ...CategoryFields::make(),

            // 2. Product Selection
            ProductFields::productSelect(),

            // 3. Dynamic Attributes Grid
            AttributesFields::make(),

            // 4. Multi-variant Selection (for create mode)
            VariantsCheckboxList::make(),

            // 5. Hidden & Additional Fields
            ProductFields::variantIdHidden(),
            ProductFields::vendorSku(),
            ProductFields::currencySelect(),
            ProductFields::vendorIdHidden(),
            ProductFields::productIdHidden(),
        ];
    }
}
