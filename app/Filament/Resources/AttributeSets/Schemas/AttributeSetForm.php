<?php

namespace App\Filament\Resources\AttributeSets\Schemas;

use Filament\Schemas\Schema;

// ⬇️ استخدم نفس أسلوبك في المشروع: عناصر الإدخال من Forms، والتجميع من Schemas
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;

class AttributeSetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ========== الأساسيات ==========
                Fieldset::make()->label(__('lang.basic_information'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()->columns(12)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('lang.name'))
                                    ->placeholder(__('lang.example_electronics'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->minLength(2)
                                    ->maxLength(100)
                                    ->columnSpan(8),

                                Toggle::make('active')
                                    ->label(__('lang.active'))
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(4),

                                Textarea::make('description')
                                    ->label(__('lang.description'))
                                    ->placeholder(__('lang.short_desc_placeholder'))
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpan(12),
                            ]),
                    ]),

                // ========== السمات المرتبطة بالقالب ==========
                Fieldset::make()->label(__('lang.attributes_in_set'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()->columns(12)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('attributes')->columnSpanFull()
                                    ->label(__('lang.attributes'))
                                    ->relationship('attributes', 'name')   // Many-to-Many
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),
            ]);
    }
}
