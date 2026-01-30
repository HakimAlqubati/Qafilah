<?php

declare(strict_types=1);

namespace App\Repositories\Reports\Vendors;

use App\DTOs\Reports\Vendors\VendorPerformanceDTO;
use App\DTOs\Reports\Vendors\VendorsFilterDTO;
use App\DTOs\Reports\Vendors\VendorsSummaryDTO;
use Illuminate\Support\Collection;

/**
 * Vendors Report Repository Interface
 * 
 * Defines the contract for all vendor performance report data operations.
 * All implementations must use database-level aggregations for optimal performance.
 */
interface VendorsReportRepositoryInterface
{
    /**
     * Get top performing vendors
     * 
     * @return Collection<int, VendorPerformanceDTO>
     */
    public function getTopVendors(VendorsFilterDTO $filter): Collection;

    /**
     * Get vendors summary statistics
     */
    public function getVendorsSummary(VendorsFilterDTO $filter): VendorsSummaryDTO;

    /**
     * Get specific vendor performance details
     */
    public function getVendorPerformance(int $vendorId, VendorsFilterDTO $filter): ?VendorPerformanceDTO;

    /**
     * Get vendors by category breakdown
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getVendorsByCategory(VendorsFilterDTO $filter): Collection;

    /**
     * Get vendor performance trends over time
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getVendorTrends(int $vendorId, VendorsFilterDTO $filter, string $groupBy = 'daily'): Collection;

    /**
     * Get low performing vendors
     * 
     * @return Collection<int, VendorPerformanceDTO>
     */
    public function getLowPerformingVendors(VendorsFilterDTO $filter): Collection;

    /**
     * Get vendor performance comparison
     * 
     * @return array<string, mixed>
     */
    public function compareVendorPerformance(
        VendorsFilterDTO $currentPeriod,
        VendorsFilterDTO $previousPeriod
    ): array;

    /**
     * Get vendors growth ranking
     * 
     * @return Collection<int, array<string, mixed>>
     */
    public function getVendorsGrowthRanking(VendorsFilterDTO $filter): Collection;
}
