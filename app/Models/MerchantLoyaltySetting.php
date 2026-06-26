<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantLoyaltySetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'merchant_id',
        'is_active',
        'earning_spend_amount',
        'earning_reward_points',
        'redemption_points_block',
        'redemption_discount_value',
        'min_points_to_redeem',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'earning_spend_amount' => 'decimal:2',
        'redemption_discount_value' => 'decimal:2',
        'earning_reward_points' => 'integer',
        'redemption_points_block' => 'integer',
        'min_points_to_redeem' => 'integer',
    ];

    /**
     * Get the merchant that owns the settings.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
