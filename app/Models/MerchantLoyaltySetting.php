<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MerchantLoyaltySetting
 *
 * Database Fields:
 * @property int $id
 * @property int $merchant_id
 * @property bool $is_active
 * @property float $earning_spend_amount
 * @property int $earning_reward_points
 * @property int $redemption_points_block
 * @property float $redemption_discount_value
 * @property int $min_points_to_redeem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
