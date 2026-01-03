<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\UnitsRepeater;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\VariantsUnitsRepeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;

class UnitsPricingStep
{
    /**
     * Create the Units & Pricing step
     */
    public static function make(): Section
    {
        return Section::make(__('lang.units_pricing'))
            ->icon('heroicon-o-cube')
            ->schema([
                VariantsUnitsRepeater::make()->columnSpanFull(),
                UnitsRepeater::make()->columnSpanFull(),
            ])
            ->columns(1)
            ->columnSpanFull();
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
