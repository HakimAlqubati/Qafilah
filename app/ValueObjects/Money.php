<?php

namespace App\ValueObjects;

use App\Models\Currency;
use NumberFormatter;

class Money
{
    private static ?Currency $defaultCurrency = null;

    public function __construct(
        private readonly float $amount,
        private readonly ?string $currency = null
    ) {}

    /**
     * دالة المصنع لإنشاء الكائن بسهولة
     */
    public static function make(float|int|string|null $amount, ?string $currency = null): self
    {
        return new self((float) ($amount ?? 0), $currency);
    }

    /**
     * الحصول على العملة الافتراضية من النظام
     */
    private static function getDefaultCurrency(): ?Currency
    {
        if (self::$defaultCurrency === null) {
            self::$defaultCurrency = Currency::default()->first();
        }

        return self::$defaultCurrency;
    }

    /**
     * الحصول على رمز العملة
     */
    private function getCurrencyCode(): string
    {
        if ($this->currency) {
            return $this->currency;
        }

        return self::getDefaultCurrency()?->code ?? 'SAR';
    }

    /**
     * الحصول على رمز العملة (symbol)
     */
    private function getCurrencySymbol(): string
    {
        if ($this->currency) {
            // إذا تم تحديد عملة معينة، ابحث عنها
            $currency = Currency::where('code', $this->currency)->first();
            return $currency?->symbol ?? $this->currency;
        }

        return self::getDefaultCurrency()?->symbol ?? '';
    }

    /**
     * تنسيق المبلغ مع العملة
     * @param int $decimals عدد الأرقام العشرية
     * @param bool $useSymbol استخدام الرمز بدلاً من الكود
     */
    public function format(int $decimals = 2, bool $useSymbol = true): string
    {
        $formatted = number_format($this->amount, $decimals, '.', ',');

        if ($useSymbol) {
            $symbol = $this->getCurrencySymbol();
            return trim("{$symbol} {$formatted}");
        }

        $code = $this->getCurrencyCode();
        return "{$code} {$formatted}";
    }

    /**
     * تنسيق باستخدام NumberFormatter (للغة العربية)
     */
    public function formatLocale(string $locale = 'ar_SA'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->amount, $this->getCurrencyCode());
    }

    /**
     * الحصول على المبلغ
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * الحصول على المبلغ كـ integer (بالهللات/الفلوس)
     */
    public function getAmountInCents(): int
    {
        return (int) ($this->amount * 100);
    }

    /**
     * تحويل إلى string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
