<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'rate',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($currency) {
            // إذا كانت العملة الحالية ستصبح افتراضية
            if ($currency->is_default) {
                // تحقق من وجود عملة افتراضية أخرى
                $existingDefault = static::where('is_default', true)
                    ->where('id', '!=', $currency->id ?? 0)
                    ->first();

                if ($existingDefault) {
                    throw ValidationException::withMessages([
                        'is_default' => __('lang.default_currency_already_exists', [
                            'name' => $existingDefault->name
                        ]),
                    ]);
                }
            }
        });
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
