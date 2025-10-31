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
                Fieldset::make()->label('Basic Information')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()->columns(12)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->placeholder('e.g., Electronics, Apparel, Printers')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->minLength(2)
                                    ->maxLength(100)
                                    ->columnSpan(8),

                                Toggle::make('active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(4),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Short description of this attribute set...')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpan(12),
                            ]),
                    ]),

                // ========== السمات المرتبطة بالقالب ==========
                Fieldset::make()->label('Attributes in this Set')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()->columns(12)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('attributes')->columnSpanFull()
                                    ->label('Attributes')
                                    ->relationship('attributes', 'name')   // Many-to-Many
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),
            ]);
    }
}
