<?php

namespace App\Filament\Merchant\Resources\Vendors\RelationManagers;

use App\Filament\Resources\Vendors\RelationManagers\BranchesRelationManager;
use Illuminate\Database\Eloquent\Model;

/**
 * نسخة لوحة التاجر من BranchesRelationManager.
 * ترث كامل الكود (الفورم، الجدول) من الأصلي،
 * وتتجاوز فقط صلاحيات VendorPolicy التي لا يملكها التاجر.
 * الأمان مضمون: العلاقة branches() تقيّد تلقائياً على parent_id = vendor.id
 */
class MerchantBranchesRelationManager extends BranchesRelationManager
{
    /** إظهار تبويب الفروع */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    /** السماح بإضافة فرع جديد */
    public function canCreate(): bool
    {
        return true;
    }

    /** السماح بتعديل الفروع */
    public function canEdit(Model $record): bool
    {
        return true;
    }

    /** منع حذف الفروع من لوحة التاجر */
    public function canDelete(Model $record): bool
    {
        return false;
    }

    public function canForceDelete(Model $record): bool
    {
        return false;
    }

    public function canRestore(Model $record): bool
    {
        return false;
    }
}

