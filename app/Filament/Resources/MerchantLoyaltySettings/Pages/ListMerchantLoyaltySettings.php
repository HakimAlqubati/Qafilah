<?php

namespace App\Filament\Resources\MerchantLoyaltySettings\Pages;

use App\Filament\Resources\MerchantLoyaltySettings\MerchantLoyaltySettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerchantLoyaltySettings extends ListRecords
{
    protected static string $resource = MerchantLoyaltySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
