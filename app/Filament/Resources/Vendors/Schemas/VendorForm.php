<?php

// app/Filament/Resources/Vendors/Schemas/VendorForm.php 
// (or directly in VendorResource::form)

namespace App\Filament\Resources\Vendors\Schemas;

use App\Models\Vendor;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VendorForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Vendor Details')
                    ->tabs([
                        // Tab 1: Basic Information
                        Tab::make('Basic Info')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                ComponentsGrid::make(3)
                                    ->schema([
                                        // 1. Name & Slug (Required)
                                        TextInput::make('name')
                                            ->label('Vendor Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true) // Update slug on blur
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $set('slug', Str::slug($state)); // Auto-generate slug
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('slug')
                                            ->label('Slug (URL Identifier)')
                                            ->required()
                                            ->unique(ignoreRecord: true) // Ensure unique slug
                                            ->maxLength(255),
                                    ]),

                                ComponentsGrid::make(2)
                                    ->schema([
                                        // 2. Contact Information
                                        TextInput::make('contact_person')
                                            ->label('Contact Person Name')
                                            ->maxLength(255),

                                        TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->maxLength(50),
                                    ]),

                                // 3. VAT ID (Unique)
                                TextInput::make('vat_id')
                                    ->label('VAT / Commercial ID')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100),

                                // 4. Description
                                RichEditor::make('description')
                                    ->label('Detailed Description')
                                    ->maxLength(65535) // Max TEXT length
                                    ->columnSpanFull(),
                            ]),

                        // Tab 2: Settings & Media
                        Tab::make('Settings & Media')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                ComponentsGrid::make(2)
                                    ->schema([
                                        // 5. Status
                                        Select::make('status')
                                            ->label('Status')
                                            ->required()
                                            ->default('active')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                                'pending' => 'Pending Review',
                                            ]),

                                        // 6. Email (Unique)
                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                    ]),

                                // 7. Logo Upload (using standard FileUpload for simplicity)
                                // *Note: Spatie Media Library is better for production, but using default for direct field `logo_path`*
                                FileUpload::make('logo_path')
                                    ->label('Vendor Logo')
                                    ->disk('public') // Store in public disk
                                    ->directory('vendors/logos')
                                    ->image() // Restrict to image files
                                    ->maxSize(500), // Max file size 500 KB
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
