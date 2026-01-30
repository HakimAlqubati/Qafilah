<?php

declare(strict_types=1);

namespace App\Repositories\Reports\Products;

use App\DTOs\Reports\Products\ProductsFilterDTO;
use App\DTOs\Reports\Products\ProductsSummaryDTO;
use App\DTOs\Reports\Products\TopProductDTO;
use App\Models\Order;
use App\ValueObjects\Money;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Implementation of Products Report Repository
 * 
 * Optimized for performance using database-level aggregations.
 */
class EloquentProductsReportRepository implements ProductsReportRepositoryInterface
{
    /**
     * Excluded statuses for revenue calculations
     */
    private const EXCLUDED_ORDER_STATUSES = [
        Order::STATUS_CANCELLED,
        Order::STATUS_RETURNED,
    ];

    /**
     * @inheritDoc
     */
    public function getTopProducts(ProductsFilterDTO $filter): Collection
    {
        $orderByColumn = $filter->getOrderByColumn();
        $orderDirection = $filter->sortOrder;

        $results = $this->getBaseProductQuery($filter)
            ->orderBy($orderByColumn, $orderDirection)
            ->limit($filter->limit)
            ->get();

        // Calculate total revenue for percentage
        $totalRevenue = $results->sum('total_revenue');

        return $results->map(function (object $row, int $index) use ($totalRevenue): TopProductDTO {
            $dto = TopProductDTO::fromQueryResult($row);
            return $dto->withRankAndPercentage($index + 1, $totalRevenue);
        });
    }

    /**
     * @inheritDoc
     */
    public function getProductsSummary(ProductsFilterDTO $filter): ProductsSummaryDTO
    {
        $result = $this->getBaseQuery($filter)
            ->selectRaw('
                COUNT(DISTINCT order_items.product_id) as total_products,
                COALESCE(SUM(order_items.quantity), 0) as total_quantity_sold,
                COALESCE(SUM(order_items.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as total_orders
            ')
            ->first();

        if ($result === null) {
            return ProductsSummaryDTO::empty($filter->getDateRangeLabel());
        }

        // Get top category
        $topCategory = $this->getBaseQuery($filter)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.name as category_name, SUM(order_items.total) as revenue')
            ->orderByDesc('revenue')
            ->first();

        // Get top vendor
        $topVendor = $this->getBaseQuery($filter)
            ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->groupBy('vendors.id', 'vendors.name')
            ->selectRaw('vendors.name as vendor_name, SUM(order_items.total) as revenue')
            ->orderByDesc('revenue')
            ->first();

        $result->top_category_name = $topCategory?->category_name;
        $result->top_vendor_name = $topVendor?->vendor_name;

        return ProductsSummaryDTO::fromQueryResult($result, $filter->getDateRangeLabel());
    }

    /**
     * @inheritDoc
     */
    public function getProductsByCategory(ProductsFilterDTO $filter): Collection
    {
        return $this->getBaseQuery($filter)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('
                categories.id as category_id,
                categories.name as category_name,
                COUNT(DISTINCT order_items.product_id) as products_count,
                COALESCE(SUM(order_items.quantity), 0) as quantity_sold,
                COALESCE(SUM(order_items.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as orders_count
            ')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn(object $row): array => [
                'category_id' => (int) $row->category_id,
                'category_name' => (string) $row->category_name,
                'products_count' => (int) $row->products_count,
                'quantity_sold' => (int) $row->quantity_sold,
                'total_revenue' => (string) Money::make((float) $row->total_revenue),
                'orders_count' => (int) $row->orders_count,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getProductsByVendor(ProductsFilterDTO $filter): Collection
    {
        return $this->getBaseQuery($filter)
            ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->groupBy('vendors.id', 'vendors.name')
            ->selectRaw('
                vendors.id as vendor_id,
                vendors.name as vendor_name,
                COUNT(DISTINCT order_items.product_id) as products_count,
                COALESCE(SUM(order_items.quantity), 0) as quantity_sold,
                COALESCE(SUM(order_items.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as orders_count
            ')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn(object $row): array => [
                'vendor_id' => (int) $row->vendor_id,
                'vendor_name' => (string) $row->vendor_name,
                'products_count' => (int) $row->products_count,
                'quantity_sold' => (int) $row->quantity_sold,
                'total_revenue' => (string) Money::make((float) $row->total_revenue),
                'orders_count' => (int) $row->orders_count,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getProductSalesTrends(ProductsFilterDTO $filter, string $groupBy = 'daily'): Collection
    {
        $dateFormat = match ($groupBy) {
            'weekly' => "DATE_FORMAT(orders.placed_at, '%Y-%u')",
            'monthly' => "DATE_FORMAT(orders.placed_at, '%Y-%m')",
            default => 'DATE(orders.placed_at)',
        };

        $labelFormat = match ($groupBy) {
            'weekly' => "CONCAT('Week ', WEEK(orders.placed_at), ' - ', YEAR(orders.placed_at))",
            'monthly' => "DATE_FORMAT(orders.placed_at, '%Y-%m')",
            default => 'DATE(orders.placed_at)',
        };

        return $this->getBaseQuery($filter)
            ->groupByRaw($dateFormat)
            ->selectRaw("
                {$labelFormat} as period_label,
                {$dateFormat} as period_key,
                COUNT(DISTINCT order_items.product_id) as products_count,
                COALESCE(SUM(order_items.quantity), 0) as quantity_sold,
                COALESCE(SUM(order_items.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as orders_count
            ")
            ->orderByRaw("{$dateFormat} ASC")
            ->get()
            ->map(fn(object $row): array => [
                'period_label' => (string) $row->period_label,
                'products_count' => (int) $row->products_count,
                'quantity_sold' => (int) $row->quantity_sold,
                'total_revenue' => (string) Money::make((float) $row->total_revenue),
                'orders_count' => (int) $row->orders_count,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getSlowMovingProducts(ProductsFilterDTO $filter): Collection
    {
        // Override sort to get lowest selling products
        $results = $this->getBaseProductQuery($filter)
            ->orderBy('total_revenue', 'asc')
            ->limit($filter->limit)
            ->get();

        $totalRevenue = $results->sum('total_revenue');

        return $results->map(function (object $row, int $index) use ($totalRevenue): TopProductDTO {
            $dto = TopProductDTO::fromQueryResult($row);
            return $dto->withRankAndPercentage($index + 1, $totalRevenue);
        });
    }

    /**
     * @inheritDoc
     */
    public function compareProductPerformance(
        ProductsFilterDTO $currentPeriod,
        ProductsFilterDTO $previousPeriod
    ): array {
        $current = $this->getProductsSummary($currentPeriod);
        $previous = $this->getProductsSummary($previousPeriod);

        return [
            'current' => $current->toArray(),
            'previous' => $previous->toArray(),
            'changes' => [
                'products_change' => $this->calculatePercentageChange(
                    (float) $previous->totalProducts,
                    (float) $current->totalProducts
                ) . '%',
                'quantity_change' => $this->calculatePercentageChange(
                    (float) $previous->totalQuantitySold,
                    (float) $current->totalQuantitySold
                ) . '%',
                'revenue_change' => $this->calculatePercentageChange(
                    $previous->totalRevenue,
                    $current->totalRevenue
                ) . '%',
                'orders_change' => $this->calculatePercentageChange(
                    (float) $previous->totalOrders,
                    (float) $current->totalOrders
                ) . '%',
            ],
        ];
    }

    /**
     * Build base query for product reports
     */
    private function getBaseQuery(ProductsFilterDTO $filter): Builder
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($filter->startDate, fn(Builder $q) => $q->where('orders.placed_at', '>=', $filter->startDate->toDateTimeString()))
            ->when($filter->endDate, fn(Builder $q) => $q->where('orders.placed_at', '<=', $filter->endDate->toDateTimeString()))
            ->when(
                $filter->orderStatus,
                fn(Builder $q) => $q->where('orders.status', $filter->orderStatus),
                fn(Builder $q) => $q->whereNotIn('orders.status', self::EXCLUDED_ORDER_STATUSES)
            )
            ->when($filter->vendorId, fn(Builder $q) => $q->where('orders.vendor_id', $filter->vendorId))
            ->when($filter->categoryId, fn(Builder $q) => $q->where('products.category_id', $filter->categoryId));
    }

    /**
     * Build base product query with full product details
     */
    private function getBaseProductQuery(ProductsFilterDTO $filter): Builder
    {
        return $this->getBaseQuery($filter)
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->groupBy('order_items.product_id', 'products.name',  'categories.name', 'vendors.name')
            ->selectRaw('
                order_items.product_id,
                products.name as product_name,
                categories.name as category_name,
                vendors.name as vendor_name,
                COALESCE(SUM(order_items.quantity), 0) as quantity_sold,
                COALESCE(SUM(order_items.total), 0) as total_revenue,
                COALESCE(AVG(order_items.unit_price), 0) as average_price,
                COUNT(DISTINCT orders.id) as orders_count
            ');
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue === 0.0) {
            return $newValue > 0 ? 100.0 : 0.0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }
}
