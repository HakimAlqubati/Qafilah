<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\UnitsRepeater;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\VariantsUnitsRepeater;
use Filament\Schemas\Components\Wizard\Step;

class UnitsPricingStep
{
    /**
     * Create the Units & Pricing step
     */
    public static function make(): Step
    {
        return Step::make('units')
            ->label(__('lang.units_pricing'))
            ->icon('heroicon-o-cube')
            ->columnSpanFull()
            ->schema(self::getSchema());
    }

    /**
     * Get the step schema
     */
    private static function getSchema(): array
    {
        return [
            // For products with variants (create mode)
            VariantsUnitsRepeater::make(),

            // For simple products (no variants) or edit mode
            UnitsRepeater::make(),
        ];
    }
}
