<?php

namespace App\Models;

use App\Traits\Viewable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, Viewable;

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
        'views',
        'label_attribute',
        'created_by',
        'updated_by',
    ];

    /* ============================================================
     | 🔹 التحويلات (Casts)
     |============================================================ */
    protected $casts = [
        'is_featured' => 'boolean',
        'label_attribute' => 'array',
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

    // علاقة M2M مع الخصائص عبر جدول product_attributes (للـ API والعرض)
    public function attributesDirect()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot(['value', 'is_variant_option', 'sort_order'])
            ->withTimestamps();
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

    // علاقات الوحدات المباشرة (للمنتجات البسيطة)
    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function baseUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base_unit', true);
    }

    public function sellableUnits()
    {
        return $this->hasMany(ProductUnit::class)
            ->where('is_sellable', true)
            ->orderBy('sort_order');
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

    /**
     * بنود الطلبات المرتبطة بالمنتج
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * الحصول على إجمالي الكميات المباعة
     */
    public function getTotalSoldQuantity(): int
    {
        return $this->orderItems()
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->sum('quantity');
    }

    /**
     * هل المنتج بسيط (بدون متغيرات)
     * يتحقق من عدم وجود attributes مع is_variant_option = true
     */
    public function isSimpleProduct(): bool
    {
        return !$this->needsVariants();
    }

    /**
     * هل المنتج يحتاج متغيرات
     * يتحقق من وجود attributes مع is_variant_option = true
     */
    public function needsVariants(): bool
    {
        if ($this->relationLoaded('attributes')) {
            return $this->attributes
                ->where('pivot.is_variant_option', true)
                ->isNotEmpty();
        }

        return $this->attributes()
            ->where('is_variant_option', true)
            ->exists();
    }

    /* ============================================================
     | 🖼️ Helper Methods - Images
     |============================================================ */

    /**
     * الحصول على الصورة الافتراضية للمنتج (الأولى في الترتيب)
     */
    public function getDefaultImage(): ?\Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $this->getFirstMedia('default');
    }

    /**
     * الحصول على رابط الصورة الافتراضية للمنتج
     */
    public function getDefaultImageUrl(string $conversionName = ''): string
    {
        return $this->getFirstMediaUrl('default', $conversionName);
    }

    /**
     * التحقق من وجود صورة افتراضية للمنتج
     */
    public function hasDefaultImage(): bool
    {
        return $this->hasMedia('default');
    }

    /**
     * Accessor: جلب رابط الصورة الافتراضية لاستخدامه مع ImageColumn في Filament
     * يمكن الوصول إليه عبر $product->default_image
     */
    public function getDefaultImageAttribute(): ?string
    {
        return $this->getMedia('default')->last()?->getUrl() ?: null;
        return $this->getFirstMediaUrl('default') ?: null;
    }
}
