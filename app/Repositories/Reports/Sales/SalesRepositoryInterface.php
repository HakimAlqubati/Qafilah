<?php

declare(strict_types=1);

namespace App\Repositories\Reports\Sales;

use App\DTOs\Reports\Sales\SalesFilterDTO;
use App\DTOs\Reports\Sales\SalesSummaryDTO;
use App\DTOs\Reports\Sales\VendorSalesDTO;
use Illuminate\Support\Collection;

/**
 * Sales Repository Interface
 * 
 * Defines the contract for all sales report data operations.
 * All implementations must use database-level aggregations for optimal performance.
 */
interface SalesRepositoryInterface
{
    /**
     * Get aggregated sales summary for the given filter criteria
     */
    public function getSalesSummary(SalesFilterDTO $filter): SalesSummaryDTO;

    /**
     * Get sales breakdown by vendor/merchant
     * 
     * @return Collection<int, VendorSalesDTO>
     */
    public function getSalesByVendor(SalesFilterDTO $filter, int $limit = 10): Collection;

    /**
     * Get detailed sales summary for a specific vendor
     */
    public function getVendorSalesSummary(int $vendorId, SalesFilterDTO $filter): SalesSummaryDTO;

    /**
     * Get sales trends over time (daily, weekly, monthly)
     * 
     * @return Collection<int, SalesSummaryDTO>
     */
    public function getSalesTrends(SalesFilterDTO $filter, string $groupBy = 'daily'): Collection;

    /**
     * Get top selling products
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getTopProducts(SalesFilterDTO $filter, int $limit = 10): Collection;

    /**
     * Compare sales between two time periods
     * 
     * @return array<string, mixed>
     */
    public function comparePeriods(SalesFilterDTO $currentPeriod, SalesFilterDTO $previousPeriod): array;

    /**
     * Get customer metrics (new vs returning)
     * 
     * @return array<string, mixed>
     */
    public function getCustomerMetrics(SalesFilterDTO $filter): array;
}
