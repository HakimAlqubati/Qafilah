<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Steps;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Wizard\Step;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImagesStep
{
    /**
     * Create the Images Upload step (currently hidden)
     */
    public static function make(): Step
    {
        return Step::make('images')
            ->hidden()
            ->label(__('lang.images'))
            ->icon('heroicon-o-photo')
            ->columnSpanFull()
            ->schema(self::getSchema());
    }

    /**
     * Get the step schema
     */
    private static function getSchema(): array
    {
        return [
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
        ];
    }
}
