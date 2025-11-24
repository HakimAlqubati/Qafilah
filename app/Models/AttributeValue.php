<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'sort_order',
        'is_active',
    ];

    /* ============================================================
     | 🔗 العلاقات
     |============================================================ */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_values', 'attribute_value_id', 'variant_id');
    }

    /* ============================================================
     | ⚙️ Constants (أنواع القيمة)
     |============================================================ */
    public static array $VALUE_TYPES = [
        'TEXT'     => 'text',
        'NUMBER'   => 'number',
        'BOOLEAN'  => 'boolean',
        'SELECT'   => 'select',
        'RADIO'    => 'radio',
        'DATE'     => 'date',
    ];

    /* ============================================================
     | ⚙️ Helpers
     |============================================================ */

    /**
     * 🔹 جلب نوع الإدخال من الخاصية المرتبطة
     */
    public function getInputType(): ?string
    {
        return $this->attribute?->input_type;
    }

    /**
     * 🔹 هل القيمة رقمية؟
     */
    public function isNumeric(): bool
    {
        return $this->getInputType() === self::$VALUE_TYPES['NUMBER'];
    }

    /**
     * 🔹 هل القيمة من نوع Boolean؟
     */
    public function isBoolean(): bool
    {
        return $this->getInputType() === self::$VALUE_TYPES['BOOLEAN'];
    }

    /**
     * 🔹 هل القيمة اختيار (Select أو Radio)؟
     */
    public function isSelectable(): bool
    {
        return in_array($this->getInputType(), [
            self::$VALUE_TYPES['SELECT'],
            self::$VALUE_TYPES['RADIO'],
        ]);
    }

    /**
     * 🔹 عرض القيمة بشكل منسق حسب نوعها
     */
    public function formattedValue(): string
    {
        if ($this->isBoolean()) {
            return $this->value ? 'Yes' : 'No';
        }

        if ($this->isNumeric()) {
            return number_format((float) $this->value, 0);
        }

        if ($this->getInputType() === self::$VALUE_TYPES['DATE']) {
            return date('Y-m-d', strtotime($this->value));
        }

        return (string) $this->value;
    }

    /**
     * 🔹 Helper لتعبئة Dropdowns في الواجهات الإدارية
     */
    public static function listForAttribute($attributeId): array
    {
        return self::where('attribute_id', $attributeId)
            ->orderBy('sort_order')
            ->pluck('value', 'id')
            ->toArray();
    }
}
