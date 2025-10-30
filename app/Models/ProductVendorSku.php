<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVendorSku extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'variant_id',
        'vendor_id',
        'vendor_sku',
        'is_default_offer',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default_offer' => 'boolean',
    ];

    /* ============================================================
     | 🔹 Constants
     |============================================================ */
    public static array $STATUSES = [
        'AVAILABLE'    => 'available',
        'OUT_OF_STOCK' => 'out_of_stock',
        'INACTIVE'     => 'inactive',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    // المتغير المرتبط
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // البائع
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    // وحدات البيع (ProductUnits)
    // public function units()
    // {
    //     return $this->hasMany(ProductUnit::class, 'product_vendor_sku_id');
    // }

    // المستخدم الذي أنشأ العرض
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // المستخدم الذي عدله
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopeAvailable($query)
    {
        return $query->where('status', self::$STATUSES['AVAILABLE']);
    }

    public function scopeDefaultOffer($query)
    {
        return $query->where('is_default_offer', true);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    public function isAvailable(): bool
    {
        return $this->status === self::$STATUSES['AVAILABLE'];
    }

    public function isOutOfStock(): bool
    {
        return $this->status === self::$STATUSES['OUT_OF_STOCK'];
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::$STATUSES['AVAILABLE']    => 'Available',
            self::$STATUSES['OUT_OF_STOCK'] => 'Out of Stock',
            self::$STATUSES['INACTIVE']     => 'Inactive',
            default                         => 'Unknown',
        };
    }
}
