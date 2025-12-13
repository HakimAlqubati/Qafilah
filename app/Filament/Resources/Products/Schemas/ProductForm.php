<?php

namespace App\Filament\Resources\Products\Schemas;

use Illuminate\Support\Facades\Schema as DBSchema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;
use App\Models\Product;
use App\Models\AttributeSet;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Component; // إبقاء نفس الـ import كما في مشروعك
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;                // إبقاء نفس الـ Schema wrapper الخاص بمشروعك
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Tables\Columns\Column;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $form): Schema
    {
        return $form->schema([
            Wizard::make()
                ->columnSpanFull()
                ->steps([
                    // ----------------------------------------------------
                    // Step 1) General Information
                    // ----------------------------------------------------
                    Step::make(__('lang.general_information'))
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
                                            if (! empty($state)) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),

                                    TextInput::make('slug')
                                        ->label(__('lang.url_slug'))
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->helperText(__('lang.auto_generated')),
                                ]),

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
                        ]),

                    // ----------------------------------------------------
                    // Step 2) Media
                    // ----------------------------------------------------
                    Step::make(__('lang.media'))
                        ->icon('heroicon-o-photo')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('images')
                                ->lazy()
                                ->disk('public')
                                ->label('')
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
                                ->circleCropper(),
                        ]),

                    // ----------------------------------------------------
                    // Step 3.5) Direct Attributes (بدون Set)
                    // ----------------------------------------------------
                    Step::make(__('lang.attributes'))
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Section::make(__('lang.attach_attributes'))
                                ->columns(1)
                                ->schema([
                                    Fieldset::make()->columnSpanFull()->columns(1)->schema([
                                        CheckboxList::make('attributes_direct')
                                            ->label(__('lang.attributes'))
                                            ->relationship('attributesDirect', 'name') // M2M: products <-> attributes عبر product_set_attributes
                                            ->columns(4)
                                            ->bulkToggleable()
                                            ->helperText('اختَر السمات التي تنطبق على هذا المنتج مباشرةً (بدون Set).'),
                                        Section::make('')
                                            ->collapsed(false)
                                            ->visible(fn(Get $get) => filled($get('attributes_direct')) && count($get('attributes_direct') ?? []) > 0)
                                            ->schema([
                                                Repeater::make('attributes_direct_pivot')
                                                    ->label(__('lang.pivot_settings'))
                                                    ->dehydrated(false) // لن نرفعها مباشرة؛ سنحدّث الـ pivot يدويًا
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
                                                                // اعرض فقط السمات المختارة في CheckboxList
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

                                                    // عند تحميل نموذج التعديل: املأ الريبيتر بالقيم من الـ pivot الحالي
                                                    ->afterStateHydrated(function (Component $component, $state) {
                                                        /** @var \App\Models\Product|null $product */
                                                        $product = $component->getRecord();
                                                        if (! $product) return;

                                                        $rows = $product->attributesDirect()
                                                            ->withPivot(['is_variant_option', 'sort_order'])
                                                            ->get()
                                                            ->map(fn($attr) => [
                                                                'attribute_id'      => $attr->id,
                                                                'is_variant_option' => (bool) ($attr->pivot->is_variant_option ?? true),
                                                                'sort_order'        => $attr->pivot->sort_order,
                                                            ])->values()->toArray();

                                                        $component->state($rows);
                                                    })

                                                    // مزامنة الإعدادات مع جدول الـ pivot عند الحفظ
                                                    ->saveRelationshipsUsing(function ($state, Product $record, Get $get) {
                                                        $selectedIds = collect($get('attributes_direct') ?? [])->map(fn($v) => (int) $v)->all();

                                                        // بنينا خريطة من attribute_id => [pivot fields...]
                                                        $byAttr = collect($state ?? [])->keyBy(fn($row) => (int) ($row['attribute_id'] ?? 0));
                                                        foreach ($selectedIds as $attrId) {
                                                            $pivotData = [
                                                                'is_variant_option' => (bool) data_get($byAttr->get($attrId), 'is_variant_option', true),
                                                                'sort_order'        => data_get($byAttr->get($attrId), 'sort_order'),
                                                            ];
                                                            // موجودة مسبقًا (لأن CheckboxList يقوم بالربط/الفصل)، هنا مجرد تحديث للحقول
                                                            $record->attributesDirect()->updateExistingPivot($attrId, $pivotData, true);
                                                        }
                                                    }),
                                            ]),
                                    ]),

                                ]),
                        ]),

                    // ----------------------------------------------------
                    // Step 3) Catalog
                    // ----------------------------------------------------
                    Step::make(__('lang.catalog'))
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Section::make(__('lang.categorization'))
                                ->columns(3)
                                ->schema([
                                    Select::make('category_id')
                                        ->label(__('lang.category'))
                                        ->relationship('category', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload(),

                                    Select::make('brand_id')
                                        ->label(__('lang.brand'))
                                        ->relationship('brand', 'name')
                                        ->nullable()
                                        ->searchable()
                                        ->preload(),

                                ]),


                        ]),

                    // ----------------------------------------------------
                    // Step 3.5) Product Units (للمنتجات البسيطة)
                    // ----------------------------------------------------
                    Step::make(__('lang.product_units'))
                        ->icon('heroicon-o-cube')
                        ->description(__('lang.product_units_desc'))
                        ->visible(function (Get $get, Component $component) {
                            $productId = $get('id') ?? ($component->getRecord()?->id);
                            if (! $productId) {
                                return false; // في الإنشاء، لا نعرض هذا Step
                            }

                            // نعرض فقط للمنتجات البسيطة (بدون variant attributes)
                            $product = \App\Models\Product::find($productId);
                            return $product && !$product->needsVariants();
                        })
                        ->schema([
                            Section::make(__('lang.product_units'))
                                ->description(__('lang.base_unit_desc'))
                                ->schema([
                                    Repeater::make('units')
                                        ->relationship('units')
                                        ->label('')
                                        ->columns(12)
                                        ->defaultItems(0)
                                        ->addActionLabel(__('lang.add_product_unit'))
                                        ->schema([
                                            Grid::make(12)->schema([
                                                Select::make('unit_id')
                                                    ->label(__('lang.unit'))
                                                    ->relationship('unit', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->distinct()
                                                    ->columnSpan(3),

                                                TextInput::make('package_size')
                                                    ->label(__('lang.package_size'))
                                                    ->helperText(__('lang.package_size_helper'))
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->columnSpan(2),

                                                TextInput::make('conversion_factor')
                                                    ->label(__('lang.conversion_factor'))
                                                    ->helperText(__('lang.conversion_factor_helper'))
                                                    ->numeric()
                                                    ->default(1.0000)
                                                    ->step(0.0001)
                                                    ->minValue(0.0001)
                                                    ->columnSpan(2),

                                                Toggle::make('is_base_unit')
                                                    ->label(__('lang.is_base_unit'))
                                                    ->helperText(__('lang.is_base_unit_helper'))
                                                    ->inline(false)
                                                    ->columnSpan(2),

                                                Toggle::make('is_sellable')
                                                    ->label(__('lang.is_sellable'))
                                                    ->helperText(__('lang.is_sellable_helper'))
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->columnSpan(2),

                                                TextInput::make('sort_order')
                                                    ->label(__('lang.sort_order'))
                                                    ->helperText(__('lang.sort_order_helper'))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->columnSpan(1),
                                            ]),
                                        ])
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            \App\Models\Unit::find($state['unit_id'] ?? null)?->name ?? 'Unit'
                                        ),
                                ]),
                        ])
                        ->hiddenOn('create'),

                    // ----------------------------------------------------
                    // Step 4) Attribute Values (Custom Attributes)
                    // ----------------------------------------------------
                    Step::make(__('lang.attribute_values'))
                        ->icon('heroicon-o-rectangle-group')
                        ->schema([
                            Repeater::make('attributes')
                                ->label('')
                                ->relationship('attributes') // product->attributes() => ProductAttribute
                                ->columns(12)
                                ->collapsed(false)
                                ->table([
                                    TableColumn::make(__('lang.attribute'))->width(4),
                                    TableColumn::make(__('lang.value'))->width(8),
                                ])
                                ->reorderable(false)
                                ->minItems(0)->defaultItems(0)
                                ->addActionLabel(__('lang.add_attribute'))
                                ->schema([
                                    Select::make('attribute_id')
                                        ->label(__('lang.attribute'))
                                        ->columnSpan(4)
                                        ->required()
                                        ->distinct()
                                        ->searchable()
                                        ->preload()
                                        ->reactive()
                                        ->options(fn(Get $get) => self::getAvailableAttributesForProductOrSet($get('../../id')))
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $set('value', null);
                                            if ($state) {
                                                $attr = Attribute::with('values')->find($state);
                                                if ($attr && $attr->isBoolean()) {
                                                    $set('value', '0');
                                                }
                                            }
                                        }),

                                    Grid::make()
                                        ->columnSpan(8)
                                        ->columns(12)
                                        ->schema(fn(Get $get) => self::makeAttributeValueField($get('attribute_id'))),
                                ]),
                        ])
                        ->visible(function (Get $get, Component $component) {
                            $productId = $get('id') ?? ($component->getRecord()?->id);
                            if (! $productId) {
                                return false;
                            }

                            // نعتمد على product_attributes
                            return \App\Models\Attribute::query()
                                ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                                ->where('pa.product_id', $productId)
                                ->where('attributes.active', true)
                                ->exists();
                        })
                        ->hiddenOn('create')->hidden(),

                    // ----------------------------------------------------
                    // Step 5) Variants
                    // ----------------------------------------------------
                    Step::make(__('lang.variants'))
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
                                    // 1) سمات المتغيرات (الخيارات)
                                    $variantAttributes = self::getVariantAttributesForProductOrSet($get('id'));

                                    // dd($variantAttributes);
                                    // 2) حقول أساسية
                                    $variantFields = [
                                        Grid::make(12)->schema([
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
                                                ->options(\App\Models\ProductVariant::$STATUSES)
                                                ->default(\App\Models\ProductVariant::$STATUSES['active'])
                                                ->required()
                                                ->native(false)
                                                ->columnSpan(2),

                                            Toggle::make('is_default')
                                                ->label(__('lang.default_variant'))
                                                ->inline(false)
                                                ->helperText('يُنصح بتحديد متغير افتراضي واحد للعرض السريع.')
                                                ->columnSpan(2)
                                                ->dehydrated(true),
                                        ]),

                                        // صور المتغير
                                        Grid::make(10)->schema([
                                            SpatieMediaLibraryFileUpload::make('images')
                                                ->disk('public')->columnSpanFull()
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
                                                // ->maxSize(2000)
                                                ->imageEditorMode(2)
                                                ->imageEditorEmptyFillColor('#fff000')
                                                ->circleCropper()
                                                ->helperText('صور خاصة لهذا المتغير (مثال: صورة القميص باللون الأزرق).'),
                                        ]),
                                    ];

                                    // 3) حقول خيارات المتغير (Attribute Values)
                                    $optionFields = [];
                                    foreach ($variantAttributes as $attribute) {
                                        $options = self::buildChoiceOptionsFromValues($attribute);

                                        $optionFields[] = Select::make("attribute_values.{$attribute->id}")
                                            ->label($attribute->name)
                                            ->options($options)
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->columnSpan(4)
                                            ->helperText("Select the {$attribute->name} value for this variant.")

                                            // عند التحميل (تحرير): تعبئة القيمة المحفوظة
                                            ->afterStateHydrated(function (Component $component, $state) use ($attribute) {
                                                /** @var \App\Models\ProductVariant|null $variant */
                                                $variant = $component->getRecord();

                                                if (! $variant) {
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

                                            // لا نُجفف الحقل مباشرة (سنحفظ بالعلاقة)
                                            ->dehydrated(false)

                                            // حفظ أو تحديث قيمة خيار المتغير
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

                                    // 4) قسم منظم لخيارات المتغير
                                    $optionsSection = Section::make(__('lang.variant_options'))
                                        ->description(__('lang.variant_options_desc'))
                                        ->columns(12)->columnSpanFull()
                                        ->schema([
                                            Grid::make(12)->columnSpanFull()->schema($optionFields),
                                        ]);

                                    return array_merge($variantFields, [$optionsSection]);
                                })
                                ->afterStateUpdated(function (Get $get, Set $set, ?array $state) {
                                    // تأكد أن هناك متغير افتراضي واحد فقط
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
                            if (! $productId) {
                                return false;
                            }

                            // نعتمد على product_attributes
                            return \App\Models\Attribute::query()
                                ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                                ->where('pa.product_id', $productId)
                                ->where('attributes.active', true)
                                ->exists();
                        })
                        ->hiddenOn('create'),

                    // ----------------------------------------------------
                    // Step 6) Visibility & Status
                    // ----------------------------------------------------
                    Step::make(__('lang.visibility_status'))
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('status')
                                        ->label(__('lang.product_status'))
                                        ->options(Product::statusOptions())
                                        ->default(Product::$STATUSES['DRAFT'])
                                        ->required()
                                        ->native(false),

                                    Toggle::make('is_featured')
                                        ->label(__('lang.feature_on_homepage'))
                                        ->default(false)
                                        ->inline(false)
                                        ->helperText(__('lang.feature_on_homepage_desc')),
                                ]),
                        ]),
                ])->skippable(),
        ]);
    }



    /**
     * أو من الربط المباشر عبر product_set_attributes.
     */
    protected static function getAvailableAttributesForProductOrSet(?int $productId): array
    {
        // ✅ أولاً نحاول السمات المرتبطة مباشرةً بالمنتج
        if ($productId) {
            $attributes = Attribute::query()
                ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                ->where('pa.product_id', $productId)
                ->where('attributes.active', true)
                ->orderBy('pa.sort_order')
                ->orderBy('attributes.name')
                ->select('attributes.id', 'attributes.name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            if (!empty($attributes)) {
                return $attributes;
            }
        }



        return [];
    }



    /**
     * يبني حقول value داخل Repeater بحسب نوع إدخال السمة المختارة.
     */
    protected static function makeAttributeValueField(?int $attributeId): array
    {
        if (! $attributeId) {
            return [
                TextInput::make('value')
                    ->label(__('lang.value'))
                    ->placeholder(__('lang.select_attribute_first'))
                    ->disabled()
                    ->columnSpan(12),
            ];
        }

        /** @var Attribute|null $attribute */
        $attribute = Attribute::with('values')->find($attributeId);
        if (! $attribute) {
            return [
                TextInput::make('value')
                    ->label(__('lang.value'))
                    ->placeholder(__('lang.attribute_not_found'))
                    ->disabled()
                    ->columnSpan(12),
            ];
        }

        $type = $attribute->input_type;
        $choiceOptions = null;
        if ($attribute->isChoiceType()) {
            $choiceOptions = self::buildChoiceOptionsFromValues($attribute);
        }

        switch ($type) {
            case Attribute::$INPUT_TYPES['NUMBER']:
                return [
                    TextInput::make('value')
                        ->label(__('lang.value'))
                        ->numeric()
                        ->inputMode('decimal')
                        ->required($attribute->is_required)
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['SELECT']:
                return [
                    Select::make('value')
                        ->label(__('lang.value'))
                        ->options($choiceOptions ?? [])
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder(__('lang.choose_value'))
                        ->required($attribute->is_required)
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['RADIO']:
                return [
                    Radio::make('value')
                        ->label(__('lang.value'))
                        ->options($choiceOptions ?? [])
                        ->required($attribute->is_required)
                        ->inline()
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['BOOLEAN']:
                return [
                    Toggle::make('value')
                        ->label(__('lang.value'))
                        ->inline(false)
                        ->required($attribute->is_required)
                        ->dehydrateStateUsing(fn($state) => $state ? '1' : '0')
                        ->afterStateHydrated(function (Set $set, $state) {
                            $set('value', ($state === '1' || $state === 1 || $state === true));
                        })
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['DATE']:
                return [
                    DatePicker::make('value')
                        ->label(__('lang.value'))
                        ->native(false)
                        ->required($attribute->is_required)
                        ->dehydrateStateUsing(function ($state) {
                            if (empty($state)) {
                                return null;
                            }
                            try {
                                return \Carbon\Carbon::parse($state)->format('Y-m-d');
                            } catch (\Throwable) {
                                return $state;
                            }
                        })
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['TEXT']:
            default:
                return [
                    TextInput::make('value')
                        ->label(__('lang.value'))
                        ->required($attribute->is_required)
                        ->maxLength(255)
                        ->columnSpan(12),
                ];
        }
    }

    /**
     * يبني خيارات الاختيار من AttributeValue
     */
    protected static function buildChoiceOptionsFromValues(Attribute $attribute): array
    {
        return AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->when(
                DBSchema::hasColumn('attribute_values', 'is_active'),
                fn($q) => $q->where('is_active', true)
            )
            ->orderByRaw('COALESCE(sort_order, 999999), value asc')
            ->pluck('value', 'id') // key = id, label = value
            ->toArray();
    }
    protected static function getVariantAttributesForProductOrSet(?int $productId)
    {
        if ($productId) {
            $attrs = Attribute::query()
                ->with('values')
                ->select('attributes.*', 'pa.sort_order', 'pa.is_variant_option')
                ->join('product_attributes as pa', 'pa.attribute_id', '=', 'attributes.id')
                ->where('pa.product_id', $productId)
                ->where('attributes.active', true)
                ->where('pa.is_variant_option', true)
                ->get()
                ->filter(fn(Attribute $a) => $a->isChoiceType())
                ->sortBy(fn($a) => $a->sort_order ?? PHP_INT_MAX)
                ->values();

            if ($attrs->isNotEmpty()) {
                return $attrs;
            }
        }
        return [];
    }
}
