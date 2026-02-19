<?php

namespace App\Enums;

enum UserTypes: string
{
    case ADMIN = 'admin';
    case MERCHANT = 'merchant';

    /**
     * Get all values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
