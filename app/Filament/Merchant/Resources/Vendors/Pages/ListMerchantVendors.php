<?php

namespace App\Filament\Merchant\Resources\Vendors\Pages;

use App\Filament\Merchant\Resources\Vendors\MerchantVendorResource;
use Filament\Resources\Pages\ListRecords;

/**
 * صفحة قائمة Vendors للتاجر.
 * تعرض سجل التاجر الواحد فقط (مقيّد من getEloquentQuery في MerchantVendorResource).
 * لا تحتوي على زر "إنشاء" لأن التاجر لا يستطيع إضافة تجار آخرين.
 */
class ListMerchantVendors extends ListRecords
{
    protected static string $resource = MerchantVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا يوجد CreateAction - التاجر يرى بياناته فقط
        ];
    }
}
