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
        'estimated_delivery_value',
        'estimated_delivery_unit',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'estimated_delivery_value' => 'integer',
    ];

    /**
     * Get the vendor that owns the shipping policy.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
