<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Setting extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * The attributes that should be included in audit.
     *
     * @var array<string>
     */
    protected $auditInclude = ['key', 'value'];

    /**
     * Get a setting by its key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by its key.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public static function setSetting(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
