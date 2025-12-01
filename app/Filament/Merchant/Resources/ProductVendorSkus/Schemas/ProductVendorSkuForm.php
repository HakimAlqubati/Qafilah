<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas;

use App\Models\Currency;
use App\Models\ProductVariant;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductVendorSkuForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Wizard::make()
                    ->columnSpanFull()
                    ->skippable()
                    ->schema([
                        // Step 1: Product Information
                        Step::make('info')
                            ->label(__('lang.product_information'))
                            ->icon('heroicon-o-information-circle')
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                // 1. Main Category
                                Select::make('main_category_id')
                                    ->label(__('lang.main_category'))
                                    ->options(function () {
                                        return \App\Models\Category::whereNull('parent_id')
                                            ->active()
                                            ->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('sub_category_id', null);
                                        $set('product_id', null);
                                        $set('variant_id', null);
                                        $set('attributes', []);
                                    })
                                    ->required(),

                                // 2. Sub Category
                                Select::make('sub_category_id')
                                    ->label(__('lang.sub_category'))
                                    ->options(function ($get) {
                                        $mainCategoryId = $get('main_category_id');
                                        if (!$mainCategoryId) {
                                            return [];
                                        }
                                        return \App\Models\Category::where('parent_id', $mainCategoryId)
                                            ->active()
                                            ->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('product_id', null);
                                        $set('variant_id', null);
                                        $set('attributes', []);
                                    })
                                    ->required(),

                                // 3. Product
                                Select::make('product_id')
                                    ->label(__('lang.product'))
                                    ->options(function ($get) {
                                        $subCategoryId = $get('sub_category_id');
                                        $mainCategoryId = $get('main_category_id');

                                        // if (!$subCategoryId) {
                                        //     return [];
                                        // }
                                        return \App\Models\Product::whereIn('category_id', [
                                            $subCategoryId,
                                            $mainCategoryId
                                        ])
                                            // ->active()
                                            ->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('variant_id', null);
                                        $set('attributes', []);
                                    })
                                    ->required(),

                                // 4. Dynamic Attributes
                                Grid::make(2)
                                    ->schema(function ($get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return [];
                                        }

                                        $product = \App\Models\Product::with(['attributesDirect.values'])->find($productId);
                                        if (!$product) {
                                            return [];
                                        }

                                        $sortedAttributes = $product->attributesDirect->sortBy('pivot.sort_order')->values();
                                        $components = [];
                                        $previousAttributeId = null;

                                        foreach ($sortedAttributes as $index => $attribute) {
                                            // Only show attributes that are used for variants
                                            if (!$attribute->pivot->is_variant_option) {
                                                continue;
                                            }

                                            // Determine if this attribute should be visible (disabled or hidden if previous not selected)
                                            $isDisabled = false;
                                            if ($index > 0) {
                                                $prevAttrId = $sortedAttributes[$index - 1]->id;
                                                $prevValue = $get("attributes.{$prevAttrId}");
                                                if (empty($prevValue)) {
                                                    $isDisabled = true;
                                                    // Or we can just not add the component to hide it, 
                                                    // but user asked for "appears", so maybe hiding is better.
                                                    // Let's hide it by not adding it to components if previous is missing.
                                                    break;
                                                }
                                            }

                                            $components[] = Select::make("attributes.{$attribute->id}")
                                                ->label($attribute->name)
                                                ->options(function ($get) use ($productId, $attribute, $sortedAttributes, $index) {
                                                    // Filter options based on previous selections
                                                    $query = \App\Models\ProductVariant::where('product_id', $productId);

                                                    // Apply filters from previous attributes
                                                    for ($i = 0; $i < $index; $i++) {
                                                        $prevAttr = $sortedAttributes[$i];
                                                        $prevVal = $get("attributes.{$prevAttr->id}");
                                                        if ($prevVal) {
                                                            $query->whereHas('variantValues', function ($q) use ($prevVal) {
                                                                // $q->where('attribute_value_id', $prevVal);
                                                            });
                                                        }
                                                    }

                                                    // Get valid attribute values for the current attribute from the filtered variants
                                                    $validVariantIds = $query->pluck('id');

                                                    return \App\Models\AttributeValue::where('attribute_id', $attribute->id)
                                                        ->whereHas('variants', function ($q) use ($validVariantIds) {
                                                            // $q->whereIn('product_variants.id', $validVariantIds);
                                                        })
                                                        ->pluck('value', 'id');
                                                })
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($set, $get) use ($sortedAttributes, $index) {
                                                    // Reset subsequent attributes
                                                    for ($i = $index + 1; $i < $sortedAttributes->count(); $i++) {
                                                        $nextAttr = $sortedAttributes[$i];
                                                        $set("attributes.{$nextAttr->id}", null);
                                                    }
                                                    $set('variant_id', null);
                                                });
                                        }
                                        return $components;
                                    }),

                                // Variant Selection (Visible)
                                Select::make('variant_id')
                                    ->label(__('lang.variant'))
                                    ->options(function ($get) {
                                        $productId = $get('product_id');
                                        $attributes = $get('attributes') ?? [];

                                        if (!$productId || empty($attributes)) {
                                            return [];
                                        }

                                        $query = \App\Models\ProductVariant::where('product_id', $productId)
                                            ->active()
                                            ->with(['values.attribute', 'values.attributeValue']);

                                        foreach ($attributes as $attrId => $valId) {
                                            if ($valId) {
                                                $query->whereHas('variantValues', function ($q) use ($valId) {
                                                    $q->where('attribute_value_id', $valId);
                                                });
                                            }
                                        }

                                        return $query->get()->mapWithKeys(function ($variant) {
                                            $name = $variant->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(', ');
                                            return [$variant->id => $name ?: $variant->master_sku];
                                        });
                                    })
                                    ->required()
                                    ->live()
                                    ->preload()
                                    ->searchable()
                                    ->dehydrated(),

                                TextInput::make('vendor_sku')
                                    ->label(__('lang.vendor_sku'))
                                    ->helperText(__('lang.vendor_sku_helper'))
                                    ->maxLength(255)
                                    ->required(),

                                Select::make('currency_id')
                                    ->label(__('lang.currency'))
                                    ->options(Currency::active()->pluck('code', 'id'))
                                    ->default(fn() => Currency::default()->first()?->id)
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('cost_price')
                                    ->label(__('lang.cost_price'))
                                    ->numeric()
                                    ->required(),

                                TextInput::make('selling_price')
                                    ->label(__('lang.selling_price'))
                                    ->numeric()
                                    ->required(),

                                TextInput::make('stock')
                                    ->label(__('lang.stock'))
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('moq')
                                    ->label(__('lang.moq'))
                                    ->helperText(__('lang.moq_helper'))
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Hidden::make('vendor_id')
                                    ->default(fn() => auth()->user()->vendor_id),
                            ]),

                        // Step 2: Images Upload
                        Step::make('images')
                            ->label(__('lang.images'))
                            ->icon('heroicon-o-photo')
                            ->columnSpanFull()
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->disk('public')
                                    ->label(__('lang.product_images'))
                                    ->directory('products')
                                    ->columnSpanFull()
                                    ->image()
                                    ->multiple()
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
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                        return (string) str($file->getClientOriginalName())->prepend('product-');
                                    })
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                                    ->maxSize(2000)
                                    ->imageEditorMode(2)
                                    ->imageEditorEmptyFillColor('#fff000')
                                    ->circleCropper()
                                    ->helperText(__('lang.upload_images_helper')),
                            ])
                    ])
            ]);
    }
}
