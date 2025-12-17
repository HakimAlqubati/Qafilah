<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas;

use App\Models\Currency;
use App\Models\ProductVariant;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductVendorSkuForm
{
    /**
     * Generate a unique vendor SKU
     * Format: VND{vendorId}-PRD{productId}-{randomSuffix}
     */
    public static function generateUniqueVendorSku(int $productId, int $vendorId): string
    {
        do {
            $randomSuffix = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $sku = "VND{$vendorId}-PRD{$productId}-{$randomSuffix}";
        } while (\App\Models\ProductVendorSku::where('vendor_sku', $sku)->exists());

        return $sku;
    }

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
                                    ->afterStateUpdated(function ($set, $get) {
                                        $set('variant_id', null);
                                        $set('attributes', []);

                                        $productId = $get('product_id');
                                        if ($productId) {
                                            $product = \App\Models\Product::with(['attributesDirect', 'units.unit'])->find($productId);
                                            $hasVariantAttributes = $product?->attributesDirect->where('pivot.is_variant_option', true)->isNotEmpty();

                                            // Generate unique vendor_sku
                                            $vendorId = auth()->user()->vendor_id ?? 0;
                                            $uniqueSku = self::generateUniqueVendorSku($productId, $vendorId);
                                            $set('vendor_sku', $uniqueSku);

                                            // إذا المنتج بسيط (بدون متغيرات)
                                            if (!$hasVariantAttributes) {
                                                // Find the default/single variant if exists
                                                $variant = \App\Models\ProductVariant::where('product_id', $productId)->active()->first();
                                                if ($variant) {
                                                    $set('variant_id', $variant->id);
                                                }
                                            }

                                            // تحميل الوحدات الافتراضية من المنتج
                                            $defaultUnits = $product->units->map(fn($pu) => [
                                                'unit_id' => $pu->unit_id,
                                                'package_size' => $pu->package_size,
                                                'cost_price' => $pu->cost_price,
                                                'selling_price' => $pu->selling_price,
                                                'moq' => 1,
                                                'stock' => 0,
                                                'is_default' => $pu->is_base_unit,
                                                'status' => 'active',
                                                'sort_order' => $pu->sort_order,
                                            ])->toArray();
                                            $set('units', $defaultUnits);
                                        }
                                    })
                                    ->required(),

                                // 4. Dynamic Attributes
                                Grid::make(2)
                                    ->schema(function ($get, $set) {
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

                                        // Filter only variant option attributes
                                        $variantAttributes = $sortedAttributes->filter(fn($attr) => $attr->pivot->is_variant_option)->values();

                                        foreach ($variantAttributes as $index => $attribute) {
                                            // Determine if this attribute should be visible (disabled or hidden if previous not selected)
                                            if ($index > 0) {
                                                $prevAttrId = $variantAttributes[$index - 1]->id;
                                                $prevValue = $get("attributes.{$prevAttrId}");
                                                if (empty($prevValue)) {
                                                    break;
                                                }
                                            }

                                            $components[] = Select::make("attributes.{$attribute->id}")
                                                ->label($attribute->name)
                                                ->options(function ($get) use ($productId, $attribute, $variantAttributes, $index) {
                                                    // Filter options based on previous selections
                                                    $query = \App\Models\ProductVariant::where('product_id', $productId)->active();

                                                    // Apply filters from previous attributes
                                                    for ($i = 0; $i < $index; $i++) {
                                                        $prevAttr = $variantAttributes[$i];
                                                        $prevVal = $get("attributes.{$prevAttr->id}");
                                                        if ($prevVal) {
                                                            $query->whereHas('variantValues', function ($q) use ($prevVal) {
                                                                $q->where('attribute_value_id', $prevVal);
                                                            });
                                                        }
                                                    }

                                                    // Get valid attribute values for the current attribute from the filtered variants
                                                    $validVariantIds = $query->pluck('id');

                                                    return \App\Models\AttributeValue::where('attribute_id', $attribute->id)
                                                        ->whereHas('variants', function ($q) use ($validVariantIds) {
                                                            $q->whereIn('product_variants.id', $validVariantIds);
                                                        })
                                                        ->pluck('value', 'id');
                                                })
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($set, $get) use ($variantAttributes, $index, $productId) {
                                                    // Reset subsequent attributes
                                                    for ($i = $index + 1; $i < $variantAttributes->count(); $i++) {
                                                        $nextAttr = $variantAttributes[$i];
                                                        $set("attributes.{$nextAttr->id}", null);
                                                    }
                                                    $set('variant_id', null);

                                                    // Check if all attributes are selected
                                                    $allSelected = true;
                                                    $selectedAttributes = $get('attributes') ?? [];

                                                    foreach ($variantAttributes as $attr) {
                                                        if (empty($selectedAttributes[$attr->id])) {
                                                            $allSelected = false;
                                                            break;
                                                        }
                                                    }

                                                    if ($allSelected) {
                                                        // Find the matching variant
                                                        $query = \App\Models\ProductVariant::where('product_id', $productId)->active();

                                                        foreach ($selectedAttributes as $attrId => $valId) {
                                                            if ($valId) {
                                                                $query->whereHas('variantValues', function ($q) use ($valId) {
                                                                    $q->where('attribute_value_id', $valId);
                                                                });
                                                            }
                                                        }

                                                        $variant = $query->first();
                                                        if ($variant) {
                                                            $set('variant_id', $variant->id);
                                                            // SKU is already generated when product is selected, don't override
                                                        }
                                                    }
                                                });
                                        }
                                        return $components;
                                    }),

                                // Variant Selection (Hidden) - اختياري للمنتجات البسيطة
                                Hidden::make('variant_id')
                                    ->dehydrated(),

                                TextInput::make('vendor_sku')
                                    ->label(__('lang.vendor_sku'))
                                    ->helperText(__('lang.vendor_sku_helper'))
                                    ->maxLength(255)
                                    ->required()
                                    ->visible(fn($get) => filled($get('product_id'))),

                                Select::make('currency_id')
                                    ->label(__('lang.currency'))
                                    ->options(Currency::active()->pluck('code', 'id'))
                                    ->default(fn() => Currency::default()->first()?->id)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->visible(fn($get) => filled($get('product_id')))
                                    ->validationMessages([
                                        'unique' => __('This product variant is already added with this currency.'),
                                    ])
                                    ->rules([
                                        function (Get $get, Component $component) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get, $component) {
                                                $productId = $get('product_id');
                                                $variantId = $get('variant_id');
                                                $vendorId = $get('vendor_id');

                                                if (!$productId || !$vendorId) {
                                                    return;
                                                }

                                                $query = \App\Models\ProductVendorSku::where('product_id', $productId)
                                                    ->where('vendor_id', $vendorId)
                                                    ->where('currency_id', $value);

                                                // إذا كان هناك متغير محدد، نتحقق منه أيضاً
                                                if ($variantId) {
                                                    $query->where('variant_id', $variantId);
                                                } else {
                                                    $query->whereNull('variant_id');
                                                }

                                                // Ignore current record if editing
                                                $record = $component->getRecord();
                                                if ($record) {
                                                    $query->where('id', '!=', $record->id);
                                                }

                                                if ($query->exists()) {
                                                    $fail(__('This product is already added with this currency.'));
                                                }
                                            };
                                        },
                                    ]),

                                Hidden::make('vendor_id')
                                    ->default(fn() => auth()->user()->vendor_id),

                                // حقل مخفي لحفظ product_id مباشرة
                                Hidden::make('product_id')
                                    ->dehydrated(),
                            ]),

                        // Step 2: Units & Pricing
                        Step::make('units')
                            ->label(__('lang.units_pricing'))
                            ->icon('heroicon-o-cube')
                            ->columnSpanFull()
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('units')
                                    ->label(__('lang.unit_prices'))
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->itemLabel(
                                        fn(array $state): ?string =>
                                        \App\Models\Unit::find($state['unit_id'])?->name ?? 'New Unit'
                                    )
                                    ->defaultItems(0)
                                    ->addActionLabel(__('lang.add_unit'))
                                    ->reorderable(true)
                                    ->reorderableWithButtons()
                                    ->columns(3)
                                    ->schema([
                                        Select::make('unit_id')
                                            ->label(__('lang.unit'))
                                            ->options(\App\Models\Unit::active()->pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->columnSpan(1),

                                        TextInput::make('package_size')
                                            ->label(__('lang.package_size'))
                                            ->helperText(__('lang.package_size_helper'))
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->default(1)
                                            ->columnSpan(1),

                                        TextInput::make('moq')
                                            ->label(__('lang.moq'))
                                            ->helperText(__('lang.moq_unit_helper'))
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->default(1)
                                            ->columnSpan(1),

                                        TextInput::make('cost_price')
                                            ->label(__('lang.unit_cost_price'))
                                            ->helperText(__('lang.unit_cost_price_helper'))
                                            ->numeric()
                                            ->nullable()
                                            ->columnSpan(1),

                                        TextInput::make('selling_price')
                                            ->label(__('lang.unit_selling_price'))
                                            ->helperText(__('lang.unit_selling_price_helper'))
                                            ->numeric()
                                            ->required()
                                            ->columnSpan(1),

                                        TextInput::make('stock')
                                            ->label(__('lang.unit_stock'))
                                            ->helperText(__('lang.unit_stock_helper'))
                                            ->numeric()
                                            ->required()
                                            ->default(0)
                                            ->minValue(0)
                                            ->columnSpan(1),

                                        \Filament\Forms\Components\Toggle::make('is_default')
                                            ->label(__('lang.is_default_unit'))
                                            ->helperText(__('lang.is_default_unit_helper'))
                                            ->inline(false)
                                            ->columnSpan(1),

                                        Select::make('status')
                                            ->label(__('lang.status'))
                                            ->options([
                                                'active' => __('lang.active'),
                                                'inactive' => __('lang.inactive'),
                                            ])
                                            ->default('active')
                                            ->required()
                                            ->columnSpan(1),

                                        TextInput::make('sort_order')
                                            ->label(__('lang.sort_order'))
                                            ->helperText(__('lang.sort_order_helper'))
                                            ->numeric()
                                            ->default(0)
                                            ->columnSpan(1),
                                    ])
                            ]),

                        // Step 3: Images Upload
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
