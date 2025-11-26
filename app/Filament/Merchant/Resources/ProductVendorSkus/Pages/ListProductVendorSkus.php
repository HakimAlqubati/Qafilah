<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Pages;

use App\Filament\Merchant\Resources\ProductVendorSkus\ProductVendorSkuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProductVendorSkus extends ListRecords
{
    protected static string $resource = ProductVendorSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->icon(Heroicon::PlusCircle)
            ->label(__('lang.create')),
        ];
    }
}
