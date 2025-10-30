<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeSet extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /* ============================================================
     | 🔗 العلاقات
     |============================================================ */

    // علاقة القالب مع الفئات
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    // علاقة القالب مع السمات (عبر جدول وسيط)
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_set_attributes')
                    ->withPivot('is_required')
                    ->withTimestamps();
    }

    /* ============================================================
     | ⚙️ Helpers
     |============================================================ */

    public static function activeOptions(): array
    {
        return [
            true  => 'Active',
            false => 'Inactive',
        ];
    }

    public function isActive(): bool
    {
        return $this->active === true;
    }
}
