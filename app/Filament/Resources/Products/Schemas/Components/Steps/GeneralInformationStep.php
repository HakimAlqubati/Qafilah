<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Str;

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
                            ->maxLength(255)
                            ->reactive()
                            ->debounce(500)
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (!empty($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label(__('lang.url_slug'))
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('lang.auto_generated')),
                    ]),

                Repeater::make('label_attribute')
                    ->label(__('lang.attribute_label'))
                    ->schema([
                        TextInput::make('value')->label(__('lang.value'))->required(),
                    ])
                    ->columnSpanFull(),



                Textarea::make('short_description')
                    ->label(__('lang.short_description'))
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),


                RichEditor::make('description')
                    ->label(__('lang.detailed_description'))
                    ->columnSpanFull()
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
            ]);
    }
}
