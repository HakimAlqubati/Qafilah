<?php

namespace App\Filament\Merchant\Resources\Vendors;

use App\Filament\Merchant\Resources\Vendors\Pages\EditMerchantVendor;
use App\Filament\Merchant\Resources\Vendors\Pages\ListMerchantVendors;
use App\Filament\Merchant\Resources\Vendors\RelationManagers\MerchantBranchesRelationManager;
use App\Filament\Resources\Vendors\VendorResource;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * واجهة التاجر لإدارة بياناته الخاصة.
 * يرث كامل الإعدادات (الفورم، الجدول، RelationManagers) من VendorResource في لوحة الأدمن
 * مع تقييد الوصول للتاجر المسجل دخوله فقط.
 */
class MerchantVendorResource extends VendorResource
{
    /**
     * نقيّد الاستعلام ليعيد فقط الـ vendor الخاص بالتاجر المسجل دخوله.
     */
    // يجب تعريف navigationIcon صراحةً لأن Filament يعتمد على static properties
    // وليس على الوراثة الديناميكية لتسجيل الـ Resource في القائمة
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

        public static function getLabel(): ?string
    {
        return __('lang.vendor');
    }

    public static function getModelLabel(): string
    {
        return __('lang.vendor');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lang.vendors');
    }

    public static function getEloquentQuery(): Builder
    {
        $vendorId = Auth::user()?->vendor_id;

        return parent::getEloquentQuery()
            ->where('id', $vendorId);
    }

    /**
     * الشاشات المتاحة في لوحة التاجر (عرض وتعديل فقط، بدون إنشاء أو حذف).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListMerchantVendors::route('/'),
            'edit'  => EditMerchantVendor::route('/{record}/edit'),
        ];
    }

    /**
     * نستخدم MerchantBranchesRelationManager بدلاً من الأصلي
     * لتجاوز VendorPolicy التي تمنع التاجر من رؤية الفروع.
     */
    public static function getRelations(): array
    {
        return [
            MerchantBranchesRelationManager::class,
        ];
    }

    /**
     * إخفاء الـ badge لأن التاجر يرى سجلاً واحداً دائماً.
     */
    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    // -------------------------------------------------------
    // تجاوز صلاحيات VendorPolicy — الأمان يُضمَن عبر
    // getEloquentQuery() الذي يقيّد النتائج لـ vendor_id التاجر.
    // -------------------------------------------------------

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false; // التاجر لا يستطيع إنشاء تاجر جديد
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canForceDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canRestore(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
