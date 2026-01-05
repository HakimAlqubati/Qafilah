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
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', 1);
    }

    public function scopeDefaultActive(Builder $query): Builder
    {
        return $query->active()->where('is_default', 1);
    }
}
