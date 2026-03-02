<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'type',
        'title',
        'content',
        'is_active',
    ];

    public const TYPE_ABOUT_US = 'about_us';
    public const TYPE_ABOUT_SERVICE = 'about_service';
    public const TYPE_POLICY = 'policy';

    public static function getTypes(): array
    {
        return [
            self::TYPE_ABOUT_US,
            self::TYPE_ABOUT_SERVICE,
            self::TYPE_POLICY,
        ];
    }
}
