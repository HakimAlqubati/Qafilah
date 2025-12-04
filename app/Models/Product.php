<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /* ============================================================
     | 🔹 الحقول القابلة للتعبئة
     |============================================================ */
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'attribute_set_id',
        'short_description',
        'description',
        'status',
        'is_featured',
        'created_by',
        'updated_by',
    ];

    /* ============================================================
     | 🔹 التحويلات (Casts)
     |============================================================ */
    protected $casts = [
        'is_featured' => 'boolean',
    ];

    /* ============================================================
     | 🔹 الثوابت (Static Constants)
     |============================================================ */
    public static array $STATUSES = [
        'DRAFT'    => 'draft',
        'ACTIVE'   => 'active',
        'INACTIVE' => 'inactive',
    ];

    /* ============================================================
     | 🔹 الأحداث التلقائية (Boot)
     |============================================================ */
    protected static function booted(): void
    {
        // إنشاء slug تلقائي من الاسم إن لم يُحدد
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    // الفئة
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // الماركة
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // قالب الخصائص (Attribute Set)
    public function attributeSet()
    {
        return $this->belongsTo(AttributeSet::class);
    }

    // المتغيرات (Variants)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // الخصائص الوصفية العامة للمنتج
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }


    public function attributesDirect()
    {
        return $this->belongsToMany(Attribute::class, 'product_set_attributes')
            ->withPivot(['is_variant_option', 'sort_order'])
            ->withTimestamps();
    }

    public function seAttributes()
    {
        return $this->hasMany(ProductSeAttribute::class);
    }

    public function vendors()
    {
        return $this->hasManyThrough(Vendor::class, ProductVariant::class);
    }


    public function vendorOffers()
    {
        // المنتج -> له متغيرات -> لها عروض تجار
        return $this->hasManyThrough(ProductVendorSku::class, ProductVariant::class, 'product_id', 'variant_id', 'id', 'id');
    }

    public function offers()
    {
        return $this->hasMany(ProductVendorSku::class);
    }

    // المستخدم الذي أنشأ المنتج
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // آخر من عدل على المنتج
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    public function isActive(): bool
    {
        return $this->status === self::$STATUSES['ACTIVE'];
    }

    public function isDraft(): bool
    {
        return $this->status === self::$STATUSES['DRAFT'];
    }

    public function isInactive(): bool
    {
        return $this->status === self::$STATUSES['INACTIVE'];
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::$STATUSES['DRAFT']    => 'Draft',
            self::$STATUSES['ACTIVE']   => 'Active',
            self::$STATUSES['INACTIVE'] => 'Inactive',
            default                     => 'Unknown',
        };
    }

    public static function statusOptions(): array
    {
        return [
            self::$STATUSES['DRAFT']    => 'Draft',
            self::$STATUSES['ACTIVE']   => 'Active',
            self::$STATUSES['INACTIVE'] => 'Inactive',
        ];
    }

    /* ============================================================
     | 🧩 Scopes
     |============================================================ */
    public function scopeActive($query)
    {
        return $query->where('status', self::$STATUSES['ACTIVE']);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
