<?php

namespace App\Filament\Resources\Vendors\Schemas;

use App\Filament\Resources\Vendors\Schemas\Components\Tabs\BasicInfoTab;
use App\Filament\Resources\Vendors\Schemas\Components\Tabs\LocationDeliveryTab;
use App\Filament\Resources\Vendors\Schemas\Components\Tabs\SettingsMediaTab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class VendorForm
{
    /**
     * Configure the form schema
     */
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make(__('lang.vendor_details'))
                    ->tabs([
                        // Tab 1: Basic Information
                        BasicInfoTab::make(),

                        // Tab 2: Location & Delivery
                        LocationDeliveryTab::make(),

                        // Tab 3: Settings & Media
                        SettingsMediaTab::make(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
