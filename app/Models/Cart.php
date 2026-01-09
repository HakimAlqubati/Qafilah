<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'buyer_id','seller_id','cart_token','status',
        'subtotal','tax_amount','discount_amount','shipping_amount','total',
        'notes','expires_at','converted_order_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}

