<?php

namespace App\Filament\Merchant\Resources\Orders\Pages;

use App\Filament\Merchant\Resources\Orders\MerchantOrderResource;
use App\Filament\Resources\Orders\Pages\ViewOrder;

/**
 * صفحة عرض تفاصيل الطلب للتاجر — ترث من ViewOrder في الأدمن
 * لا تحتوي على أزرار Edit أو Delete
 */
class ViewMerchantOrder extends ViewOrder
{
    protected static string $resource = MerchantOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // التاجر يعرض الطلب فقط بدون أي actions
        ];
    }
}
