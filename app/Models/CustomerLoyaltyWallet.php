<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CustomerLoyaltyWallet
 *
 * Database Fields:
 * @property int $id
 * @property int $customer_id
 * @property int $merchant_id
 * @property int $balance
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
     * Get the transactions for the loyalty wallet.
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'wallet_id');
    }
}
