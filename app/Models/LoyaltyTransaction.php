<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'order_id',
        'type',
        'points',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * Get the customer loyalty wallet that owns the transaction.
     */
    public function customerLoyaltyWallet(): BelongsTo
    {
        return $this->belongsTo(CustomerLoyaltyWallet::class, 'wallet_id');
    }

    /**
     * Alias for customerLoyaltyWallet relationship.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CustomerLoyaltyWallet::class, 'wallet_id');
    }

    /**
     * Get the order associated with the loyalty transaction.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
