<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'variant_id',
        'product_vendor_sku_id', 'product_vendor_sku_unit_id', 'unit_id',
        'sku', 'package_size', 'quantity',
        'unit_price', 'discount', 'tax', 'total', 'notes',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

