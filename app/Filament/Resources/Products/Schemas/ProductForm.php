<?php

namespace App\Filament\Resources\Products\Schemas;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeSet;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Product Details')->columnSpanFull()
                    ->tabs([
                        // ----------------------------------------------------
                        // 1. علامة التبويب الأساسية (General)
                        // ----------------------------------------------------
                        Tab::make('General Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // الاسم والسلاج
                                        TextInput::make('name')
                                            ->label('Product Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->reactive()
                                            ->debounce(500),

                                        TextInput::make('slug')
                                            ->label('URL Slug')
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Automatically generated from the name.'),
                                    ]),

                                // الأوصاف
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

                        Tab::make('media')
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
                        // 2. علامة تبويب الكتالوج والتسعير (Catalog & Pricing)
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

                                        // ربط المنتج بقالب الخصائص
                                        Select::make('attribute_set_id')
                                            ->label('Attribute Set')
                                            ->relationship('attributeSet', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Determines the available variant options (e.g., Color, Size).'),
                                    ]),

                                Section::make('Inventory & Pricing')
                                    ->columns(3)
                                    ->schema([
                                        // TextInput::make('sku')
                                        //     ->label('SKU (Stock Keeping Unit)')
                                        //     ->required()
                                        //     ->unique(ignoreRecord: true)
                                        //     ->maxLength(50),

                                        // TextInput::make('base_price')
                                        //     ->label('Base Price (Default)')
                                        //     ->numeric()
                                        //     ->inputMode('decimal')
                                        //     ->required()
                                        //     ->prefix('SAR'),


                                    ]),
                            ]),

                        // ----------------------------------------------------
                        // 3. علامة تبويب الإعدادات (Settings)
                        // ----------------------------------------------------
                        Tab::make('Visibility & Status')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // حالة المنتج
                                        Select::make('status')
                                            ->label('Product Status')
                                            ->options(Product::statusOptions())
                                            ->default(Product::$STATUSES['DRAFT'])
                                            ->required()
                                            ->native(false),

                                        // التمييز
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
}
