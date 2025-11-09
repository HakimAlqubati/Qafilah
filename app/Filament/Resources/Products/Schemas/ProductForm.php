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
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                                        'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3',
                                        'italic', 'link', 'orderedList', 'redo', 'strike', 'undo',
                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 2) Media
                        // ----------------------------------------------------
                        Tab::make('Media')
                            ->icon('heroicon-o-photo')
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
                                        '16:9', '4:3', '1:1',
                                    ])->maxSize(2000)
                                    ->imageEditorMode(2)
                                    ->imageEditorEmptyFillColor('#fff000')
                                    ->circleCropper(),
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
                                                // عند تغيير الـ Attribute Set نفرّغ عناصر السمات والمتغيرات الحالية
                                                $set('attributes', []);
                                                $set('variants', []); // مهم جداً: تفريغ المتغيرات عند تغيير المجموعة
                                            })
                                            ->helperText('Determines the available variant options and custom fields.'),
                                    ]),

                                Section::make('Inventory & Pricing')
                                    ->columns(3)
                                    ->schema([
                                        // هنا تُضاف حقول SKU/Base Price/Stock إذا كان المنتج بسيطاً (ليس له متغيرات)
                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 4) Attribute Set Fields (Custom Attributes)
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
                                            ->schema([
                                                Select::make('attribute_id')
                                                    ->label('Attribute')
                                                    ->columnSpan(4)
                                                    ->required()
                                                    ->distinct()
                                                    ->searchable()
                                                    ->preload()
                                                    ->reactive()
                                                    ->options(fn(Get $get) => self::getAvailableAttributesForSet($get('../../attribute_set_id')))
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
                                            ])
                                            ->helperText('Values are stored in product_attributes (attribute_id, value).'),
                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 5) Variants (SKU / Barcode / Options) - الحقول المباشرة
                        // ----------------------------------------------------
                        Tab::make('Variants')
                            ->icon('heroicon-o-squares-2x2')
                            ->visible(fn(Get $get) => filled($get('attribute_set_id')))
                            ->schema([
                                Repeater::make('variants')
                                    ->label('Product Variants')
                                    ->relationship('variants')
                                    ->minItems(1)
                                    ->collapsed()
                                    ->reorderable()
                                    ->itemLabel(fn(array $state): ?string => $state['master_sku'] ?? $state['barcode'] ?? 'Variant')
                                    ->schema(function (Get $get) {
                                        // 1. تحديد السمات التي ستُستخدم كخيارات للمتغيرات (اللون، المقاس، إلخ)
                                        $productSetId = $get('attribute_set_id');
                                        $variantAttributes = self::getVariantAttributes($productSetId); // استدعاء الوظيفة الجديدة

                                        // 2. إنشاء حقول التحكم الأساسية للمتغير
                                        $variantFields = [
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
                                                    ->default(\App\Models\ProductVariant::$STATUSES['active'])
                                                    ->required()
                                                    ->native(false)
                                                    ->columnSpan(2),

                                                Toggle::make('is_default')
                                                    ->label('Default Variant?')
                                                    ->inline(false)
                                                    ->helperText('يُنصح بتحديد متغير افتراضي واحد للعرض السريع.')
                                                    ->columnSpan(2)
                                                    ->dehydrated(true) // تأكد من حفظ القيمة
                                            ]),

                                            // حقول الأسعار والمخزون
                                            Grid::make(10)->schema([
                                           

                                          

                                                // حقل خاص للملفات (صور المتغير)
                                                SpatieMediaLibraryFileUpload::make('images')
                                                    ->disk('public')->columnSpanFull()
                                                    ->label('Variant Images')
                                                    ->directory('variants')
                                                    ->collection('variant_images')
                                                    ->image()
                                                    ->multiple()
                                                    ->reorderable()
                                                     ->helperText('صور خاصة لهذا المتغير (مثال: صورة القميص باللون الأزرق).'),
                                            ]),

                                            // حقول الوزن والأبعاد
                                            Grid::make(12)->schema([
                                                TextInput::make('weight')
                                                    ->label('Weight (kg)')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->columnSpan(3),

                                                TextInput::make('dimensions.length')
                                                    ->label('Length (cm)')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->columnSpan(3),

                                                TextInput::make('dimensions.width')
                                                    ->label('Width (cm)')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->columnSpan(3),

                                                TextInput::make('dimensions.height')
                                                    ->label('Height (cm)')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->columnSpan(3),
                                            ]),
                                        ];

                                        // 3. إضافة حقول الخيارات (Options Fields)
                                        $optionFields = [];
                                        foreach ($variantAttributes as $attribute) {
                                            $options = self::buildChoiceOptionsFromValues($attribute);
                                            
                                            // ⬅️ هنا التعديل الجوهري: ننشئ حقل Select لكل خاصية متغير
                                            $optionFields[] = Select::make("attribute_values.{$attribute->id}")
                                                ->label($attribute->name)
                                                ->options($options)
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->columnSpan(4)
                                                ->helperText("Select the {$attribute->name} value for this variant.");
                                        }

                                        // 4. تجميع حقول الخيارات في Grid منفصل
                                        $optionsSection = Section::make('Variant Options')
                                            ->description('Select the specific attribute values (Options) that define this unique variant.')
                                            ->columns(12)->columnSpanFull()
                                            ->schema([
                                                Grid::make(12)
                                                ->columnSpanFull()
                                                ->schema($optionFields)
                                            ]);


                                        // 5. دمج جميع الحقول وإعادتها
                                        return array_merge($variantFields, [$optionsSection]);

                                    })
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
                                            foreach (array_slice($defaultIndexes, 1) as $idx) {
                                                $state[$idx]['is_default'] = false;
                                            }
                                            $set('variants', $state);
                                        }
                                    }),
                            ]),

                        // ----------------------------------------------------
                        // 6) Visibility & Status (تم تغيير الترقيم)
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
                    ])
            ]);
    }

    /**
     * يسترجع جميع السمات المتاحة للاختيار داخل Attribute Set (للتخصيص اليدوي).
     */
    protected static function getAvailableAttributesForSet(?int $setId): array
    {
        if (! $setId) {
            return [];
        }

        $set = AttributeSet::with(['attributes' => function ($q) {
            $q->where('active', true)->orderBy('name');
        }])->find($setId);

        return $set
            ? $set->attributes->pluck('name', 'id')->toArray()
            : [];
    }
    
    /**
     * يسترجع جميع سمات المتغيرات (Choice Type) المحددة لـ Attribute Set.
     * هذه هي الخيارات التي ستُستخدم لإنشاء المتغيرات.
     */
    protected static function getVariantAttributes(?int $setId): \Illuminate\Support\Collection
    {
        if (! $setId) {
            return collect();
        }
        
        /** @var \App\Models\AttributeSet|null $set */
        $set = AttributeSet::with('attributes.values')->find($setId);

        if (!$set) {
            return collect();
        }
        
        // نختار فقط السمات التي تصلح كخيارات للمتغيرات (مثل Select أو Radio)
        return $set->attributes
            ->filter(fn (Attribute $attribute) => $attribute->isChoiceType() && $attribute->active)
            ->sortBy('name');
    }


    /**
     * يبني حقول "value" داخل عنصر الـ Repeater بناءً على نوع إدخال السمة المختارة.
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
                            if (empty($state)) { return null; }
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

    /**
     * يبني خيارات الاختيار (Select/Radio) من AttributeValue
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
}