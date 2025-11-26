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
                                Select::make('variant_id')
                                    ->label(__('lang.product'))
                                    ->searchable()
                                    ->options(function () {
                                        return ProductVariant::query()
                                            ->whereHas('product')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($variant) {
                                                return [$variant->id => $variant->product->name . ' (' . $variant->master_sku . ')'];
                                            });
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        return ProductVariant::query()
                                            ->whereHas('product', function (Builder $query) use ($search) {
                                                $query->where('name', 'like', "%{$search}%");
                                            })
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($variant) {
                                                return [$variant->id => $variant->product->name . ' (' . $variant->master_sku . ')'];
                                            });
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $variant = ProductVariant::with('product')->find($value);
                                        return $variant ? $variant->product->name . ' (' . $variant->master_sku . ')' : null;
                                    })
                                    ->required()
                                    ->columnSpanFull(),

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
