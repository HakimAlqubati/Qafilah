<?php

declare(strict_types=1);

namespace App\Repositories\Reports;

use App\DTOs\Reports\ProductsFilterDTO;
use App\DTOs\Reports\ProductsSummaryDTO;
use App\DTOs\Reports\TopProductDTO;
use Illuminate\Support\Collection;

/**
 * Products Report Repository Interface
 * 
 * Defines the contract for all product report data operations.
 * All implementations must use database-level aggregations for optimal performance.
 */
interface ProductsReportRepositoryInterface
{
    /**
     * Get top selling products
     * 
     * @return Collection<int, TopProductDTO>
     */
    public function getTopProducts(ProductsFilterDTO $filter): Collection;

    /**
     * Get products summary statistics
     */
    public function getProductsSummary(ProductsFilterDTO $filter): ProductsSummaryDTO;

    /**
     * Get products by category breakdown
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getProductsByCategory(ProductsFilterDTO $filter): Collection;

    /**
     * Get products by vendor breakdown
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getProductsByVendor(ProductsFilterDTO $filter): Collection;

    /**
     * Get product sales trends over time
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getProductSalesTrends(ProductsFilterDTO $filter, string $groupBy = 'daily'): Collection;

    /**
     * Get slow moving products (lowest sales)
     * 
     * @return Collection<int, TopProductDTO>
     */
    public function getSlowMovingProducts(ProductsFilterDTO $filter): Collection;

    /**
     * Get product performance comparison
     * 
     * @return array<string, mixed>
     */
    public function compareProductPerformance(
        ProductsFilterDTO $currentPeriod,
        ProductsFilterDTO $previousPeriod
    ): array;
}
