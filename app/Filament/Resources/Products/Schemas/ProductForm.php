<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\Products\Schemas\Components\Steps\AttributesStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\AttributeValuesStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\CatalogStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\GeneralInformationStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\MediaStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\ProductUnitsStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\VariantsStep;
use App\Filament\Resources\Products\Schemas\Components\Steps\VisibilityStatusStep;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class ProductForm
{
    /**
     * Configure the form schema
     */
    public static function configure(Schema $form): Schema
    {
        return $form->schema([
            Wizard::make()
                ->columnSpanFull()
                ->steps([
                    // Step 1: General Information
                    GeneralInformationStep::make(),

                    // Step 2: Media
                   MediaStep::make(),

                    // Step 3: Attributes (Direct Attributes without Set)
//                    AttributesStep::make(),

                    // Step 4: Catalog
//                    CatalogStep::make(),

                    // Step 5: Product Units
//                    VisibilityStatusStep::make(),
                    ProductUnitsStep::make(),

                    // Step 6: Attribute Values (Custom Attributes)
                    AttributeValuesStep::make(),

                    // Step 7: Variants
                    VariantsStep::make(),

                    // Step 8: Visibility & Status

                ])
                ->skippable(),
        ]);
    }
}
