<?php

namespace App\Filament\Resources\MerchantLoyaltySettings\Pages;

use App\Filament\Resources\MerchantLoyaltySettings\MerchantLoyaltySettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMerchantLoyaltySetting extends EditRecord
{
    protected static string $resource = MerchantLoyaltySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
