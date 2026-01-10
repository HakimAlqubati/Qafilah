<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Str;
use App\Filament\Resources\Products\Schemas\Components\Steps\MediaStep;
class GeneralInformationStep
{
    /**
     * Create the General Information step
     */
    public static function make(): Step
    {
        return Step::make(__('lang.general_information'))
            ->icon('heroicon-o-information-circle')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('lang.product_name'))
                            ->required()
                            ->maxLength(255),
                        Hidden::make('slug'),
                        Select::make('category_id')
                            ->label(__('lang.category'))
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),




                    ]),

                Grid::make(3)
                    ->schema([
                        Select::make('brand_id')
                            ->label(__('lang.brand'))
                            ->relationship('brand', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Select::make('status')
                            ->label(__('lang.product_status'))
                            ->options(Product::statusOptions())
                            ->default(Product::$STATUSES['ACTIVE'])
                            ->required()
                            ->native(false),

                        Toggle::make('is_featured')
                            ->label(__('lang.feature_on_homepage'))
                            ->helperText(__('lang.feature_on_homepage_desc'))
                            ->inline()
                            ->default(false),

                    ]),





//                Textarea::make('short_description')
//                    ->label(__('lang.short_description'))
//                    ->maxLength(500)
//                    ->rows(3)
//                    ->columnSpanFull(),


                Grid::make(12)->schema([
                    RichEditor::make('description')
                        ->label(__('lang.detailed_description'))
                        ->columnSpan(8)
                        ->extraInputAttributes([
                            'style' => 'min-height: 70px;',
                        ])
                        ->toolbarButtons([
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'h2',
                            'h3',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'undo',
                        ]),

                    Repeater::make('label_attribute')
                        ->label(__('lang.attribute_label'))
                        ->columnSpan(4)
                        ->addActionLabel(__('lang.add_attribute'))
                        ->default([])
                        ->schema([
                            TextInput::make('value')
                                ->label(__('lang.value'))
                                ->required(),
                        ]),
                ]),

                // MediaStep::make(),

            ]);
    }
}
