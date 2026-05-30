<?php

namespace App\Filament\Merchant\Resources\Vendors\Pages;

use App\Filament\Merchant\Resources\Vendors\MerchantVendorResource;
use Filament\Resources\Pages\EditRecord;

/**
 * صفحة تعديل Vendor للتاجر.
 * لا تحتوي على أزرار Delete/ForceDelete/Restore لأن التاجر لا يملك صلاحية حذف حسابه.
 * يُدمج محتوى الصفحة مع الـ RelationManagers (الفروع) في تبويبات مدمجة.
 */
class EditMerchantVendor extends EditRecord
{
    protected static string $resource = MerchantVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا Delete ولا ForceDelete ولا Restore للتاجر
        ];
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
