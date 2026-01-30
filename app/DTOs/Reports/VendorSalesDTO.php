<?php

declare(strict_types=1);

namespace App\DTOs\Reports;

/**
 * Data Transfer Object for Vendor-specific Sales Data
 * 
 * Immutable value object containing sales metrics for a specific vendor/merchant.
 */
readonly class VendorSalesDTO
{
    public function __construct(
        public int $vendorId,
        public string $vendorName,
        public float $totalRevenue,
        public int $ordersCount,
        public float $averageOrderValue,
        public int $productsCount,
        public int $itemsSold,
        public float $revenuePercentage = 0.0,
        public int $rank = 0,
    ) {}

    /**
     * Create DTO from database query result
     * 
     * @param object|array<string, mixed> $result
     */
    public static function fromQueryResult(object|array $result): self
    {
        $data = is_array($result) ? (object) $result : $result;

        return new self(
            vendorId: (int) ($data->vendor_id ?? 0),
            vendorName: (string) ($data->vendor_name ?? 'Unknown'),
            totalRevenue: (float) ($data->total_revenue ?? 0),
            ordersCount: (int) ($data->orders_count ?? 0),
            averageOrderValue: (float) ($data->average_order_value ?? 0),
            productsCount: (int) ($data->products_count ?? 0),
            itemsSold: (int) ($data->items_sold ?? 0),
            revenuePercentage: (float) ($data->revenue_percentage ?? 0),
            rank: (int) ($data->rank ?? 0),
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
            totalRevenue: $this->totalRevenue,
            ordersCount: $this->ordersCount,
            averageOrderValue: $this->averageOrderValue,
            productsCount: $this->productsCount,
            itemsSold: $this->itemsSold,
            revenuePercentage: $percentage,
            rank: $rank,
        );
    }

    /**
     * Convert DTO to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'vendor_id' => $this->vendorId,
            'vendor_name' => $this->vendorName,
            'total_revenue' => $this->totalRevenue,
            'orders_count' => $this->ordersCount,
            'average_order_value' => $this->averageOrderValue,
            'products_count' => $this->productsCount,
            'items_sold' => $this->itemsSold,
            'revenue_percentage' => $this->revenuePercentage,
            'rank' => $this->rank,
        ];
    }
}
