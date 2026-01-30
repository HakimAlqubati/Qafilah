<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reports;

use App\DTOs\Reports\Vendors\VendorsFilterDTO;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Reports\VendorsReportRequest;
use App\Repositories\Reports\Vendors\VendorsReportRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * Vendors Report API Controller
 * 
 * Handles all vendor performance report related API endpoints.
 * تقرير أداء الموردين
 */
class VendorsReportController extends ApiController
{
    public function __construct(
        private readonly VendorsReportRepositoryInterface $vendorsRepository
    ) {}

    /**
     * Get top performing vendors
     * 
     * GET /api/v1/reports/vendors/top
     */
    public function topVendors(VendorsReportRequest $request): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $vendors = $this->vendorsRepository->getTopVendors($filter);

        return $this->successResponse(
            data: [
                'vendors' => $vendors->map(fn($v) => $v->toArray())->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.top_vendors_report_generated')
        );
    }

    /**
     * Get vendors summary statistics
     * 
     * GET /api/v1/reports/vendors/summary
     */
    public function summary(VendorsReportRequest $request): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $summary = $this->vendorsRepository->getVendorsSummary($filter);

        return $this->successResponse(
            data: $summary->toArray(),
            message: __('lang.vendors_summary_generated')
        );
    }

    /**
     * Get specific vendor performance
     * 
     * GET /api/v1/reports/vendors/{vendorId}
     */
    public function vendorPerformance(VendorsReportRequest $request, int $vendorId): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $performance = $this->vendorsRepository->getVendorPerformance($vendorId, $filter);

        if ($performance === null) {
            return $this->errorResponse(
                message: __('lang.vendor_not_found'),
                statusCode: 404
            );
        }

        return $this->successResponse(
            data: [
                'vendor' => $performance->toArray(),
                'performance_grade' => $performance->getPerformanceGrade(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.vendor_performance_generated')
        );
    }

    /**
     * Get vendors breakdown by category
     * 
     * GET /api/v1/reports/vendors/by-category
     */
    public function byCategory(VendorsReportRequest $request): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $categories = $this->vendorsRepository->getVendorsByCategory($filter);

        return $this->successResponse(
            data: [
                'categories' => $categories->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.vendors_by_category_generated')
        );
    }

    /**
     * Get specific vendor trends over time
     * 
     * GET /api/v1/reports/vendors/{vendorId}/trends
     */
    public function vendorTrends(VendorsReportRequest $request, int $vendorId): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $groupBy = $request->input('group_by', 'daily');

        if (!in_array($groupBy, ['daily', 'weekly', 'monthly'], true)) {
            return $this->errorResponse(
                message: __('lang.invalid_group_by_parameter'),
                statusCode: 422
            );
        }

        $trends = $this->vendorsRepository->getVendorTrends($vendorId, $filter, $groupBy);

        return $this->successResponse(
            data: [
                'vendor_id' => $vendorId,
                'trends' => $trends->values()->all(),
                'group_by' => $groupBy,
                'filter' => $filter->toArray(),
            ],
            message: __('lang.vendor_trends_generated')
        );
    }

    /**
     * Get low performing vendors
     * 
     * GET /api/v1/reports/vendors/low-performing
     */
    public function lowPerforming(VendorsReportRequest $request): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $vendors = $this->vendorsRepository->getLowPerformingVendors($filter);

        return $this->successResponse(
            data: [
                'vendors' => $vendors->map(fn($v) => $v->toArray())->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.low_performing_vendors_generated')
        );
    }

    /**
     * Compare vendor performance between two periods
     * 
     * GET /api/v1/reports/vendors/compare
     */
    public function compare(VendorsReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $currentFilter = VendorsFilterDTO::fromArray([
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'order_status' => $validated['order_status'] ?? null,
        ]);

        $previousFilter = VendorsFilterDTO::fromArray([
            'start_date' => $validated['compare_start_date'] ?? null,
            'end_date' => $validated['compare_end_date'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'order_status' => $validated['order_status'] ?? null,
        ]);

        $comparison = $this->vendorsRepository->compareVendorPerformance($currentFilter, $previousFilter);

        return $this->successResponse(
            data: $comparison,
            message: __('lang.vendors_comparison_generated')
        );
    }

    /**
     * Get vendors growth ranking
     * 
     * GET /api/v1/reports/vendors/growth
     */
    public function growthRanking(VendorsReportRequest $request): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());
        $growth = $this->vendorsRepository->getVendorsGrowthRanking($filter);

        return $this->successResponse(
            data: [
                'vendors' => $growth->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.vendors_growth_generated')
        );
    }

    /**
     * Get comprehensive vendors dashboard
     * 
     * GET /api/v1/reports/vendors/dashboard
     */
    public function dashboard(VendorsReportRequest $request): JsonResponse
    {
        $filter = VendorsFilterDTO::fromArray($request->validated());

        $summary = $this->vendorsRepository->getVendorsSummary($filter);
        $topVendors = $this->vendorsRepository->getTopVendors($filter);
        $byCategory = $this->vendorsRepository->getVendorsByCategory($filter);
        $lowPerforming = $this->vendorsRepository->getLowPerformingVendors($filter);

        return $this->successResponse(
            data: [
                'summary' => $summary->toArray(),
                'top_vendors' => $topVendors->map(fn($v) => $v->toArray())->values()->all(),
                'by_category' => $byCategory->values()->all(),
                'low_performing' => $lowPerforming->map(fn($v) => $v->toArray())->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.vendors_dashboard_generated')
        );
    }
}
