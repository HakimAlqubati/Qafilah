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

    /* ============================================================
     | 🔹 Constants - أنواع البوابات
     |============================================================ */

    public const TYPE_ELECTRONIC = 'electronic';
    public const TYPE_CASH = 'cash';
    public const TYPE_TRANSFER = 'transfer';

    public const TYPES = [
        self::TYPE_ELECTRONIC => 'دفع إلكتروني',
        self::TYPE_CASH => 'دفع نقدي',
        self::TYPE_TRANSFER => 'تحويل بنكي',
    ];

    /* ============================================================
     | 🔹 Constants - أوضاع التشغيل
     |============================================================ */

    public const MODE_SANDBOX = 'sandbox';
    public const MODE_LIVE = 'live';

    public const MODES = [
        self::MODE_SANDBOX => 'تجريبي',
        self::MODE_LIVE => 'إنتاجي',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'gateway_id');
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    /**
     * نطاق للبوابات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق للبوابات الإلكترونية
     */
    public function scopeElectronic($query)
    {
        return $query->where('type', self::TYPE_ELECTRONIC);
    }

    /**
     * نطاق للبوابات النقدية
     */
    public function scopeCash($query)
    {
        return $query->where('type', self::TYPE_CASH);
    }

    /**
     * نطاق لبوابات التحويل
     */
    public function scopeTransfer($query)
    {
        return $query->where('type', self::TYPE_TRANSFER);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * هل البوابة إلكترونية؟
     */
    public function isElectronic(): bool
    {
        return $this->type === self::TYPE_ELECTRONIC;
    }

    /**
     * هل البوابة نقدية؟
     */
    public function isCash(): bool
    {
        return $this->type === self::TYPE_CASH;
    }

    /**
     * هل البوابة تحويل بنكي؟
     */
    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    /**
     * هل البوابة في وضع الإنتاج؟
     */
    public function isLive(): bool
    {
        return $this->mode === self::MODE_LIVE;
    }

    /**
     * هل البوابة في وضع التجريب؟
     */
    public function isSandbox(): bool
    {
        return $this->mode === self::MODE_SANDBOX;
    }

    /**
     * الحصول على تسمية النوع
     */
    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * الحصول على تسمية الوضع
     */
    public function getModeLabel(): string
    {
        return self::MODES[$this->mode] ?? $this->mode;
    }
}
