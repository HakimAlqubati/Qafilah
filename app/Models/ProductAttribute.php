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
        'is_variant_option',
        'sort_order',
    ];

    protected $casts = [
        'is_variant_option' => 'boolean',
        'sort_order' => 'integer',
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

    public function isVariantOption(): bool
    {
        return $this->is_variant_option === true;
    }

    public static function forProduct($productId)
    {
        return self::where('product_id', $productId)
            ->orderBy('sort_order')
            ->get();
    }

    public static function variantOptionsForProduct($productId)
    {
        return self::where('product_id', $productId)
            ->where('is_variant_option', true)
            ->orderBy('sort_order')
            ->get();
    }
}
