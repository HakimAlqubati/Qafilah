<?php

declare(strict_types=1);

namespace App\DTOs\Reports;

/**
 * Data Transfer Object for Sales Summary Results
 * 
 * Immutable value object containing aggregated sales metrics.
 * All calculations are done at the database level for optimal performance.
 */
readonly class SalesSummaryDTO
{
    public function __construct(
        public float $totalRevenue,
        public int $ordersCount,
        public float $averageOrderValue,
        public float $totalTax,
        public float $totalDiscount,
        public float $totalShipping,
        public float $netRevenue,
        public int $itemsCount,
        public int $cancelledOrdersCount = 0,
        public float $cancelledAmount = 0.0,
        public ?string $periodLabel = null,
    ) {}

    /**
     * Create DTO from database query result
     * 
     * @param object|array<string, mixed> $result
     */
    public static function fromQueryResult(object|array $result, ?string $periodLabel = null): self
    {
        $data = is_array($result) ? (object) $result : $result;

        $totalRevenue = (float) ($data->total_revenue ?? 0);
        $totalTax = (float) ($data->total_tax ?? 0);
        $totalDiscount = (float) ($data->total_discount ?? 0);
        $totalShipping = (float) ($data->total_shipping ?? 0);

        return new self(
            totalRevenue: $totalRevenue,
            ordersCount: (int) ($data->orders_count ?? 0),
            averageOrderValue: (float) ($data->average_order_value ?? 0),
            totalTax: $totalTax,
            totalDiscount: $totalDiscount,
            totalShipping: $totalShipping,
            netRevenue: $totalRevenue - $totalTax - $totalShipping + $totalDiscount,
            itemsCount: (int) ($data->items_count ?? 0),
            cancelledOrdersCount: (int) ($data->cancelled_orders_count ?? 0),
            cancelledAmount: (float) ($data->cancelled_amount ?? 0),
            periodLabel: $periodLabel,
        );
    }

    /**
     * Create an empty summary DTO
     */
    public static function empty(?string $periodLabel = null): self
    {
        return new self(
            totalRevenue: 0.0,
            ordersCount: 0,
            averageOrderValue: 0.0,
            totalTax: 0.0,
            totalDiscount: 0.0,
            totalShipping: 0.0,
            netRevenue: 0.0,
            itemsCount: 0,
            cancelledOrdersCount: 0,
            cancelledAmount: 0.0,
            periodLabel: $periodLabel,
        );
    }

    /**
     * Calculate cancellation rate as percentage
     */
    public function getCancellationRate(): float
    {
        if ($this->ordersCount === 0) {
            return 0.0;
        }

        return round(($this->cancelledOrdersCount / $this->ordersCount) * 100, 2);
    }

    /**
     * Calculate success rate as percentage
     */
    public function getSuccessRate(): float
    {
        return 100.0 - $this->getCancellationRate();
    }

    /**
     * Convert DTO to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_revenue' => $this->totalRevenue,
            'orders_count' => $this->ordersCount,
            'average_order_value' => $this->averageOrderValue,
            'total_tax' => $this->totalTax,
            'total_discount' => $this->totalDiscount,
            'total_shipping' => $this->totalShipping,
            'net_revenue' => $this->netRevenue,
            'items_count' => $this->itemsCount,
            'cancelled_orders_count' => $this->cancelledOrdersCount,
            'cancelled_amount' => $this->cancelledAmount,
            'cancellation_rate' => $this->getCancellationRate(),
            'success_rate' => $this->getSuccessRate(),
            'period_label' => $this->periodLabel,
        ];
    }

    /**
     * Format revenue for display
     */
    public function getFormattedRevenue(string $currency = 'SAR'): string
    {
        return number_format($this->totalRevenue, 2) . ' ' . $currency;
    }

    /**
     * Format average order value for display
     */
    public function getFormattedAverageValue(string $currency = 'SAR'): string
    {
        return number_format($this->averageOrderValue, 2) . ' ' . $currency;
    }
}
