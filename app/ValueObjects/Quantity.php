<?php

namespace App\ValueObjects;

use NumberFormatter;

class Quantity
{
    public function __construct(
        private readonly float $value
    ) {}

    /**
     * دالة المصنع لإنشاء الكائن بسهولة
     */
    public static function make(float|int|string|null $value): string
    {
        $formatted = number_format($value, 2, '.', ',');


        return  $formatted;
    }

    /**
     * تنسيق الكمية
     * @param int $decimals عدد الأرقام العشرية (افتراضي 2)
     * @param bool $removeTrailingZeros إزالة الأصفار الزائدة
     */
    public function format(int $decimals = 2, bool $removeTrailingZeros = true): string
    {
        $formatted = number_format($this->value, $decimals, '.', ',');

        if ($removeTrailingZeros) {
            // إزالة الأصفار الزائدة والنقطة العشرية إذا كانت غير ضرورية
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted;
    }

    /**
     * تنسيق باستخدام NumberFormatter (للغة العربية)
     */
    public function formatLocale(string $locale = 'ar_SA', int $decimals = 2): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return $formatter->format($this->value);
    }

    /**
     * تنسيق كعدد صحيح (بدون أرقام عشرية)
     */
    public function formatWhole(): string
    {
        return number_format($this->value, 0, '.', ',');
    }

    /**
     * تنسيق بوحدة قياس
     * @param string $unit وحدة القياس (مثل: قطعة، كيلو، لتر)
     * @param int $decimals عدد الأرقام العشرية
     */
    public function formatWithUnit(string $unit, int $decimals = 2): string
    {
        return $this->format($decimals) . ' ' . $unit;
    }

    /**
     * الحصول على القيمة
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * الحصول على القيمة كعدد صحيح
     */
    public function getValueAsInt(): int
    {
        return (int) $this->value;
    }

    /**
     * التحقق من أن الكمية موجبة
     */
    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    /**
     * التحقق من أن الكمية صفر
     */
    public function isZero(): bool
    {
        return $this->value == 0;
    }

    /**
     * التحقق من أن الكمية سالبة
     */
    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    /**
     * تحويل إلى string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
