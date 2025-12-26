<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Vendor extends Model
{
    use SoftDeletes, HasFactory;

    // Delivery Time Unit Constants
    public const DELIVERY_TIME_UNIT_HOURS = 'hours';
    public const DELIVERY_TIME_UNIT_DAYS = 'days';

    public const DELIVERY_TIME_UNITS = [
        self::DELIVERY_TIME_UNIT_HOURS => 'hours',
        self::DELIVERY_TIME_UNIT_DAYS => 'days',
    ];

    // Fillable fields (essential for mass assignment)
    protected $fillable = [
        'name',
        'slug',
        'contact_person',
        'email',
        'phone',
        'vat_id',
        'status',
        'description',
        'logo_path',
        'latitude',
        'longitude',
        'delivery_rate_per_km',
        'min_delivery_charge',
        'max_delivery_distance',
        'delivery_time_value',
        'delivery_time_unit',
        'default_currency_id',
        'referrer_id',
        'parent_id',
        'country_id',
        'city_id',
        'district_id',
    ];

    protected $casts = [
        'delivery_rate_per_km' => 'decimal:2',
        'min_delivery_charge' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public const STATUSES = [
        'ACTIVE'   => 'active',
        'INACTIVE' => 'inactive',
        'PENDING'  => 'pending',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUSES['ACTIVE']);
    }
    public function creator(): BelongsTo
    {
        // Assumes the User model is App\Models\User and the foreign key is 'created_by'
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the User who last updated this Vendor record.
     */
    public function editor(): BelongsTo
    {
        // Assumes the User model is App\Models\User and the foreign key is 'updated_by'
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --- Other Core Relations (from previous analysis) ---

    // A vendor has many products
    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }

    public function defaultCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    /**
     * Get the User who referred this vendor (for commission tracking).
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Calculate delivery cost based on distance in KM.
     *
     * @param float $distanceInKm
     * @return float
     */
    public function calculateDeliveryCost(float $distanceInKm): float
    {
        if ($this->max_delivery_distance && $distanceInKm > $this->max_delivery_distance) {
            // Option: return -1 or throw exception to indicate out of range
            return -1.0;
        }

        $cost = $distanceInKm * $this->delivery_rate_per_km;

        return max($cost, $this->min_delivery_charge);
    }

    /**
     * Set the latitude attribute with validation.
     * Ensures latitude is within valid range (-90 to 90).
     *
     * @param float|null $value
     */
    public function setLatitudeAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['latitude'] = null;
            return;
        }

        $numericValue = (float) $value;

        // Validate range
        if ($numericValue < -90 || $numericValue > 90) {
            throw new \InvalidArgumentException(
                __('lang.latitude_validation_error', ['value' => $numericValue])
            );
        }

        $this->attributes['latitude'] = $numericValue;
    }

    /**
     * Set the longitude attribute with validation.
     * Ensures longitude is within valid range (-180 to 180).
     *
     * @param float|null $value
     */
    public function setLongitudeAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['longitude'] = null;
            return;
        }

        $numericValue = (float) $value;

        // Validate range
        if ($numericValue < -180 || $numericValue > 180) {
            throw new \InvalidArgumentException(
                __('lang.longitude_validation_error', ['value' => $numericValue])
            );
        }

        $this->attributes['longitude'] = $numericValue;
    }

    protected static function boot()
    {
        parent::boot();

        // Set 'created_by' on creation
        static::creating(function ($vendor) {
            if (Auth::check()) {
                $vendor->created_by = Auth::id();
                $vendor->updated_by = Auth::id(); // Typically updated_by is also set on creation
            }
        });

        // Set 'updated_by' on update
        static::updating(function ($vendor) {
            if (Auth::check()) {
                $vendor->updated_by = Auth::id();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'parent_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Vendor::class, 'parent_id');
    }

    public function isBranch(): bool
    {
        return !is_null($this->parent_id);
    }

    public function isMainOffice(): bool
    {
        return is_null($this->parent_id);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function offers()
    {
        return $this->hasMany(ProductVendorSku::class);
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * طلبات البائع
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * الحصول على إجمالي المبيعات
     */
    public function getTotalSalesAmount(): float
    {
        return $this->orders()->where('status', '!=', 'cancelled')->sum('total');
    }
    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->logo_path;

        // Inheritance: If logo is null, try to get it from parent
        if (!$path && $this->parent_id) {
            $path = $this->parent->logo_path ?? null;
        }

        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * الحصول على الوصف (مع الوراثة)
     */
    public function getDescriptionInheritedAttribute(): ?string
    {
        if ($this->description) {
            return $this->description;
        }

        if ($this->parent_id) {
            return $this->parent->description ?? null;
        }

        return null;
    }

    /**
     * تحديد ما إذا كان التاجر لديه مستخدم مرتبط
     */
    public function getHasUserAttribute(): bool
    {
        return $this->users()->exists();
    }

    /**
     * Get formatted delivery time (e.g., "2 hours", "3 days")
     */
    public function getDeliveryTimeFormattedAttribute(): ?string
    {
        if (!$this->delivery_time_value || !$this->delivery_time_unit) {
            return null;
        }

        $unitTranslationKey = $this->delivery_time_unit === self::DELIVERY_TIME_UNIT_HOURS
            ? 'lang.hours'
            : 'lang.days';

        return "{$this->delivery_time_value} " . __($unitTranslationKey);
    }

    /**
     * Convert delivery time to hours for calculations
     */
    public function getDeliveryTimeInHoursAttribute(): ?int
    {
        if (!$this->delivery_time_value || !$this->delivery_time_unit) {
            return null;
        }

        return $this->delivery_time_unit === self::DELIVERY_TIME_UNIT_DAYS
            ? $this->delivery_time_value * 24
            : $this->delivery_time_value;
    }

    /**
     * Get delivery time units options for forms
     */
    public static function getDeliveryTimeUnitOptions(): array
    {
        return [
            self::DELIVERY_TIME_UNIT_HOURS => __('lang.hours'),
            self::DELIVERY_TIME_UNIT_DAYS => __('lang.days'),
        ];
    }
}
