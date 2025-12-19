<?php

namespace App\Filament\Resources\Users\Schemas\Components\Sections;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class VendorAssignmentSection
{
    /**
     * Create the Vendor Assignment section
     */
    public static function make(): Section
    {
        return Section::make(__('lang.vendor_assignment'))
            ->description(__('lang.vendor_assignment_description'))
            ->icon('heroicon-o-building-storefront')
            ->collapsible()
            ->collapsed()
            ->schema([
                // Toggle for Vendor
                Toggle::make('is_vendor_user')
                    ->label(__('lang.is_vendor_user'))
                    ->helperText(__('lang.is_vendor_user_helper'))
                    ->live()
                    ->default(false)
                    ->dehydrated(false)
                    ->columnSpanFull(),

                // Vendor Select
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('lang.vendor'))
                    ->placeholder(__('lang.select_vendor'))
                    ->native(false)
                    ->visible(
                        fn($get, string $context, $record): bool =>
                        (bool) $get('is_vendor_user')
                    )
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
