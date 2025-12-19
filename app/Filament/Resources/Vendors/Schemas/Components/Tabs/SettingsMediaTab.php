<?php

namespace App\Filament\Resources\Vendors\Schemas\Components\Tabs;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;

class SettingsMediaTab
{
    /**
     * Create the Settings & Media tab
     */
    public static function make(): Tab
    {
        return Tab::make(__('lang.settings_media'))
            ->icon('heroicon-o-cog-6-tooth')
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Status
                        Select::make('status')
                            ->label(__('lang.status'))
                            ->required()
                            ->default('active')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'pending' => 'Pending Review',
                            ]),

                        // Email (Unique)
                        TextInput::make('email')
                            ->label(__('lang.email_address'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                // Logo Upload
                FileUpload::make('logo_path')
                    ->label(__('lang.vendor_logo'))
                    ->disk('public')
                    ->directory('vendors/logos')
                    ->image()
                    ->maxSize(500),
            ]);
    }
}
