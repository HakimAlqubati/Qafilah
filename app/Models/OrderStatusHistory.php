<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id',
        'status',
        'comment',
        'changed_by',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * الحصول على تسمية الحالة
     */
    public function getStatusLabel(): string
    {
        return Order::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * الحصول على اسم المستخدم الذي قام بالتغيير
     */
    public function getChangedByName(): string
    {
        return $this->changedBy?->name ?? 'النظام';
    }

    /**
     * هل التغيير تم بواسطة النظام؟
     */
    public function isSystemChange(): bool
    {
        return $this->changed_by === null;
    }

    /**
     * الحصول على الفترة منذ التغيير
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }
}
