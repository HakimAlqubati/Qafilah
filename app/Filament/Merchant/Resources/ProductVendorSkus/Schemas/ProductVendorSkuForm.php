<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps\ImagesStep;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps\ProductInfoStep;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps\UnitsPricingStep;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class ProductVendorSkuForm
{
    /**
     * Configure the form schema
     */
    public static function configure(Schema $form): Schema
    {
        return $form->schema([
            ProductInfoStep::make(),

            UnitsPricingStep::make(),

//            ImagesStep::make(),
        ]);
    }

//    public static function configure(Schema $form): Schema
//    {
//        return $form
//            ->schema([
//                Wizard::make()
//                    ->columnSpanFull()
//                    ->skippable()
//                    ->schema([
//                        // Step 1: Product Information
//                        ProductInfoStep::make(),
//
//                        // Step 2: Units & Pricing
//                        UnitsPricingStep::make(),
//
//                        // Step 3: Images Upload (hidden)
//                        ImagesStep::make(),
//                    ])
//            ]);
//    }
}
