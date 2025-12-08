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
            self::$INPUT_TYPES['TEXT']    => 'Text Field',
            self::$INPUT_TYPES['NUMBER']  => 'Number Field',
            self::$INPUT_TYPES['SELECT']  => 'Dropdown (Select)',
            self::$INPUT_TYPES['RADIO']   => 'Radio Buttons',
            self::$INPUT_TYPES['BOOLEAN'] => 'Toggle (Yes/No)',
            self::$INPUT_TYPES['DATE']    => 'Date Picker',
        ];
    }

    /**
     * 🔹 ترجمة نوع الإدخال الحالي إلى نص مفهوم.
     */
    public function getInputTypeLabel(): string
    {
        return match ($this->input_type) {
            self::$INPUT_TYPES['TEXT']    => 'Text Field',
            self::$INPUT_TYPES['NUMBER']  => 'Number Field',
            self::$INPUT_TYPES['SELECT']  => 'Dropdown',
            self::$INPUT_TYPES['RADIO']   => 'Radio Buttons',
            self::$INPUT_TYPES['BOOLEAN'] => 'Boolean Switch',
            self::$INPUT_TYPES['DATE']    => 'Date Picker',
            default => 'Unknown',
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
