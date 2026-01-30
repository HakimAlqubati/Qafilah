<?php

declare(strict_types=1);

namespace App\DTOs\Reports;

use App\ValueObjects\Money;

/**
 * Data Transfer Object for Products Report Summary
 * 
 * Immutable value object containing aggregated product metrics.
 */
readonly class ProductsSummaryDTO
{
    public function __construct(
        public int $totalProducts,
        public int $totalQuantitySold,
        public float $totalRevenue,
        public float $averageRevenuePerProduct,
        public int $totalOrders,
        public float $averageQuantityPerOrder,
        public ?string $topCategoryName = null,
        public ?string $topVendorName = null,
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

        $totalProducts = (int) ($data->total_products ?? 0);
        $totalRevenue = (float) ($data->total_revenue ?? 0);
        $totalQuantity = (int) ($data->total_quantity_sold ?? 0);
        $totalOrders = (int) ($data->total_orders ?? 0);

        return new self(
            totalProducts: $totalProducts,
            totalQuantitySold: $totalQuantity,
            totalRevenue: $totalRevenue,
            averageRevenuePerProduct: $totalProducts > 0 ? round($totalRevenue / $totalProducts, 2) : 0,
            totalOrders: $totalOrders,
            averageQuantityPerOrder: $totalOrders > 0 ? round($totalQuantity / $totalOrders, 2) : 0,
            topCategoryName: $data->top_category_name ?? null,
            topVendorName: $data->top_vendor_name ?? null,
            periodLabel: $periodLabel,
        );
    }

    /**
     * Create an empty summary DTO
     */
    public static function empty(?string $periodLabel = null): self
    {
        return new self(
            totalProducts: 0,
            totalQuantitySold: 0,
            totalRevenue: 0.0,
            averageRevenuePerProduct: 0.0,
            totalOrders: 0,
            averageQuantityPerOrder: 0.0,
            periodLabel: $periodLabel,
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
            'total_products' => $this->totalProducts,
            'total_quantity_sold' => $this->totalQuantitySold,
            'total_revenue' => (string) Money::make($this->totalRevenue),
            'average_revenue_per_product' => (string) Money::make($this->averageRevenuePerProduct),
            'total_orders' => $this->totalOrders,
            'average_quantity_per_order' => $this->averageQuantityPerOrder,
            'top_category_name' => $this->topCategoryName,
            'top_vendor_name' => $this->topVendorName,
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
            'total_products' => $this->totalProducts,
            'total_quantity_sold' => $this->totalQuantitySold,
            'total_revenue' => $this->totalRevenue,
            'average_revenue_per_product' => $this->averageRevenuePerProduct,
            'total_orders' => $this->totalOrders,
            'average_quantity_per_order' => $this->averageQuantityPerOrder,
            'top_category_name' => $this->topCategoryName,
            'top_vendor_name' => $this->topVendorName,
            'period_label' => $this->periodLabel,
        ];
    }
}
