<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariantValue extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

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

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class, 'attribute_value_id');
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    public function displayValue(): string
    {
        return $this->attributeValue?->value ?? '';
    }

    public function attributeLabel(): string
    {
        return $this->attribute?->name ?? '';
    }
}
