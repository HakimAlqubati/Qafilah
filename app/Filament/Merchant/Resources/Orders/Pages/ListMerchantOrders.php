<?php

namespace App\Filament\Merchant\Resources\Orders\Pages;

use App\Filament\Merchant\Resources\Orders\MerchantOrderResource;
use Filament\Resources\Pages\ListRecords;

/**
 * صفحة قائمة الطلبات للتاجر — بدون CreateAction
 */
class ListMerchantOrders extends ListRecords
{
    protected static string $resource = MerchantOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا يوجد CreateAction — التاجر يعرض طلباته فقط
        ];
    }
}
