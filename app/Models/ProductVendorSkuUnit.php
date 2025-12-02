<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVendorSkuUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_vendor_sku_id',
        'unit_id',
        'package_size',
        'cost_price',
        'selling_price',
        'stock',
        'moq',
        'is_default',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'package_size' => 'integer',
        'stock' => 'integer',
        'moq' => 'integer',
        'sort_order' => 'integer',
    ];

    /* ============================================================
     | 🔹 Constants
     |============================================================ */
    public static array $STATUSES = [
        'ACTIVE'   => 'active',
        'INACTIVE' => 'inactive',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    /**
     * العرض المرتبط (ProductVendorSku)
     */
    public function productVendorSku()
    {
        return $this->belongsTo(ProductVendorSku::class, 'product_vendor_sku_id');
    }

    /**
     * الوحدة (Unit)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * المستخدم الذي أنشأ السجل
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * المستخدم الذي عدل السجل
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopeActive($query)
    {
        return $query->where('status', self::$STATUSES['ACTIVE']);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    public function isActive(): bool
    {
        return $this->status === self::$STATUSES['ACTIVE'];
    }

    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::$STATUSES['ACTIVE']   => 'Active',
            self::$STATUSES['INACTIVE'] => 'Inactive',
            default                     => 'Unknown',
        };
    }

    /**
     * حساب السعر للقطعة الواحدة
     */
    public function getPricePerPiece(): float
    {
        if ($this->package_size <= 0) {
            return 0;
        }

        return round($this->selling_price / $this->package_size, 2);
    }

    /**
     * حساب التكلفة للقطعة الواحدة
     */
    public function getCostPerPiece(): float
    {
        if ($this->package_size <= 0 || !$this->cost_price) {
            return 0;
        }

        return round($this->cost_price / $this->package_size, 2);
    }

    /**
     * حساب الربح للوحدة الكاملة
     */
    public function getProfit(): float
    {
        if (!$this->cost_price) {
            return 0;
        }

        return $this->selling_price - $this->cost_price;
    }

    /**
     * حساب نسبة الربح
     */
    public function getProfitMargin(): float
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return 0;
        }

        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }
}
