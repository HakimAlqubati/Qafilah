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
        'default_currency_id',
        // 'created_by' and 'updated_by' are typically handled automatically by Observers/Events
        // or by manually assigning Auth::id() before saving.
    ];

    protected $casts = [
        'delivery_rate_per_km' => 'decimal:2',
        'min_delivery_charge' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // --- 🔑 Audit Relations ---

    /**
     * Get the User who created this Vendor record.
     */
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
        if (! $this->logo_path) {
            return null;
        }
        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * تحديد ما إذا كان التاجر لديه مستخدم مرتبط
     */
    public function getHasUserAttribute(): bool
    {
        return $this->users()->exists();
    }
}
