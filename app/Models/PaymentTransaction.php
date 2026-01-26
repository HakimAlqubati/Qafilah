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
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supportsOtp(): bool
    {
         return $this->gateway?->supportsOtp() ?? false;
    }

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


    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }


    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isReviewing(): bool
    {
        return $this->status === self::STATUS_REVIEWING;
    }


    public function getStatusLabel(): string
    {
        return __('lang.' . $this->status);
    }

    public function markAs(string $status, ?array $gatewayResponse = null): bool
    {
        $data = ['status' => $status];

        if ($gatewayResponse !== null) {
            $data['gateway_response'] = $gatewayResponse;
        }

        return $this->update($data);
    }

    public function markAsPaid(?array $gatewayResponse = null): bool
    {
        return $this->markAs(self::STATUS_PAID, $gatewayResponse);
    }

    public function markAsFailed(?array $gatewayResponse = null): bool
    {
        return $this->markAs(self::STATUS_FAILED, $gatewayResponse);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }

            if (auth()->check() && empty($transaction->created_by)) {
                $transaction->created_by = auth()->id();
            }
        });
    }
}
