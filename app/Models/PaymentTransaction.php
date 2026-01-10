<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'gateway_id',
        'payable_type',
        'payable_id',
        'user_id',
        'amount',
        'currency',
        'reference_id',
        'proof_image',
        'status',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    /* ============================================================
     | 🔹 Constants - حالات المعاملة
     |============================================================ */

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_REVIEWING = 'reviewing';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_FAILED,
        self::STATUS_REFUNDED,
        self::STATUS_REVIEWING,
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', self::STATUS_REVIEWING);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForGateway($query, int $gatewayId)
    {
        return $query->where('gateway_id', $gatewayId);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * هل المعاملة في انتظار الدفع؟
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * هل تم الدفع؟
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * هل فشلت المعاملة؟
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * هل تم الاسترداد؟
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * هل المعاملة قيد المراجعة؟
     */
    public function isReviewing(): bool
    {
        return $this->status === self::STATUS_REVIEWING;
    }

    /**
     * الحصول على تسمية الحالة
     */
    public function getStatusLabel(): string
    {
        return __('lang.' . $this->status);
    }

    /**
     * تحديث حالة المعاملة
     */
    public function markAs(string $status, ?array $gatewayResponse = null): bool
    {
        $data = ['status' => $status];

        if ($gatewayResponse !== null) {
            $data['gateway_response'] = $gatewayResponse;
        }

        return $this->update($data);
    }

    /**
     * تحديد كمدفوعة
     */
    public function markAsPaid(?array $gatewayResponse = null): bool
    {
        return $this->markAs(self::STATUS_PAID, $gatewayResponse);
    }

    /**
     * تحديد كفاشلة
     */
    public function markAsFailed(?array $gatewayResponse = null): bool
    {
        return $this->markAs(self::STATUS_FAILED, $gatewayResponse);
    }

    /* ============================================================
     | 🔄 Boot Methods
     |============================================================ */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
        });
    }
}
