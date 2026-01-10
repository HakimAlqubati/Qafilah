<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\UnitsRepeater;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields\ProductFields;
use App\Models\Currency;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;

class UnitsPricingStep
{
    /**
     * Create the Units & Pricing step
     */
    public static function make(): Section
    {
        $defaultCurrency = Currency::default()->first();
        $currencyName = $defaultCurrency?->name ?? '';
        $currencySymbol = $defaultCurrency?->symbol ?? '';

        return Section::make(__('lang.units_pricing'))
            ->icon('heroicon-o-cube')
            ->schema([
                // ملاحظة العملة الافتراضية
                Placeholder::make('currency_note')
                    ->label(__('lang.default_currency'))
                    ->content(fn() => __('lang.prices_in_default_currency', ['currency' => $currencyName]))
                    ->columnSpanFull(),

                // حقل العملة المخفي
                ProductFields::currencyHidden(),

                UnitsRepeater::make($currencySymbol)->columnSpanFull(),
            ])
            ->columns(1)
            ->columnSpanFull();
    }
}
