<?php

namespace App\Filament\Resources\Products\Schemas\Components\Steps;

use App\Filament\Resources\Products\Schemas\Components\Fields\ProductImages;
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
                ProductImages::make(),
            ]);
    }
}
