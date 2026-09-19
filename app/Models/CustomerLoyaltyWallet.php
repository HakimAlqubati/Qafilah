<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class CustomerLoyaltyWallet
 *
 * Database Fields:
 * @property int $id
 * @property int $customer_id
 * @property int $merchant_id
 * @property int $balance
 * @property-read float $monetary_value
 * @property-read string $formatted_monetary_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerLoyaltyWallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'merchant_id',
        'balance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'monetary_value',
    ];

    /**
     * Get the customer that owns the loyalty wallet.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the merchant associated with the loyalty wallet.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the loyalty settings for the wallet's merchant.
     */
    public function loyaltySetting(): HasOne
    {
        return $this->hasOne(MerchantLoyaltySetting::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the monetary value of the points balance.
     */
    public function getMonetaryValueAttribute(): float
    {
        $discountPerPoint = $this->loyaltySetting?->redemption_discount_value
            ?? $this->merchant?->loyaltySetting?->redemption_discount_value
            ?? 0;

        return round((int) $this->balance * (float) $discountPerPoint, 2);
    }

    /**
     * Get the formatted monetary value with currency symbol.
     */
    public function getFormattedMonetaryValueAttribute(): string
    {
        $symbol = Currency::getMerchantCurrencySymbol($this->merchant_id);

        return $symbol . ' ' . number_format($this->monetary_value, 2);
    }

    /**
     * Get the transactions for the loyalty wallet.
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'wallet_id');
    }
}
