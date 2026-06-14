<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'is_free',
        'min_order_amount',
        'charge_type',
        'fixed_amount',
        'per_km_rate',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
    ];

    /**
     * Get the vendor that owns the shipping policy.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
