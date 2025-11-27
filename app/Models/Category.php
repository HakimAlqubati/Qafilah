<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'attribute_set_id',
        'active',
        'sort_order',
    ];
    protected $casts = [
        'active' => 'bool',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function attributeSet()
    {
        return $this->belongsTo(AttributeSet::class, 'attribute_set_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
