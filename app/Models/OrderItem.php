<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_vendor_sku_id',
        'product_vendor_sku_unit_id',
        'unit_id',
        'product_name',
        'sku',
        'package_size',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total',
        'notes',
    ];

    protected $casts = [
        'package_size' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /* ============================================================
     | 🔗 العلاقات (Relations)
     |============================================================ */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function productVendorSku(): BelongsTo
    {
        return $this->belongsTo(ProductVendorSku::class);
    }

    public function productVendorSkuUnit(): BelongsTo
    {
        return $this->belongsTo(ProductVendorSkuUnit::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /* ============================================================
     | 🧭 Scopes
     |============================================================ */

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    /* ============================================================
     | ⚙️ Helper Methods
     |============================================================ */

    /**
     * حساب الإجمالي
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        return $subtotal - $this->discount + $this->tax;
    }

    /**
     * إعادة حساب وحفظ الإجمالي
     */
    public function recalculateTotal(): void
    {
        $this->total = $this->calculateTotal();
        $this->saveQuietly();
    }

    /**
     * الحصول على السعر للقطعة الواحدة
     */
    public function getPricePerPiece(): float
    {
        if ($this->package_size <= 0) {
            return $this->unit_price;
        }

        return round($this->unit_price / $this->package_size, 2);
    }

    /**
     * الحصول على عدد القطع الإجمالي
     */
    public function getTotalPieces(): int
    {
        return $this->quantity * $this->package_size;
    }

    /**
     * الحصول على اسم المنتج مع اسم المتغير
     */
    public function getFullProductName(): string
    {
        $name = $this->product_name;

        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }

        return $name;
    }

    /**
     * الحصول على اسم الوحدة
     */
    public function getUnitName(): string
    {
        return $this->unit?->name ?? 'قطعة';
    }

    /* ============================================================
     | 🔄 Boot Methods
     |============================================================ */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // حساب الإجمالي تلقائياً
            if (empty($item->total)) {
                $item->total = $item->calculateTotal();
            }

            // نسخ اسم المنتج إذا لم يكن موجوداً
            if (empty($item->product_name) && $item->product_id) {
                $product = Product::find($item->product_id);
                $item->product_name = $product?->name ?? '';
            }
        });

        static::updating(function ($item) {
            // إعادة حساب الإجمالي
            $item->total = $item->calculateTotal();
        });

        // إعادة حساب إجماليات الطلب بعد أي تغيير
        static::saved(function ($item) {
            $item->order?->recalculateTotals();
        });

        static::deleted(function ($item) {
            $item->order?->recalculateTotals();
        });
    }
}
