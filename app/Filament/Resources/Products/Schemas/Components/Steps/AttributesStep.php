<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Models\Product;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;

class AttributesStep
{
    /**
     * Create the Attributes step (Direct Attributes without Set)
     */
    public static function make(): Step
    {
        return Step::make(__('lang.attributes'))
            ->icon('heroicon-o-adjustments-horizontal')
            ->schema([
                Section::make(__('lang.attach_attributes'))
                    ->columns(1)
                    ->schema([
                        Fieldset::make()->columnSpanFull()->columns(1)->schema([
                            CheckboxList::make('attributes_direct')
                                ->label(__('lang.attributes'))
                                ->relationship('attributesDirect', 'name')
                                ->columns(4)
                                ->bulkToggleable()
                                ->helperText('اختَر السمات التي تنطبق على هذا المنتج مباشرةً (بدون Set).'),

                            Section::make('')
                                ->collapsed(false)
                                ->visible(fn(Get $get) => filled($get('attributes_direct')) && count($get('attributes_direct') ?? []) > 0)
                                ->schema([
                                    self::attributesPivotRepeater(),
                                ]),
                        ]),
                    ]),
            ]);
    }

    /**
     * Create the attributes pivot repeater
     */
    private static function attributesPivotRepeater(): Repeater
    {
        return Repeater::make('attributes_direct_pivot')
            ->label(__('lang.pivot_settings'))
            ->dehydrated(false)
            ->columns(12)
            ->table([
                TableColumn::make(__('lang.attribute')),
                TableColumn::make(__('lang.use_as_variant_option')),
                TableColumn::make(__('lang.sort_order'))
            ])
            ->schema([
                Select::make('attribute_id')
                    ->label(__('lang.attribute'))
                    ->required()
                    ->columnSpan(6)
                    ->options(function (Get $get, ?Product $record) {
                        $chosen = $get('../../attributes_direct') ?? [];
                        return \App\Models\Attribute::query()
                            ->whereIn('id', $chosen)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->reactive()
                    ->distinct(),

                Toggle::make('is_variant_option')
                    ->label(__('lang.use_as_variant_option'))
                    ->default(true)
                    ->columnSpan(3),

                TextInput::make('sort_order')
                    ->label(__('lang.sort_order'))
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(3),
            ])
            ->afterStateHydrated(function (Component $component, $state) {
                $product = $component->getRecord();
                if (!$product) return;

                $rows = $product->attributesDirect()
                    ->withPivot(['is_variant_option', 'sort_order'])
                    ->get()
                    ->map(fn($attr) => [
                        'attribute_id' => $attr->id,
                        'is_variant_option' => (bool) ($attr->pivot->is_variant_option ?? true),
                        'sort_order' => $attr->pivot->sort_order,
                    ])->values()->toArray();

                $component->state($rows);
            })
            ->saveRelationshipsUsing(function ($state, Product $record, Get $get) {
                $selectedIds = collect($get('attributes_direct') ?? [])->map(fn($v) => (int) $v)->all();

                $byAttr = collect($state ?? [])->keyBy(fn($row) => (int) ($row['attribute_id'] ?? 0));
                foreach ($selectedIds as $attrId) {
                    $pivotData = [
                        'is_variant_option' => (bool) data_get($byAttr->get($attrId), 'is_variant_option', true),
                        'sort_order' => data_get($byAttr->get($attrId), 'sort_order'),
                    ];
                    $record->attributesDirect()->updateExistingPivot($attrId, $pivotData, true);
                }
            });
    }
}
