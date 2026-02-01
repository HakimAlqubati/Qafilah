<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Slider\Enums\PipsMode;
use Filament\Support\RawJs;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema(
                Fieldset::make()->columnSpanFull()->schema([
                    Grid::make()->columns(6)->columnSpanFull()->schema([
                        Select::make('product_id')
                            ->label(__('lang.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->columnSpan(1)

                            ->preload(),
                        TextInput::make('name')
                            ->label(__('lang.name'))
                            ->required()
                            ->columnSpan(2)
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label(__('lang.title'))
                            ->columnSpan(2)

                            ->maxLength(255),
                        Toggle::make('is_active')->inline(false)
                            ->label(__('lang.is_active'))
                            ->required()
                            ->default(true),
                    ]),
                    Slider::make('sort_order')
                        ->label(__('lang.slider_.order'))
                        ->minValue(0)
                        ->maxValue(20)
                        ->step(1)
                        ->columnSpanFull()
                        ->fillTrack()
                        ->tooltips()
                        ->live() // لجعل التلميحات تتغير فوراً أثناء السحب
                        ->hint(fn($state) => match (true) {
                            $state == 0 => '💎💎💎',
                            $state <= 5 => '🚀',
                            $state >= 15 => '💤',
                            default => '📍',
                        })
                        ->hintIcon('heroicon-m-adjustments-horizontal')
                        ->helperText(__('lang.slider_.order_helper'))
                        ->pips(PipsMode::Steps, density: 10)
                        ->default(0),
                    Textarea::make('body')
                        ->label(__('lang.body'))
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('image')
                        ->label(__('lang.image'))
                        ->collection('image')
                        ->image()
                        ->columnSpanFull(),


                ])
            );
    }
}
