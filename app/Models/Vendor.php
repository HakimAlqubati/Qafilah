<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Vendor extends Model
{
    use SoftDeletes,HasFactory;

    // Fillable fields (essential for mass assignment)
    protected $fillable = [
        'name',
        'slug',
        'contact_person',
        'email',
        'phone',
        'vat_id',
        'status',
        'description',
        'logo_path',
        // 'created_by' and 'updated_by' are typically handled automatically by Observers/Events 
        // or by manually assigning Auth::id() before saving.
    ];

    // --- 🔑 Audit Relations ---

    /**
     * Get the User who created this Vendor record.
     */
    public function creator(): BelongsTo
    {
        // Assumes the User model is App\Models\User and the foreign key is 'created_by'
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the User who last updated this Vendor record.
     */
    public function editor(): BelongsTo
    {
        // Assumes the User model is App\Models\User and the foreign key is 'updated_by'
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --- Other Core Relations (from previous analysis) ---

    // A vendor has many products
    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }

    protected static function boot()
    {
        parent::boot();

        // Set 'created_by' on creation
        static::creating(function ($vendor) {
            if (Auth::check()) {
                $vendor->created_by = Auth::id();
                $vendor->updated_by = Auth::id(); // Typically updated_by is also set on creation
            }
        });

        // Set 'updated_by' on update
        static::updating(function ($vendor) {
            if (Auth::check()) {
                $vendor->updated_by = Auth::id();
            }
        });
    }
}
