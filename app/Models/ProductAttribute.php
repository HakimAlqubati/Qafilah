<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /* ============================================================
     | ⚙️ Helpers
     |============================================================ */

    public function attributeLabel(): string
    {
        return $this->attribute?->name ?? '';
    }

    public function displayValue(): string
    {
        return $this->value ?? '';
    }

    public static function forProduct($productId)
    {
        return self::where('product_id', $productId)->get();
    }
}
