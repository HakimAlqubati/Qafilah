<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProductSeAttribute extends Model
{
    protected $table = 'product_set_attributes';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'is_variant_option',
        'sort_order',
    ];

    protected $casts = [
        'is_variant_option' => 'boolean',
        'sort_order'        => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function seAttributes()
    {
        return $this->hasMany(ProductSeAttribute::class)
            ->orderBy('sort_order');
    }
}
