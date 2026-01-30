<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reports;

use App\DTOs\Reports\Products\ProductsFilterDTO;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Reports\ProductsReportRequest;
use App\Repositories\Reports\Products\ProductsReportRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * Products Report API Controller
 * 
 * Handles all product report related API endpoints.
 * Controller only passes and returns data - no formatting logic here.
 */
class ProductsReportController extends ApiController
{
    public function __construct(
        private readonly ProductsReportRepositoryInterface $productsRepository
    ) {}

    /**
     * Get top selling products
     * 
     * GET /api/v1/reports/products/top
     */
    public function topProducts(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());
        $products = $this->productsRepository->getTopProducts($filter);

        return $this->successResponse(
            data: [
                'products' => $products->map(fn($p) => $p->toArray())->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.top_products_report_generated')
        );
    }

    /**
     * Get products summary statistics
     * 
     * GET /api/v1/reports/products/summary
     */
    public function summary(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());
        $summary = $this->productsRepository->getProductsSummary($filter);

        return $this->successResponse(
            data: $summary->toArray(),
            message: __('lang.products_summary_generated')
        );
    }

    /**
     * Get products breakdown by category
     * 
     * GET /api/v1/reports/products/by-category
     */
    public function byCategory(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());
        $categories = $this->productsRepository->getProductsByCategory($filter);

        return $this->successResponse(
            data: [
                'categories' => $categories->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.products_by_category_generated')
        );
    }

    /**
     * Get products breakdown by vendor
     * 
     * GET /api/v1/reports/products/by-vendor
     */
    public function byVendor(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());
        $vendors = $this->productsRepository->getProductsByVendor($filter);

        return $this->successResponse(
            data: [
                'vendors' => $vendors->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.products_by_vendor_generated')
        );
    }

    /**
     * Get product sales trends over time
     * 
     * GET /api/v1/reports/products/trends
     */
    public function trends(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());
        $groupBy = $request->input('group_by', 'daily');

        if (!in_array($groupBy, ['daily', 'weekly', 'monthly'], true)) {
            return $this->errorResponse(
                message: __('lang.invalid_group_by_parameter'),
                statusCode: 422
            );
        }

        $trends = $this->productsRepository->getProductSalesTrends($filter, $groupBy);

        return $this->successResponse(
            data: [
                'trends' => $trends->values()->all(),
                'group_by' => $groupBy,
                'filter' => $filter->toArray(),
            ],
            message: __('lang.products_trends_generated')
        );
    }

    /**
     * Get slow moving products (lowest sales)
     * 
     * GET /api/v1/reports/products/slow-moving
     */
    public function slowMoving(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());
        $products = $this->productsRepository->getSlowMovingProducts($filter);

        return $this->successResponse(
            data: [
                'products' => $products->map(fn($p) => $p->toArray())->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.slow_moving_products_generated')
        );
    }

    /**
     * Compare product performance between two periods
     * 
     * GET /api/v1/reports/products/compare
     */
    public function compare(ProductsReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $currentFilter = ProductsFilterDTO::fromArray([
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'order_status' => $validated['order_status'] ?? null,
        ]);

        $previousFilter = ProductsFilterDTO::fromArray([
            'start_date' => $validated['compare_start_date'] ?? null,
            'end_date' => $validated['compare_end_date'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'order_status' => $validated['order_status'] ?? null,
        ]);

        $comparison = $this->productsRepository->compareProductPerformance($currentFilter, $previousFilter);

        return $this->successResponse(
            data: $comparison,
            message: __('lang.products_comparison_generated')
        );
    }

    /**
     * Get comprehensive products dashboard
     * 
     * GET /api/v1/reports/products/dashboard
     */
    public function dashboard(ProductsReportRequest $request): JsonResponse
    {
        $filter = ProductsFilterDTO::fromArray($request->validated());

        $summary = $this->productsRepository->getProductsSummary($filter);
        $topProducts = $this->productsRepository->getTopProducts($filter);
        $byCategory = $this->productsRepository->getProductsByCategory($filter);
        $byVendor = $this->productsRepository->getProductsByVendor($filter);
        $trends = $this->productsRepository->getProductSalesTrends($filter, 'daily');

        return $this->successResponse(
            data: [
                'summary' => $summary->toArray(),
                'top_products' => $topProducts->map(fn($p) => $p->toArray())->values()->all(),
                'by_category' => $byCategory->values()->all(),
                'by_vendor' => $byVendor->values()->all(),
                'trends' => $trends->values()->all(),
                'filter' => $filter->toArray(),
            ],
            message: __('lang.products_dashboard_generated')
        );
    }
}
