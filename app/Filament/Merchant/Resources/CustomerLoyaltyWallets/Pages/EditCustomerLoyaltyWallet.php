<?php

namespace App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Pages;

use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\CustomerLoyaltyWalletResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerLoyaltyWallet extends EditRecord
{
    protected static string $resource = CustomerLoyaltyWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
