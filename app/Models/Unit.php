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
        'description',
        'active',
        'sort_order',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    /**
     * علاقة الوحدة بـ ProductVendorSkuUnits
     */
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
