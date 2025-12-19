<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'input_type',
        'is_required',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'active'      => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }
    public static array $INPUT_TYPES = [
        'TEXT'     => 'text',
        'NUMBER'   => 'number',
        'SELECT'   => 'select',
        'RADIO'    => 'radio',
        'BOOLEAN'  => 'boolean',
        'DATE'     => 'date',
    ];

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * 🔹 إرجاع قائمة أنواع الإدخال لاستخدامها في الـ Form أو الـ Dropdown.
     */
    public static function inputTypeOptions(): array
    {
        return [
            self::$INPUT_TYPES['TEXT']    => __('lang.input_type_text'),
            self::$INPUT_TYPES['NUMBER']  => __('lang.input_type_number'),
            self::$INPUT_TYPES['SELECT']  => __('lang.input_type_select'),
            self::$INPUT_TYPES['RADIO']   => __('lang.input_type_radio'),
            self::$INPUT_TYPES['BOOLEAN'] => __('lang.input_type_boolean'),
            self::$INPUT_TYPES['DATE']    => __('lang.input_type_date'),
        ];
    }

    /**
     * 🔹 ترجمة نوع الإدخال الحالي إلى نص مفهوم.
     */
    public function getInputTypeLabel(): string
    {
        return match ($this->input_type) {
            self::$INPUT_TYPES['TEXT']    => __('lang.input_type_text'),
            self::$INPUT_TYPES['NUMBER']  => __('lang.input_type_number'),
            self::$INPUT_TYPES['SELECT']  => __('lang.input_type_select'),
            self::$INPUT_TYPES['RADIO']   => __('lang.input_type_radio'),
            self::$INPUT_TYPES['BOOLEAN'] => __('lang.input_type_boolean'),
            self::$INPUT_TYPES['DATE']    => __('lang.input_type_date'),
            default => __('lang.unknown'),
        };
    }

    /**
     * 🔹 هل الخاصية اختيار من قائمة (select أو radio)؟
     */
    public function isChoiceType(): bool
    {
        return in_array($this->input_type, [
            self::$INPUT_TYPES['SELECT'],
            self::$INPUT_TYPES['RADIO'],
        ]);
    }

    /**
     * 🔹 هل الخاصية رقمية؟
     */
    public function isNumeric(): bool
    {
        return $this->input_type === self::$INPUT_TYPES['NUMBER'];
    }

    /**
     * 🔹 هل الخاصية من نوع Boolean؟
     */
    public function isBoolean(): bool
    {
        return $this->input_type === self::$INPUT_TYPES['BOOLEAN'];
    }

    /* ============================================================
     | 🔗 العلاقات
     |============================================================ */
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function productsDirect()
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
            ->withPivot(['value', 'is_variant_option', 'sort_order'])
            ->withTimestamps();
    }
}
