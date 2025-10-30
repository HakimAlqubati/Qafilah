<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariantValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'attribute_id',
        'attribute_value_id',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function value()
    {
        return $this->belongsTo(AttributeValue::class, 'attribute_value_id');
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    public function displayValue(): string
    {
        return $this->value?->value ?? '';
    }

    public function attributeLabel(): string
    {
        return $this->attribute?->name ?? '';
    }
}
