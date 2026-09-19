<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_id',
        'currency_id',
        'status',
        'payment_status',
        'shipping_status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total',
        'shipping_address_id',
        'billing_address_id',
        'notes',
        'internal_notes',
        'placed_at',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'placed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /* ============================================================
     | 🔹 Constants - حالات الطلب
     |============================================================ */

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED = 'returned';

    public const STATUSES = [
        self::STATUS_PENDING => 'قيد الانتظار',
        self::STATUS_CONFIRMED => 'تم التأكيد',
        self::STATUS_PROCESSING => 'قيد المعالجة',
        self::STATUS_SHIPPED => 'تم الشحن',
        self::STATUS_DELIVERED => 'تم التوصيل',
        self::STATUS_COMPLETED => 'مكتمل',
        self::STATUS_CANCELLED => 'ملغي',
        self::STATUS_RETURNED => 'مرتجع',
    ];

    /* ============================================================
     | 🔹 Constants - حالات الدفع
     |============================================================ */

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_PENDING => 'في انتظار الدفع',
        self::PAYMENT_PARTIAL => 'دفع جزئي',
        self::PAYMENT_PAID => 'مدفوع',
        self::PAYMENT_REFUNDED => 'تم الاسترداد',
    ];

    /* ============================================================
     | 🔹 Constants - حالات الشحن
     |============================================================ */

    public const SHIPPING_PENDING = 'pending';
    public const SHIPPING_PREPARING = 'preparing';
    public const SHIPPING_SHIPPED = 'shipped';
    public const SHIPPING_IN_TRANSIT = 'in_transit';
    public const SHIPPING_DELIVERED = 'delivered';

    public const SHIPPING_STATUSES = [
        self::SHIPPING_PENDING => 'في انتظار الشحن',
        self::SHIPPING_PREPARING => 'قيد التجهيز',
        self::SHIPPING_SHIPPED => 'تم الشحن',
        self::SHIPPING_IN_TRANSIT => 'في الطريق',
        self::SHIPPING_DELIVERED => 'تم التوصيل',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'billing_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PENDING);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * توليد رقم طلب جديد
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest('id')->first();
        $sequence = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * هل الطلب قابل للإلغاء؟
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    /**
     * هل الطلب قابل للتعديل؟
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    /**
     * هل تم تأكيد الطلب؟
     */
    public function isConfirmed(): bool
    {
        return $this->status !== self::STATUS_PENDING;
    }

    /**
     * هل الطلب مكتمل؟
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * هل الطلب ملغي؟
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * هل الطلب مدفوع؟
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * الحصول على تسمية الحالة
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * الحصول على تسمية حالة الدفع
     */
    public function getPaymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * الحصول على تسمية حالة الشحن
     */
    public function getShippingStatusLabel(): string
    {
        return self::SHIPPING_STATUSES[$this->shipping_status] ?? $this->shipping_status;
    }

    /**
     * حساب عدد البنود
     */
    public function getItemsCount(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * إعادة حساب الإجماليات
     */
    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total');
        $this->total = $this->subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
        $this->saveQuietly();
    }

    /**
     * تغيير حالة الطلب مع التسجيل
     */
    public function changeStatus(string $status, ?string $comment = null, ?int $userId = null): void
    {
        $this->update(['status' => $status]);

        $this->statusHistory()->create([
            'status' => $status,
            'comment' => $comment,
            'changed_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * تأكيد الطلب
     */
    public function confirm(?string $comment = null): void
    {
        $this->changeStatus(self::STATUS_CONFIRMED, $comment);
        $this->update(['confirmed_at' => now()]);
    }

    /**
     * إلغاء الطلب
     */
    public function cancel(?string $reason = null): void
    {
        if (!$this->isCancellable()) {
            throw new \Exception('لا يمكن إلغاء هذا الطلب');
        }

        $this->changeStatus(self::STATUS_CANCELLED, $reason);
    }

    /* ============================================================
     | 🔄 Boot Methods
     |============================================================ */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
            if (empty($order->placed_at)) {
                $order->placed_at = now();
            }
        });

        static::created(function ($order) {
            if ($order->status === self::STATUS_COMPLETED) {
                app(\App\Services\LoyaltyService::class)->awardPointsForOrder($order);
            }
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                $loyaltyService = app(\App\Services\LoyaltyService::class);

                if ($order->status === self::STATUS_COMPLETED) {
                    $loyaltyService->awardPointsForOrder($order);
                } elseif (in_array($order->status, [self::STATUS_CANCELLED, self::STATUS_RETURNED])) {
                    $loyaltyService->handleOrderRefundOrCancellation($order);
                }
            }
        });
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function paymentTransactions()
    {
        return   $this->morphMany(PaymentTransaction::class, 'payable');
    }
}
