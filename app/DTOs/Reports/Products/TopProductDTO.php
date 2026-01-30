<?php

declare(strict_types=1);

namespace App\DTOs\Reports\Products;

use App\ValueObjects\Money;

/**
 * Data Transfer Object for Top Product Report
 * 
 * Immutable value object containing product sales metrics.
 */
readonly class TopProductDTO
{
    public function __construct(
        public int $productId,
        public string $productName,
        public ?string $productSku,
        public ?string $categoryName,
        public ?string $vendorName,
        public int $quantitySold,
        public float $totalRevenue,
        public float $averagePrice,
        public int $ordersCount,
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
            productId: (int) ($data->product_id ?? 0),
            productName: (string) ($data->product_name ?? 'Unknown'),
            productSku: $data->product_sku ?? null,
            categoryName: $data->category_name ?? null,
            vendorName: $data->vendor_name ?? null,
            quantitySold: (int) ($data->quantity_sold ?? 0),
            totalRevenue: (float) ($data->total_revenue ?? 0),
            averagePrice: (float) ($data->average_price ?? 0),
            ordersCount: (int) ($data->orders_count ?? 0),
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
            productId: $this->productId,
            productName: $this->productName,
            productSku: $this->productSku,
            categoryName: $this->categoryName,
            vendorName: $this->vendorName,
            quantitySold: $this->quantitySold,
            totalRevenue: $this->totalRevenue,
            averagePrice: $this->averagePrice,
            ordersCount: $this->ordersCount,
            revenuePercentage: $percentage,
            rank: $rank,
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
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'category_name' => $this->categoryName,
            'vendor_name' => $this->vendorName,
            'quantity_sold' => $this->quantitySold,
            'total_revenue' => (string) Money::make($this->totalRevenue),
            'average_price' => (string) Money::make($this->averagePrice),
            'orders_count' => $this->ordersCount,
            'revenue_percentage' => $this->revenuePercentage . '%',
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
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'category_name' => $this->categoryName,
            'vendor_name' => $this->vendorName,
            'quantity_sold' => $this->quantitySold,
            'total_revenue' => $this->totalRevenue,
            'average_price' => $this->averagePrice,
            'orders_count' => $this->ordersCount,
            'revenue_percentage' => $this->revenuePercentage,
        ];
    }
}
