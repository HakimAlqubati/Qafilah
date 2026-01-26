<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'credentials',
        'instructions',
        'is_active',
        'mode',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_active' => 'boolean',
    ];


    public const TYPE_ELECTRONIC = 'electronic';
    public const TYPE_CASH = 'cash';
    public const TYPE_TRANSFER = 'transfer';

    public const TYPES = [
        self::TYPE_ELECTRONIC,
        self::TYPE_CASH,
        self::TYPE_TRANSFER,
    ];


    public const MODE_SANDBOX = 'sandbox';
    public const MODE_LIVE = 'live';

    public const MODES = [
        self::MODE_SANDBOX,
        self::MODE_LIVE,
    ];


    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'gateway_id');
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeElectronic($query)
    {
        return $query->where('type', self::TYPE_ELECTRONIC);
    }


    public function scopeCash($query)
    {
        return $query->where('type', self::TYPE_CASH);
    }


    public function scopeTransfer($query)
    {
        return $query->where('type', self::TYPE_TRANSFER);
    }

    public function isElectronic(): bool
    {
        return $this->type === self::TYPE_ELECTRONIC;
    }

    public function isCash(): bool
    {
        return $this->type === self::TYPE_CASH;
    }

    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    public function isLive(): bool
    {
        return $this->mode === self::MODE_LIVE;
    }


    public function isSandbox(): bool
    {
        return $this->mode === self::MODE_SANDBOX;
    }

    public function getTypeLabel(): string
    {
        return __('lang.gateway_type_' . $this->type);
    }


    public function getModeLabel(): string
    {
        return __('lang.gateway_mode_' . $this->mode);
    }

    public function supportsOtp(): bool
    {
         return $this->type === self::TYPE_ELECTRONIC;
    }
}
