<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function districts(): HasManyThrough
    {
        return $this->hasManyThrough(District::class, City::class);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
