<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model
{
    use HasFactory;
    use InteractsWithMedia;
    protected $fillable = ['name','title','body','sort_order','is_active'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

}
