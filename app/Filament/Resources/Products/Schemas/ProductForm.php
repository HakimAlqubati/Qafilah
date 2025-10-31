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
use Filament\Forms\Components\Hidden;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeSet;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Product Details')->columnSpanFull()
                    ->tabs([
                        // ----------------------------------------------------
                        // 1) General
                        // ----------------------------------------------------
                        Tab::make('General Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Product Name')
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
                                            ->label('URL Slug')
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Automatically generated from the name.'),
                                    ]),

                                Textarea::make('short_description')
                                    ->label('Short Description')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->columnSpanFull(),

                                RichEditor::make('description')
                                    ->label('Detailed Description')
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
                        // 2) Media
                        // ----------------------------------------------------
                        Tab::make('Media')
                            ->icon(Heroicon::Photo)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('images')
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
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])->maxSize(2000)
                                    ->imageEditorMode(2)
                                    ->imageEditorEmptyFillColor('#fff000')
                                    ->circleCropper()
                            ]),

                        // ----------------------------------------------------
                        // 3) Catalog & Pricing
                        // ----------------------------------------------------
                        Tab::make('Catalog & Pricing')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Section::make('Categorization')
                                    ->columns(3)
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        Select::make('brand_id')
                                            ->label('Brand / Manufacturer')
                                            ->relationship('brand', 'name')
                                            ->nullable()
                                            ->searchable()
                                            ->preload(),

                                        Select::make('attribute_set_id')
                                            ->label('Attribute Set')
                                            ->relationship('attributeSet', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->afterStateUpdated(function (Set $set) {
                                                // عند تغيير الـ Attribute Set نفرّغ عناصر السمات الحالية
                                                $set('attributes', []);
                                            })
                                            ->helperText('Determines the available variant options and custom fields.'),
                                    ]),

                                Section::make('Inventory & Pricing')
                                    ->columns(3)
                                    ->schema([
                                        // مستقبلاً: SKU/Prices/Stock...
                                        // TextInput::make('sku')->label('SKU')->required()->unique(ignoreRecord: true)->maxLength(50),
                                        // TextInput::make('base_price')->label('Base Price')->numeric()->inputMode('decimal')->prefix('SAR'),
                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 4) Attribute Set Fields (Dynamic by input_type)
                        // ----------------------------------------------------
                        Tab::make('Attribute Set Fields')
                            ->icon('heroicon-o-rectangle-group')
                            ->visible(fn(Get $get) => filled($get('attribute_set_id')))
                            ->schema([
                                Section::make('Custom Fields from Attribute Set')
                                    ->description('Fill in attributes defined by the selected Attribute Set. Field types adapt automatically (text/number/select/radio/boolean/date).')
                                    ->columns(1)
                                    ->schema([
                                        Repeater::make('attributes')
                                            ->label('Custom Attributes')
                                            ->relationship('attributes') // product->attributes() => ProductAttribute
                                            ->columns(12)
                                            ->collapsed(false)
                                            ->reorderable(false)
                                            ->minItems(0)
                                            ->addActionLabel('Add Attribute')
                                            ->table([
                                                TableColumn::make('Attribute'),
                                                TableColumn::make('Value'),
                                            ])
                                            ->schema([
                                                // اختيار السمة (محصور على الـ Attribute Set المختار)
                                                Select::make('attribute_id')
                                                    ->label('Attribute')
                                                    ->columnSpan(4)
                                                    ->required()
                                                    ->distinct()
                                                    ->searchable()
                                                    ->preload()
                                                    ->reactive()
                                                    ->options(function (Get $get) {
                                                        $setId = $get('../../attribute_set_id');
                                                        if (! $setId) {
                                                            return [];
                                                        }

                                                        $set = AttributeSet::with(['attributes' => function ($q) {
                                                            $q->where('active', true)->orderBy('name');
                                                        }])->find($setId);

                                                        return $set
                                                            ? $set->attributes->pluck('name', 'id')->toArray()
                                                            : [];
                                                    })
                                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                        // إعادة ضبط قيمة value عند تغيير السمة
                                                        $set('value', null);

                                                        // إذا كانت Boolean نحضّرها كـ false افتراضيًا
                                                        if ($state) {
                                                            $attr = Attribute::with('values')->find($state);
                                                            if ($attr && $attr->isBoolean()) {
                                                                $set('value', '0');
                                                            }
                                                        }
                                                    }),

                                                // الحقل الديناميكي لقيمة السمة بحسب نوع الإدخال
                                                // نُنشئ Schema ديناميكي داخل نفس عنصر الـ Repeater Row
                                                Grid::make()
                                                    ->columnSpan(8)
                                                    ->columns(12)
                                                    ->schema(function (Get $get) {
                                                        $attributeId = $get('attribute_id');
                                                        return self::makeAttributeValueField($attributeId);
                                                    }),
                                            ])
                                            ->helperText('Values are stored in product_attributes (attribute_id, value). For boolean we store 1/0, for date we store Y-m-d, for select/radio we store the chosen label/value.'),
                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 5) Visibility & Status
                        // ----------------------------------------------------
                        Tab::make('Visibility & Status')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('status')
                                            ->label('Product Status')
                                            ->options(Product::statusOptions())
                                            ->default(Product::$STATUSES['DRAFT'])
                                            ->required()
                                            ->native(false),

                                        Toggle::make('is_featured')
                                            ->label('Feature on Homepage?')
                                            ->default(false)
                                            ->inline(false)
                                            ->helperText('If enabled, the product will be highlighted in featured sections.'),
                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 5) Variants (SKU / Barcode / Options)
                        // ----------------------------------------------------
                        Tab::make('Variants')
                            ->icon('heroicon-o-squares-2x2')
                            ->visible(fn(Get $get) => filled($get('attribute_set_id')))
                            ->schema([
                                Section::make('Product Variants')
                                    ->description('أضف المتغيرات (SKU/Barcode) مع قيم السمات المكوّنة للمتغير (مثل اللون/المقاس).')
                                    ->columns(1)
                                    ->schema([
                                        Repeater::make('variants')
                                            ->label('Variants')
                                            ->relationship('variants') // ← هذا يربط المتغيرات بالمنتج الحالي
                                            ->minItems(1)
                                            ->collapsed()            // اجعل كل صف مطوي افتراضياً
                                            ->reorderable()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                $state['master_sku'] ?? $state['barcode'] ?? 'Variant'
                                            )
                                            ->schema([
                                                Grid::make(12)->schema([
                                                    TextInput::make('master_sku')
                                                        ->label('Master SKU')
                                                        ->required()
                                                        ->maxLength(100)
                                                        ->unique(ignoreRecord: true)
                                                        ->columnSpan(4),

                                                    TextInput::make('barcode')
                                                        ->label('Barcode')
                                                        ->maxLength(100)
                                                        ->columnSpan(4),

                                                    Select::make('status')
                                                        ->label('Status')
                                                        ->options(\App\Models\ProductVariant::$STATUSES)
                                                        ->default(\App\Models\ProductVariant::$STATUSES['ACTIVE'])
                                                        ->required()
                                                        ->native(false)
                                                        ->columnSpan(2),

                                                    Toggle::make('is_default')
                                                        ->label('Default Variant?')
                                                        ->inline(false)
                                                        ->helperText('يُنصح بتحديد متغير افتراضي واحد للعرض السريع.')
                                                        ->columnSpan(2),

                                                    TextInput::make('weight')
                                                        ->label('Weight (kg)')
                                                        ->numeric()
                                                        ->inputMode('decimal')
                                                        ->columnSpan(3),

                                                    TextInput::make('dimensions.length')
                                                        ->label('Length (cm)')
                                                        ->numeric()
                                                        ->inputMode('decimal')
                                                        ->dehydrated(true)
                                                        ->columnSpan(3),

                                                    TextInput::make('dimensions.width')
                                                        ->label('Width (cm)')
                                                        ->numeric()
                                                        ->inputMode('decimal')
                                                        ->dehydrated(true)
                                                        ->columnSpan(3),

                                                    TextInput::make('dimensions.height')
                                                        ->label('Height (cm)')
                                                        ->numeric()
                                                        ->inputMode('decimal')
                                                        ->dehydrated(true)
                                                        ->columnSpan(3),
                                                ]),

                                                // القيم المُكوِّنة للمتغير (لون/مقاس/…)
                                                Repeater::make('values')
                                                    ->label('Variant Options')
                                                    ->relationship('values') // ← هذا يربط ProductVariantValue بالـVariant الحالي
                                                    ->minItems(0)
                                                    ->reorderable(false)
                                                    ->columns(12)
                                                    ->addActionLabel('Add Option')
                                                    ->schema([
                                                        SpatieMediaLibraryFileUpload::make('images')
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
                                                            ->imageEditorAspectRatios([
                                                                '16:9',
                                                                '4:3',
                                                                '1:1',
                                                            ])->maxSize(2000)
                                                            ->imageEditorMode(2)
                                                            ->imageEditorEmptyFillColor('#fff000')
                                                            ->circleCropper(),
                                                        // اختيار السمة من الـ Attribute Set المختار بالمنتج
                                                        Select::make('attribute_id')
                                                            ->label('Attribute')
                                                            ->required()
                                                            ->columnSpan(6)
                                                            ->searchable()
                                                            ->preload()
                                                            ->options(function (Get $get) {
                                                                $productSetId = $get('../../../../attribute_set_id'); // اصعد لأعلى حتى تبويب المنتج
                                                                if (! $productSetId) {
                                                                    return [];
                                                                }
                                                                /** @var \App\Models\AttributeSet|null $set */
                                                                $set = \App\Models\AttributeSet::with(['attributes' => function ($q) {
                                                                    $q->where('active', true)->orderBy('name');
                                                                }])->find($productSetId);

                                                                return $set
                                                                    ? $set->attributes->pluck('name', 'id')->toArray()
                                                                    : [];
                                                            })
                                                            ->reactive()
                                                            ->afterStateUpdated(fn(Set $set) => $set('attribute_value_id', null)),

                                                        // اختيار قيمة السمة من AttributeValue
                                                        Select::make('attribute_value_id')
                                                            ->label('Value')
                                                            ->required()
                                                            ->columnSpan(6)
                                                            ->searchable()
                                                            ->preload()
                                                            ->options(function (Get $get) {
                                                                $attrId = $get('attribute_id');
                                                                if (! $attrId) return [];
                                                                return \App\Models\AttributeValue::query()
                                                                    ->where('attribute_id', $attrId)
                                                                    ->when(
                                                                        \Illuminate\Support\Facades\Schema::hasColumn('attribute_values', 'is_active'),
                                                                        fn($q) => $q->where('is_active', true)
                                                                    )
                                                                    ->orderByRaw('COALESCE(sort_order, 999999), value asc')
                                                                    ->pluck('value', 'id')
                                                                    ->toArray();
                                                            }),
                                                    ])
                                                    ->helperText('مثال: اللون = أسود، المقاس = L. هذه القيم تُحدد هوية المتغير.'),
                                            ])
                                            ->afterStateUpdated(function (Get $get, Set $set, ?array $state) {
                                                // اجعل متغيرًا افتراضيًا واحدًا فقط إن وُجد أكثر من واحد محدد
                                                if (!is_array($state)) return;
                                                $defaultIndexes = [];
                                                foreach ($state as $idx => $row) {
                                                    if (!empty($row['is_default'])) {
                                                        $defaultIndexes[] = $idx;
                                                    }
                                                }
                                                if (count($defaultIndexes) > 1) {
                                                    // اترك الأول وألغِ البقية
                                                    foreach (array_slice($defaultIndexes, 1) as $idx) {
                                                        $state[$idx]['is_default'] = false;
                                                    }
                                                    $set('variants', $state);
                                                }
                                            }),
                                    ]),
                            ]),


                    ])
            ]);
    }

    /**
     * يبني حقول "value" داخل عنصر الـ Repeater بناءً على نوع إدخال السمة المختارة.
     * نُعيد مصفوفة من عناصر Filament (داخل Grid بسطر واحد).
     */
    protected static function makeAttributeValueField(?int $attributeId): array
    {
        if (! $attributeId) {
            return [
                TextInput::make('value')
                    ->label('Value')
                    ->placeholder('Select an attribute first...')
                    ->disabled()
                    ->columnSpan(12),
            ];
        }

        /** @var Attribute|null $attribute */
        $attribute = Attribute::with('values')->find($attributeId);
        if (! $attribute) {
            return [
                TextInput::make('value')
                    ->label('Value')
                    ->placeholder('Attribute not found.')
                    ->disabled()
                    ->columnSpan(12),
            ];
        }

        $type = $attribute->input_type;

        // إن كانت الخاصية اختيارية (select/radio) نجهّز الخيارات من AttributeValue
        $choiceOptions = null;
        if ($attribute->isChoiceType()) {
            $choiceOptions = self::buildChoiceOptionsFromValues($attribute);
        }

        switch ($type) {
            case Attribute::$INPUT_TYPES['NUMBER']:
                return [
                    TextInput::make('value')
                        ->label('Value')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required($attribute->is_required)
                        ->columnSpan(12),
                ];

                // ✅ إذا كان النوع SELECT نستخدم Select::make بشكل صريح
            case Attribute::$INPUT_TYPES['SELECT']:
                return [
                    Select::make('value')
                        ->label('Value')
                        ->options($choiceOptions ?? [])
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Choose a value')
                        ->required($attribute->is_required)
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['RADIO']:
                return [
                    Radio::make('value')
                        ->label('Value')
                        ->options($choiceOptions ?? [])
                        ->required($attribute->is_required)
                        ->inline()
                        ->columnSpan(12),
                ];

            case Attribute::$INPUT_TYPES['BOOLEAN']:
                return [
                    Toggle::make('value')
                        ->label('Value')
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
                        ->label('Value')
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
                        ->label('Value')
                        ->required($attribute->is_required)
                        ->maxLength(255)
                        ->columnSpan(12),
                ];
        }
    }

    protected static function buildChoiceOptionsFromValues(Attribute $attribute): array
    {
        return AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->when(
                DBSchema::hasColumn('attribute_values', 'is_active'),
                fn($q) => $q->where('is_active', true)
            )
            // ترتيب: sort_order أولاً ثم value كبديل
            ->orderByRaw('COALESCE(sort_order, 999999), value asc')
            ->pluck('value', 'id')   // key = id, label = value
            ->toArray();
    }

    protected static function buildChoiceOptions(Attribute $attribute): array
    {
        // خذ الأسماء، احذف الفارغ منها، أزل التكرار، وحوّلها إلى مصفوفة key=value = name
        return $attribute->values
            ->map(fn($v) => $v->name)
            ->filter(fn($name) => filled($name))
            ->unique()
            ->values()
            ->mapWithKeys(fn($name) => [(string) $name => (string) $name])
            ->all();
    }
}
