<?php

declare(strict_types=1);

namespace App\DTOs\Reports\Vendors;

use App\ValueObjects\Money;

/**
 * Data Transfer Object for Vendor Performance
 * 
 * Immutable value object containing vendor performance metrics.
 */
readonly class VendorPerformanceDTO
{
    public function __construct(
        public int $vendorId,
        public string $vendorName,
        public ?string $vendorEmail,
        public ?string $vendorPhone,
        public float $totalRevenue,
        public int $ordersCount,
        public float $averageOrderValue,
        public int $productsCount,
        public int $itemsSold,
        public int $completedOrders,
        public int $cancelledOrders,
        public float $completionRate,
        public float $cancellationRate,
        public float $revenuePercentage = 0.0,
        public int $rank = 0,
        public ?string $periodLabel = null,
    ) {}

    /**
     * Create DTO from database query result
     * 
     * @param object|array<string, mixed> $result
     */
    public static function fromQueryResult(object|array $result): self
    {
        $data = is_array($result) ? (object) $result : $result;

        $ordersCount = (int) ($data->orders_count ?? 0);
        $completedOrders = (int) ($data->completed_orders ?? 0);
        $cancelledOrders = (int) ($data->cancelled_orders ?? 0);

        $completionRate = $ordersCount > 0
            ? round(($completedOrders / $ordersCount) * 100, 2)
            : 0.0;

        $cancellationRate = $ordersCount > 0
            ? round(($cancelledOrders / $ordersCount) * 100, 2)
            : 0.0;

        return new self(
            vendorId: (int) ($data->vendor_id ?? 0),
            vendorName: (string) ($data->vendor_name ?? 'Unknown'),
            vendorEmail: $data->vendor_email ?? null,
            vendorPhone: $data->vendor_phone ?? null,
            totalRevenue: (float) ($data->total_revenue ?? 0),
            ordersCount: $ordersCount,
            averageOrderValue: (float) ($data->average_order_value ?? 0),
            productsCount: (int) ($data->products_count ?? 0),
            itemsSold: (int) ($data->items_sold ?? 0),
            completedOrders: $completedOrders,
            cancelledOrders: $cancelledOrders,
            completionRate: $completionRate,
            cancellationRate: $cancellationRate,
            revenuePercentage: (float) ($data->revenue_percentage ?? 0),
            rank: (int) ($data->rank ?? 0),
            periodLabel: $data->period_label ?? null,
        );
    }

    /**
     * Create a new instance with calculated rank and percentage
     */
    public function withRankAndPercentage(int $rank, float $totalSystemRevenue): self
    {
        $percentage = $totalSystemRevenue > 0
            ? round(($this->totalRevenue / $totalSystemRevenue) * 100, 2)
            : 0.0;

        return new self(
            vendorId: $this->vendorId,
            vendorName: $this->vendorName,
            vendorEmail: $this->vendorEmail,
            vendorPhone: $this->vendorPhone,
            totalRevenue: $this->totalRevenue,
            ordersCount: $this->ordersCount,
            averageOrderValue: $this->averageOrderValue,
            productsCount: $this->productsCount,
            itemsSold: $this->itemsSold,
            completedOrders: $this->completedOrders,
            cancelledOrders: $this->cancelledOrders,
            completionRate: $this->completionRate,
            cancellationRate: $this->cancellationRate,
            revenuePercentage: $percentage,
            rank: $rank,
            periodLabel: $this->periodLabel,
        );
    }

    /**
     * Convert DTO to array representation with formatted money values
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rank' => $this->rank,
            'vendor_id' => $this->vendorId,
            'vendor_name' => $this->vendorName,
            'vendor_email' => $this->vendorEmail,
            'vendor_phone' => $this->vendorPhone,
            'total_revenue' => (string) Money::make($this->totalRevenue),
            'orders_count' => $this->ordersCount,
            'average_order_value' => (string) Money::make($this->averageOrderValue),
            'products_count' => $this->productsCount,
            'items_sold' => $this->itemsSold,
            'completed_orders' => $this->completedOrders,
            'cancelled_orders' => $this->cancelledOrders,
            'completion_rate' => $this->completionRate . '%',
            'cancellation_rate' => $this->cancellationRate . '%',
            'revenue_percentage' => $this->revenuePercentage . '%',
            'period_label' => $this->periodLabel,
        ];
    }

    /**
     * Convert DTO to array with raw numeric values
     * 
     * @return array<string, mixed>
     */
    public function toRawArray(): array
    {
        return [
            'rank' => $this->rank,
            'vendor_id' => $this->vendorId,
            'vendor_name' => $this->vendorName,
            'vendor_email' => $this->vendorEmail,
            'vendor_phone' => $this->vendorPhone,
            'total_revenue' => $this->totalRevenue,
            'orders_count' => $this->ordersCount,
            'average_order_value' => $this->averageOrderValue,
            'products_count' => $this->productsCount,
            'items_sold' => $this->itemsSold,
            'completed_orders' => $this->completedOrders,
            'cancelled_orders' => $this->cancelledOrders,
            'completion_rate' => $this->completionRate,
            'cancellation_rate' => $this->cancellationRate,
            'revenue_percentage' => $this->revenuePercentage,
            'period_label' => $this->periodLabel,
        ];
    }

    /**
     * Get performance grade based on metrics
     */
    public function getPerformanceGrade(): string
    {
        $score = 0;

        // Completion rate factor (60%)
        $score += ($this->completionRate / 100) * 60;

        // Cancellation penalty (40%)
        $score += ((100 - $this->cancellationRate) / 100) * 40;

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }
}
