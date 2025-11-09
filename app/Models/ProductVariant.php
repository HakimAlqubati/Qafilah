<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'master_sku',
        'barcode',
        'weight',
        'dimensions',
        'status',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'dimensions' => 'array',
    ];

    /* ============================================================
     | 🔹 Constants
     |============================================================ */
    public static array $STATUSES = [
        'DRAFT'    => 'draft',
        'active'   => 'active',
        'INACTIVE' => 'inactive',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class, 'variant_id');
    }

    public function variantValues()
    {
        // نربط المنتج بقيم السمات مباشرة عبر جدول الربط ProductVariantValue
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values', 'variant_id', 'attribute_value_id')
            // نضيف العمود الذي يحدد السمة التي تنتمي إليها هذه القيمة (مهم جداً)
            ->withPivot('attribute_id')
            ->withTimestamps();
    }

    public function vendorOffers()
    {
        return $this->hasMany(ProductVendorSku::class, 'variant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */
    public function scopeActive($query)
    {
        return $query->where('status', self::$STATUSES['active']);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */
    public function isActive(): bool
    {
        return $this->status === self::$STATUSES['active'];
    }

    public function isDraft(): bool
    {
        return $this->status === self::$STATUSES['DRAFT'];
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::$STATUSES['DRAFT']    => 'Draft',
            self::$STATUSES['active']   => 'Active',
            self::$STATUSES['INACTIVE'] => 'Inactive',
            default                     => 'Unknown',
        };
    }
}
