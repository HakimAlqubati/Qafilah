<?php

namespace App\Filament\Resources\Vendors\Schemas\Components\Tabs;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class BasicInfoTab
{
    /**
     * Create the Basic Information tab
     */
    public static function make(): Tab
    {
        return Tab::make(__('lang.basic_info'))
            ->icon('heroicon-o-building-storefront')
            ->schema([
                Grid::make(3)
                    ->schema([
                        // 1. Name & Slug (Required)
                        TextInput::make('name')
                            ->label(__('lang.vendor_name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $set('slug', Str::slug($state));
                            })
                            ->columnSpan(2),

                        TextInput::make('slug')
                            ->label(__('lang.url_slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->helperText(__('lang.auto_generated')),
                    ]),

                Grid::make(2)
                    ->schema([
                        // 2. Contact Information
                        TextInput::make('contact_person')
                            ->label(__('lang.contact_person'))
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('lang.phone_number'))
                            ->tel()
                            ->maxLength(50),
                    ]),

                // 3. VAT ID (Unique)
                TextInput::make('vat_id')
                    ->label(__('lang.vat_id'))
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                // 4. Description
                RichEditor::make('description')
                    ->label(__('lang.detailed_description'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }
}
