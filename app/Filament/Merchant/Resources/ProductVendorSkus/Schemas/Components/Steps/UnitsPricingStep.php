<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\UnitsRepeater;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\ProductFields;
use Filament\Schemas\Components\Section;

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
                ProductFields::currencyVisibleSelect(),
                UnitsRepeater::make()->columnSpanFull(),
            ])
            ->columns(1)
            ->columnSpanFull();
    }
}
