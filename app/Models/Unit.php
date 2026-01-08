<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'is_default',
        'description',
        'active',
        'sort_order',
    ];
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /* ============================================================
     | 🚀 Boot Events
     |============================================================ */

    protected static function booted(): void
    {
        static::saving(function (Unit $unit) {
            if ($unit->is_default && $unit->isDirty('is_default')) {
                $existingDefault = static::hasExistingDefault($unit->id);

                if ($existingDefault) {
                    throw new \Exception(
                        __('lang.default_unit_already_exists', ['name' => $existingDefault->name])
                    );
                }
            }
        });
    }

    /* ============================================================
     | 🔧 Helper Methods
     |============================================================ */

    /**
     * تحقق من وجود وحدة افتراضية أخرى
     */
    public static function hasExistingDefault(?int $excludeId = null): ?self
    {
        return static::where('is_default', true)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();
    }

    public function productVendorSkuUnits()
    {
        return $this->hasMany(ProductVendorSkuUnit::class);
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }
}
