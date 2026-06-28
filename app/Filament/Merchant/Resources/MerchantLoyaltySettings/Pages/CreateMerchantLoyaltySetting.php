<?php

namespace App\Filament\Merchant\Resources\MerchantLoyaltySettings\Pages;

use App\Filament\Merchant\Resources\MerchantLoyaltySettings\MerchantLoyaltySettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchantLoyaltySetting extends CreateRecord
{
    protected static string $resource = MerchantLoyaltySettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
