<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'unit_id',
        'package_size',
        'conversion_factor',
        'selling_price',
        'cost_price',
        'is_base_unit',
        'is_sellable',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_base_unit' => 'boolean',
        'is_sellable' => 'boolean',
        'package_size' => 'integer',
        'conversion_factor' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /* ============================================================
     | 🔹 Constants
     |============================================================ */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public static array $STATUSES = [
        'ACTIVE' => 'active',
        'INACTIVE' => 'inactive',
    ];

    /* ============================================================
     | 🔗 Relations
     |============================================================ */

    /**
     * المنتج المرتبط
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * الوحدة
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * أسعار التجار لهذه الوحدة
     */
    public function vendorPricing()
    {
        return $this->hasMany(ProductVendorSkuUnit::class);
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    /**
     * الوحدة الأساسية فقط
     */
    public function scopeBase($query)
    {
        return $query->where('is_base_unit', true);
    }

    /**
     * الوحدات القابلة للبيع
     */
    public function scopeSellable($query)
    {
        return $query->where('is_sellable', true);
    }

    /**
     * الوحدات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * هل هذه الوحدة الأساسية
     */
    public function isBaseUnit(): bool
    {
        return $this->is_base_unit === true;
    }

    /**
     * هل يمكن البيع بهذه الوحدة
     */
    public function isSellable(): bool
    {
        return $this->is_sellable === true;
    }

    /**
     * هل الوحدة نشطة
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * تحويل الكمية إلى الوحدة الأساسية
     */
    public function convertToBaseUnit(int|float $quantity): float
    {
        return $quantity * $this->package_size * $this->conversion_factor;
    }

    /**
     * الحصول على اسم الوحدة
     */
    public function getUnitNameAttribute(): ?string
    {
        return $this->unit?->name;
    }

    /**
     * الحصول على label الحالة
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => 'Unknown',
        };
    }
    public static function getAvailableUnitsForProduct($productId)
    {
        $productUnits = self::where('product_id', $productId)
            ->whereHas('unit', fn($q) => $q->active())
            ->with('unit')
            ->get();

        if ($productUnits->isNotEmpty()) {
            return $productUnits->pluck('unit');
        }

        $defaultUnit = Unit::active()->where('is_default', true)->first();

        if (! $defaultUnit) {
            return collect();
        }

        self::firstOrCreate(
            ['product_id' => $productId, 'unit_id' => $defaultUnit->id],
            [
                'package_size'      => 1,
                'conversion_factor' => 1,
                'selling_price'     => 0,
                'cost_price'        => 0,
                'is_base_unit'      => true,
                'is_sellable'       => true,
                'status'            => self::STATUS_ACTIVE,
                'sort_order'        => 0,
            ]
        );

        return collect([$defaultUnit]);
    }
}
