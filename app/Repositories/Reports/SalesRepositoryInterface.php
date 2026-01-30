<?php

declare(strict_types=1);

namespace App\Repositories\Reports;

use App\DTOs\Reports\SalesFilterDTO;
use App\DTOs\Reports\SalesSummaryDTO;
use App\DTOs\Reports\VendorSalesDTO;
use Illuminate\Support\Collection;

/**
 * Sales Repository Interface
 * 
 * Defines the contract for sales data retrieval and aggregation.
 * All implementations must use database-level aggregations for optimal performance.
 */
interface SalesRepositoryInterface
{
    /**
     * Get aggregated sales summary based on filters
     * 
     * Uses database SUM, COUNT, AVG for maximum performance.
     * Never fetches collections to iterate over them.
     */
    public function getSalesSummary(SalesFilterDTO $filter): SalesSummaryDTO;

    /**
     * Get sales summary grouped by vendor/merchant
     * 
     * @return Collection<int, VendorSalesDTO>
     */
    public function getSalesByVendor(SalesFilterDTO $filter, int $limit = 10): Collection;

    /**
     * Get sales summary for a specific vendor
     */
    public function getVendorSalesSummary(int $vendorId, SalesFilterDTO $filter): SalesSummaryDTO;

    /**
     * Get sales trends over time (daily/weekly/monthly aggregations)
     * 
     * @param 'daily'|'weekly'|'monthly' $groupBy
     * @return Collection<int, SalesSummaryDTO>
     */
    public function getSalesTrends(SalesFilterDTO $filter, string $groupBy = 'daily'): Collection;

    /**
     * Get top performing products by revenue
     * 
     * @return Collection<int, array{
     *     product_id: int,
     *     product_name: string,
     *     quantity_sold: int,
     *     total_revenue: float,
     *     average_price: float
     * }>
     */
    public function getTopProducts(SalesFilterDTO $filter, int $limit = 10): Collection;

    /**
     * Get sales comparison between two periods
     * 
     * @return array{
     *     current: SalesSummaryDTO,
     *     previous: SalesSummaryDTO,
     *     revenue_change: float,
     *     orders_change: float,
     *     average_change: float
     * }
     */
    public function comparePeriods(
        SalesFilterDTO $currentPeriod,
        SalesFilterDTO $previousPeriod
    ): array;

    /**
     * Get customer acquisition and retention metrics
     * 
     * @return array{
     *     new_customers: int,
     *     returning_customers: int,
     *     new_customer_revenue: float,
     *     returning_customer_revenue: float
     * }
     */
    public function getCustomerMetrics(SalesFilterDTO $filter): array;
}
