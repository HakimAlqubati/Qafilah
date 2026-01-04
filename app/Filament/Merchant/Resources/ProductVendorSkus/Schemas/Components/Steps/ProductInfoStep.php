<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\AttributesFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\CategoryFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\ProductFields;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\VariantsCheckboxList;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;

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
            // 1. Category Fields (Main & Sub)
            ...CategoryFields::make(),

            // 2. Product Selection
//            ProductFields::productSelect(),
//            Grid::make(1)->schema([
                ProductFields::productSelect()->columnSpan(1),
                ProductFields::vendorSku()->columnSpan(1),
//            ]),

            // 3. Dynamic Attributes Grid
            AttributesFields::make(),

            // 4. Multi-variant Selection (for create mode)
            VariantsCheckboxList::make(),

            // 5. Hidden & Additional Fields
            ProductFields::variantIdHidden(),
//            ProductFields::vendorSku(),
            ProductFields::currencySelect(),
            ProductFields::vendorIdHidden(),
            ProductFields::productIdHidden(),
        ];
    }
}
