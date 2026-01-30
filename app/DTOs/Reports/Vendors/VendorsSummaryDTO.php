<?php

declare(strict_types=1);

namespace App\DTOs\Reports\Vendors;

use App\ValueObjects\Money;

/**
 * Data Transfer Object for Vendors Summary Report
 * 
 * Immutable value object containing aggregated vendor metrics.
 */
readonly class VendorsSummaryDTO
{
    public function __construct(
        public int $totalVendors,
        public float $totalRevenue,
        public int $totalOrders,
        public float $averageRevenuePerVendor,
        public float $averageOrdersPerVendor,
        public int $totalProducts,
        public int $totalItemsSold,
        public ?string $topVendorName = null,
        public ?float $topVendorRevenue = null,
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

        $totalVendors = (int) ($data->total_vendors ?? 0);
        $totalRevenue = (float) ($data->total_revenue ?? 0);
        $totalOrders = (int) ($data->total_orders ?? 0);

        return new self(
            totalVendors: $totalVendors,
            totalRevenue: $totalRevenue,
            totalOrders: $totalOrders,
            averageRevenuePerVendor: $totalVendors > 0 ? round($totalRevenue / $totalVendors, 2) : 0,
            averageOrdersPerVendor: $totalVendors > 0 ? round($totalOrders / $totalVendors, 2) : 0,
            totalProducts: (int) ($data->total_products ?? 0),
            totalItemsSold: (int) ($data->total_items_sold ?? 0),
            topVendorName: $data->top_vendor_name ?? null,
            topVendorRevenue: isset($data->top_vendor_revenue) ? (float) $data->top_vendor_revenue : null,
            periodLabel: $periodLabel,
        );
    }

    /**
     * Create an empty summary DTO
     */
    public static function empty(?string $periodLabel = null): self
    {
        return new self(
            totalVendors: 0,
            totalRevenue: 0.0,
            totalOrders: 0,
            averageRevenuePerVendor: 0.0,
            averageOrdersPerVendor: 0.0,
            totalProducts: 0,
            totalItemsSold: 0,
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
            'total_vendors' => $this->totalVendors,
            'total_revenue' => (string) Money::make($this->totalRevenue),
            'total_orders' => $this->totalOrders,
            'average_revenue_per_vendor' => (string) Money::make($this->averageRevenuePerVendor),
            'average_orders_per_vendor' => $this->averageOrdersPerVendor,
            'total_products' => $this->totalProducts,
            'total_items_sold' => $this->totalItemsSold,
            'top_vendor_name' => $this->topVendorName,
            'top_vendor_revenue' => $this->topVendorRevenue !== null
                ? (string) Money::make($this->topVendorRevenue)
                : null,
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
            'total_vendors' => $this->totalVendors,
            'total_revenue' => $this->totalRevenue,
            'total_orders' => $this->totalOrders,
            'average_revenue_per_vendor' => $this->averageRevenuePerVendor,
            'average_orders_per_vendor' => $this->averageOrdersPerVendor,
            'total_products' => $this->totalProducts,
            'total_items_sold' => $this->totalItemsSold,
            'top_vendor_name' => $this->topVendorName,
            'top_vendor_revenue' => $this->topVendorRevenue,
            'period_label' => $this->periodLabel,
        ];
    }
}
