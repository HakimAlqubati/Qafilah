<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Filament\Resources\Products\Schemas\Helpers\AttributeHelpers;
use App\Models\ProductVariant;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;

class VariantsStep
{
    /**
     * Create the Variants step
     */
    public static function make(): Step
    {
        return Step::make(__('lang.variants'))
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                Repeater::make('variants')
                    ->label(__('lang.product_variants'))
                    ->relationship('variants')
                    ->minItems(0)
                    ->collapsed()
                    ->defaultItems(0)
                    ->reorderable()
                    ->itemLabel(fn(array $state): ?string => $state['master_sku'] ?? $state['barcode'] ?? 'Variant')
                    ->schema(function (Get $get) {
                        $variantAttributes = AttributeHelpers::getVariantAttributesForProductOrSet($get('id'));

                        $variantFields = [
                            self::basicInfoGrid(),
                            self::imagesGrid(),
                        ];

                        $optionFields = self::buildOptionFields($variantAttributes);

                        $optionsSection = Section::make(__('lang.variant_options'))
                            ->description(__('lang.variant_options_desc'))
                            ->columns(12)
                            ->columnSpanFull()
                            ->schema([
                                Grid::make(12)->columnSpanFull()->schema($optionFields),
                            ]);

                        return array_merge($variantFields, [$optionsSection]);
                    })
                    ->afterStateUpdated(function (Get $get, Set $set, ?array $state) {
                        // Ensure only one default variant
                        if (!is_array($state)) return;
                        $defaultIndexes = [];
                        foreach ($state as $idx => $row) {
                            if (!empty($row['is_default'])) {
                                $defaultIndexes[] = $idx;
                            }
                        }
                        if (count($defaultIndexes) > 1) {
                            foreach (array_slice($defaultIndexes, 1) as $idx) {
                                $state[$idx]['is_default'] = false;
                            }
                            $set('variants', $state);
                        }
                    }),
            ])
            ->visible(function (Get $get, Component $component) {
                $productId = $get('id') ?? ($component->getRecord()?->id);
                if (!$productId) {
                    return false;
                }

                return \App\Models\Attribute::query()
                    ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                    ->where('pa.product_id', $productId)
                    ->where('attributes.active', true)
                    ->exists();
            })
            ->hiddenOn('create');
    }

    /**
     * Basic variant info grid (SKU, barcode, status, is_default)
     */
    private static function basicInfoGrid(): Grid
    {
        return Grid::make(12)->schema([
            TextInput::make('master_sku')
                ->label(__('lang.master_sku'))
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true)
                ->columnSpan(4),

            TextInput::make('barcode')
                ->label(__('lang.barcode'))
                ->maxLength(100)
                ->columnSpan(4),

            Select::make('status')
                ->label(__('lang.status'))
                ->options(ProductVariant::$STATUSES)
                ->default(ProductVariant::$STATUSES['active'])
                ->required()
                ->native(false)
                ->columnSpan(2),

            Toggle::make('is_default')
                ->label(__('lang.default_variant'))
                ->inline(false)
                ->helperText('يُنصح بتحديد متغير افتراضي واحد للعرض السريع.')
                ->columnSpan(2)
                ->dehydrated(true),
        ]);
    }

    /**
     * Variant images grid
     */
    private static function imagesGrid(): Grid
    {
        return Grid::make(10)->schema([
            SpatieMediaLibraryFileUpload::make('images')
                ->disk('public')
                ->columnSpanFull()
                ->label(__('lang.variant_images'))
                ->directory('variants')
                ->collection('variant_images')
                ->multiple()
                ->reorderable()
                ->downloadable()
                ->moveFiles()
                ->previewable()
                ->imagePreviewHeight('250')
                ->loadingIndicatorPosition('right')
                ->panelLayout('integrated')
                ->removeUploadedFileButtonPosition('right')
                ->uploadButtonPosition('right')
                ->uploadProgressIndicatorPosition('right')
                ->panelLayout('grid')
                ->reorderable()
                ->openable()
                ->downloadable(true)
                ->previewable(true)
                ->imageEditor()
                ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                ->imageEditorMode(2)
                ->imageEditorEmptyFillColor('#fff000')
                ->circleCropper()
                ->helperText('صور خاصة لهذا المتغير (مثال: صورة القميص باللون الأزرق).'),
        ]);
    }

    /**
     * Build option fields for variant attributes
     */
    private static function buildOptionFields($variantAttributes): array
    {
        $optionFields = [];

        foreach ($variantAttributes as $attribute) {
            $options = AttributeHelpers::buildChoiceOptionsFromValues($attribute);

            $optionFields[] = Select::make("attribute_values.{$attribute->id}")
                ->label($attribute->name)
                ->options($options)
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->columnSpan(4)
                ->helperText("Select the {$attribute->name} value for this variant.")
                ->afterStateHydrated(function (Component $component, $state) use ($attribute) {
                    $variant = $component->getRecord();

                    if (!$variant) {
                        return;
                    }

                    $valueId = \App\Models\ProductVariantValue::query()
                        ->where('variant_id', $variant->id)
                        ->where('attribute_id', $attribute->id)
                        ->value('attribute_value_id');

                    if ($valueId) {
                        $component->state((string) $valueId);
                    }
                })
                ->dehydrated(false)
                ->saveRelationshipsUsing(function ($state, \App\Models\ProductVariant $record) use ($attribute) {
                    if (blank($state)) {
                        $record->values()
                            ->where('attribute_id', $attribute->id)
                            ->delete();
                        return;
                    }

                    $state = (int) $state;

                    $record->values()->updateOrCreate(
                        ['attribute_id' => $attribute->id],
                        ['attribute_value_id' => $state]
                    );
                });
        }

        return $optionFields;
    }
}
