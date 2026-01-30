<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reports;

use App\DTOs\Reports\SalesFilterDTO;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Reports\SalesReportRequest;
use App\Repositories\Reports\SalesRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * Sales Report API Controller
 * 
 * Handles all sales report related API endpoints.
 */
class SalesReportController extends ApiController
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository
    ) {}

    /**
     * Get sales summary report
     * 
     * @group Reports
     * @authenticated
     */
    public function summary(SalesReportRequest $request): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());
        $summary = $this->salesRepository->getSalesSummary($filter);

        return $this->successResponse(
            data: $summary->toArray(),
            message: __('lang.sales_report_generated')
        );
    }

    /**
     * Get sales by vendor/merchant
     * 
     * @group Reports
     * @authenticated
     */
    public function byVendor(SalesReportRequest $request): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());
        $limit = (int) $request->input('limit', 10);

        $vendors = $this->salesRepository->getSalesByVendor($filter, $limit);

        return $this->successResponse(
            data: [
                'vendors' => $vendors->map(fn($v) => $v->toArray())->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.vendor_sales_report_generated')
        );
    }

    /**
     * Get specific vendor sales summary
     * 
     * @group Reports
     * @authenticated
     */
    public function vendorSummary(SalesReportRequest $request, int $vendorId): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());
        $summary = $this->salesRepository->getVendorSalesSummary($vendorId, $filter);

        return $this->successResponse(
            data: $summary->toArray(),
            message: __('lang.vendor_sales_summary_generated')
        );
    }

    /**
     * Get sales trends over time
     * 
     * @group Reports
     * @authenticated
     */
    public function trends(SalesReportRequest $request): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());
        $groupBy = $request->input('group_by', 'daily');

        // Validate group_by parameter
        if (!in_array($groupBy, ['daily', 'weekly', 'monthly'], true)) {
            return $this->errorResponse(
                message: __('lang.invalid_group_by_parameter'),
                statusCode: 422
            );
        }

        $trends = $this->salesRepository->getSalesTrends($filter, $groupBy);

        return $this->successResponse(
            data: [
                'trends' => $trends->map(fn($t) => $t->toArray())->values()->all(),
                'group_by' => $groupBy,
                'filter' => $filter->toArray(),
            ],
            message: __('lang.sales_trends_generated')
        );
    }

    /**
     * Get top selling products
     * 
     * @group Reports
     * @authenticated
     */
    public function topProducts(SalesReportRequest $request): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());
        $limit = (int) $request->input('limit', 10);

        $products = $this->salesRepository->getTopProducts($filter, $limit);

        return $this->successResponse(
            data: [
                'products' => $products->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.top_products_report_generated')
        );
    }

    /**
     * Compare two time periods
     * 
     * @group Reports
     * @authenticated
     */
    public function compare(SalesReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $currentFilter = SalesFilterDTO::fromArray([
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        $previousFilter = SalesFilterDTO::fromArray([
            'start_date' => $validated['compare_start_date'] ?? null,
            'end_date' => $validated['compare_end_date'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        $comparison = $this->salesRepository->comparePeriods($currentFilter, $previousFilter);

        return $this->successResponse(
            data: [
                'current' => $comparison['current']->toArray(),
                'previous' => $comparison['previous']->toArray(),
                'changes' => [
                    'revenue_change' => $comparison['revenue_change'],
                    'orders_change' => $comparison['orders_change'],
                    'average_change' => $comparison['average_change'],
                ],
            ],
            message: __('lang.period_comparison_generated')
        );
    }

    /**
     * Get customer acquisition metrics
     * 
     * @group Reports
     * @authenticated
     */
    public function customerMetrics(SalesReportRequest $request): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());
        $metrics = $this->salesRepository->getCustomerMetrics($filter);

        return $this->successResponse(
            data: $metrics,
            message: __('lang.customer_metrics_generated')
        );
    }

    /**
     * Get comprehensive dashboard data
     * 
     * @group Reports
     * @authenticated
     */
    public function dashboard(SalesReportRequest $request): JsonResponse
    {
        $filter = SalesFilterDTO::fromArray($request->validated());

        // Get all data in parallel-like manner (optimized queries)
        $summary = $this->salesRepository->getSalesSummary($filter);
        $topVendors = $this->salesRepository->getSalesByVendor($filter, 5);
        $topProducts = $this->salesRepository->getTopProducts($filter, 5);
        $trends = $this->salesRepository->getSalesTrends($filter, 'daily');
        $customerMetrics = $this->salesRepository->getCustomerMetrics($filter);

        return $this->successResponse(
            data: [
                'summary' => $summary->toArray(),
                'top_vendors' => $topVendors->map(fn($v) => $v->toArray())->values()->all(),
                'top_products' => $topProducts->values()->all(),
                'trends' => $trends->map(fn($t) => $t->toArray())->values()->all(),
                'customer_metrics' => $customerMetrics,
                'filter' => $filter->toArray(),
            ],
            message: __('lang.dashboard_data_generated')
        );
    }
}
