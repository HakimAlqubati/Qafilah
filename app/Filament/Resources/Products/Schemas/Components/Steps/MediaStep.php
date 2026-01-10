<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaStep
{
    /**
     * Create the Media step
     */
    public static function make(): Step
    {
        return Step::make(__('lang.media'))
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
            ]);
    }
}
