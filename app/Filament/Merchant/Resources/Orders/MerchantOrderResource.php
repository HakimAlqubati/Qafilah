<?php

namespace App\Filament\Merchant\Resources\Orders;

use App\Filament\Merchant\Resources\Orders\Pages\ListMerchantOrders;
use App\Filament\Merchant\Resources\Orders\Pages\ViewMerchantOrder;
use App\Filament\Resources\Orders\OrderResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * واجهة التاجر لعرض طلباته الخاصة.
 * يرث كامل الإعدادات (الجدول، الفورم، الـ Infolist) من OrderResource في لوحة الأدمن.
 * القيد: يُعرض فقط الطلبات المرتبطة بـ vendor_id الخاص بالتاجر المسجّل.
 */
class MerchantOrderResource extends OrderResource
{
    // صريحاً لأن Filament يعتمد على static properties لتسجيل الـ resource في النافيجيشن
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    /**
     * قيّد الاستعلام بطلبات التاجر الحالي فقط
     */
    public static function getEloquentQuery(): Builder
    {
        $vendorId = Auth::user()?->vendor_id;

        return parent::getEloquentQuery()
            ->where('vendor_id', $vendorId);
    }

    /**
     * الشاشات المتاحة للتاجر: قائمة + عرض فقط (لا إنشاء ولا تعديل)
     */
    public static function getPages(): array
    {
        return [
            'index' => ListMerchantOrders::route('/'),
            'view' => ViewMerchantOrder::route('/{record}'),
        ];
    }

    protected static ?int $pendingOrdersCount = null;

    /**
     * الحصول على عدد الطلبات المعلقة الخاصة بالتاجر بشكل مخزن مؤقتاً لتجنب تكرار الاستعلامات
     */
    protected static function getPendingOrdersCount(): int
    {
        if (static::$pendingOrdersCount === null) {
            $vendorId = Auth::user()?->vendor_id;
            static::$pendingOrdersCount = $vendorId
                ? (int) static::getModel()::where('vendor_id', $vendorId)
                    ->where('status', \App\Models\Order::STATUS_PENDING)
                    ->count()
                : 0;
        }

        return static::$pendingOrdersCount;
    }
    protected static function getOrdersCount(): int
    {
        if (static::$pendingOrdersCount === null) {
            $vendorId = Auth::user()?->vendor_id;
            static::$pendingOrdersCount = $vendorId
                ? (int) static::getModel()::where('vendor_id', $vendorId)
                     ->count()
                : 0;
        }

        return static::$pendingOrdersCount;
    }

    /**
     * Badge يعرض عدد الطلبات المعلقة الخاصة بالتاجر
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getOrdersCount();

        return $count;
    }

    /**
     * لون الـ Badge للطلبات المعلقة (warning/برتقالي للتنبيه)
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getPendingOrdersCount() > 0 ? 'warning' : null;
    }

    /**
     * تلميح (Tooltip) للـ Badge
     */
    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('lang.pending_orders');
    }

    // -------------------------------------------------------
    // تجاوز صلاحيات OrderPolicy — الأمان عبر getEloquentQuery
    // -------------------------------------------------------

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false; // التاجر لا ينشئ طلبات
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false; // التاجر لا يعدّل الطلبات
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
