<?php

namespace App\Filament\Resources\CustomerLoyaltyWallets\Pages;

use App\Filament\Resources\CustomerLoyaltyWallets\CustomerLoyaltyWalletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerLoyaltyWallets extends ListRecords
{
    protected static string $resource = CustomerLoyaltyWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
