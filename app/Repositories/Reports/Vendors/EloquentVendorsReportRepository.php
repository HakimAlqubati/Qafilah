<?php

declare(strict_types=1);

namespace App\Repositories\Reports\Vendors;

use App\DTOs\Reports\Vendors\VendorPerformanceDTO;
use App\DTOs\Reports\Vendors\VendorsFilterDTO;
use App\DTOs\Reports\Vendors\VendorsSummaryDTO;
use App\Models\Order;
use App\ValueObjects\Money;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Implementation of Vendors Report Repository
 * 
 * Optimized for performance using database-level aggregations.
 */
class EloquentVendorsReportRepository implements VendorsReportRepositoryInterface
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
    public function getTopVendors(VendorsFilterDTO $filter): Collection
    {
        $orderByColumn = $filter->getOrderByColumn();
        $orderDirection = $filter->sortOrder;

        $results = $this->getBaseVendorQuery($filter)
            ->orderBy($orderByColumn, $orderDirection)
            ->limit($filter->limit)
            ->get();

        $totalRevenue = $results->sum('total_revenue');

        return $results->map(function (object $row, int $index) use ($totalRevenue): VendorPerformanceDTO {
            $dto = VendorPerformanceDTO::fromQueryResult($row);
            return $dto->withRankAndPercentage($index + 1, $totalRevenue);
        });
    }

    /**
     * @inheritDoc
     */
    public function getVendorsSummary(VendorsFilterDTO $filter): VendorsSummaryDTO
    {
        $result = $this->getBaseQuery($filter)
            ->selectRaw('
                COUNT(DISTINCT vendors.id) as total_vendors,
                COALESCE(SUM(orders.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as total_orders,
                COUNT(DISTINCT order_items.product_id) as total_products,
                COALESCE(SUM(order_items.quantity), 0) as total_items_sold
            ')
            ->first();

        if ($result === null) {
            return VendorsSummaryDTO::empty($filter->getDateRangeLabel());
        }

        // Get top vendor
        $topVendor = $this->getBaseQuery($filter)
            ->groupBy('vendors.id', 'vendors.name')
            ->selectRaw('vendors.name as vendor_name, SUM(orders.total) as revenue')
            ->orderByDesc('revenue')
            ->first();

        $result->top_vendor_name = $topVendor?->vendor_name;
        $result->top_vendor_revenue = $topVendor?->revenue;

        return VendorsSummaryDTO::fromQueryResult($result, $filter->getDateRangeLabel());
    }

    /**
     * @inheritDoc
     */
    public function getVendorPerformance(int $vendorId, VendorsFilterDTO $filter): ?VendorPerformanceDTO
    {
        $result = $this->getBaseVendorQuery($filter)
            ->where('vendors.id', $vendorId)
            ->first();

        if ($result === null) {
            return null;
        }

        return VendorPerformanceDTO::fromQueryResult($result);
    }

    /**
     * @inheritDoc
     */
    public function getVendorsByCategory(VendorsFilterDTO $filter): Collection
    {
        return $this->getBaseQuery($filter)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('
                categories.id as category_id,
                categories.name as category_name,
                COUNT(DISTINCT vendors.id) as vendors_count,
                COALESCE(SUM(orders.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as orders_count,
                COALESCE(SUM(order_items.quantity), 0) as items_sold
            ')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn(object $row): array => [
                'category_id' => (int) $row->category_id,
                'category_name' => (string) $row->category_name,
                'vendors_count' => (int) $row->vendors_count,
                'total_revenue' => (string) Money::make((float) $row->total_revenue),
                'orders_count' => (int) $row->orders_count,
                'items_sold' => (int) $row->items_sold,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getVendorTrends(int $vendorId, VendorsFilterDTO $filter, string $groupBy = 'daily'): Collection
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
            ->where('vendors.id', $vendorId)
            ->groupByRaw($dateFormat)
            ->selectRaw("
                {$labelFormat} as period_label,
                {$dateFormat} as period_key,
                COALESCE(SUM(orders.total), 0) as total_revenue,
                COUNT(DISTINCT orders.id) as orders_count,
                COALESCE(AVG(orders.total), 0) as average_order_value,
                COALESCE(SUM(order_items.quantity), 0) as items_sold
            ")
            ->orderByRaw("{$dateFormat} ASC")
            ->get()
            ->map(fn(object $row): array => [
                'period_label' => (string) $row->period_label,
                'total_revenue' => (string) Money::make((float) $row->total_revenue),
                'orders_count' => (int) $row->orders_count,
                'average_order_value' => (string) Money::make((float) $row->average_order_value),
                'items_sold' => (int) $row->items_sold,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getLowPerformingVendors(VendorsFilterDTO $filter): Collection
    {
        $results = $this->getBaseVendorQuery($filter)
            ->orderBy('total_revenue', 'asc')
            ->limit($filter->limit)
            ->get();

        $totalRevenue = $results->sum('total_revenue');

        return $results->map(function (object $row, int $index) use ($totalRevenue): VendorPerformanceDTO {
            $dto = VendorPerformanceDTO::fromQueryResult($row);
            return $dto->withRankAndPercentage($index + 1, $totalRevenue);
        });
    }

    /**
     * @inheritDoc
     */
    public function compareVendorPerformance(
        VendorsFilterDTO $currentPeriod,
        VendorsFilterDTO $previousPeriod
    ): array {
        $current = $this->getVendorsSummary($currentPeriod);
        $previous = $this->getVendorsSummary($previousPeriod);

        return [
            'current' => $current->toArray(),
            'previous' => $previous->toArray(),
            'changes' => [
                'vendors_change' => $this->calculatePercentageChange(
                    (float) $previous->totalVendors,
                    (float) $current->totalVendors
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
     * @inheritDoc
     */
    public function getVendorsGrowthRanking(VendorsFilterDTO $filter): Collection
    {
        // Calculate growth by comparing with previous period
        $periodDays = $filter->startDate && $filter->endDate
            ? $filter->startDate->diffInDays($filter->endDate)
            : 30;

        $previousFilter = new VendorsFilterDTO(
            startDate: $filter->startDate?->subDays($periodDays),
            endDate: $filter->startDate?->subDay(),
            vendorId: null,
            categoryId: $filter->categoryId,
            orderStatus: $filter->orderStatus,
            limit: $filter->limit,
        );

        $currentData = $this->getBaseVendorQuery($filter)
            ->get()
            ->keyBy('vendor_id');

        $previousData = $this->getBaseVendorQuery($previousFilter)
            ->get()
            ->keyBy('vendor_id');

        return $currentData->map(function (object $current) use ($previousData): array {
            $previous = $previousData->get($current->vendor_id);
            $previousRevenue = $previous?->total_revenue ?? 0;
            $growthRate = $previousRevenue > 0
                ? round((($current->total_revenue - $previousRevenue) / $previousRevenue) * 100, 2)
                : ($current->total_revenue > 0 ? 100.0 : 0.0);

            return [
                'vendor_id' => (int) $current->vendor_id,
                'vendor_name' => (string) $current->vendor_name,
                'current_revenue' => (string) Money::make((float) $current->total_revenue),
                'previous_revenue' => (string) Money::make((float) $previousRevenue),
                'growth_rate' => $growthRate . '%',
                'growth_amount' => (string) Money::make((float) ($current->total_revenue - $previousRevenue)),
            ];
        })
            ->sortByDesc(fn($item) => (float) str_replace('%', '', $item['growth_rate']))
            ->values();
    }

    /**
     * Build base query for vendor reports
     */
    private function getBaseQuery(VendorsFilterDTO $filter): Builder
    {
        return DB::table('vendors')
            ->leftJoin('orders', 'vendors.id', '=', 'orders.vendor_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->when($filter->startDate, fn(Builder $q) => $q->where('orders.placed_at', '>=', $filter->startDate->toDateTimeString()))
            ->when($filter->endDate, fn(Builder $q) => $q->where('orders.placed_at', '<=', $filter->endDate->toDateTimeString()))
            ->when(
                $filter->orderStatus,
                fn(Builder $q) => $q->where('orders.status', $filter->orderStatus),
                fn(Builder $q) => $q->where(function ($q) {
                    $q->whereNotIn('orders.status', self::EXCLUDED_ORDER_STATUSES)
                        ->orWhereNull('orders.status');
                })
            )
            ->when($filter->vendorId, fn(Builder $q) => $q->where('vendors.id', $filter->vendorId));
    }

    /**
     * Build base vendor query with full vendor details
     */
    private function getBaseVendorQuery(VendorsFilterDTO $filter): Builder
    {
        return $this->getBaseQuery($filter)
            ->groupBy('vendors.id', 'vendors.name', 'vendors.email', 'vendors.phone')
            ->selectRaw('
                vendors.id as vendor_id,
                vendors.name as vendor_name,
                vendors.email as vendor_email,
                vendors.phone as vendor_phone,
                COALESCE(SUM(CASE WHEN orders.status NOT IN (?, ?) THEN orders.total ELSE 0 END), 0) as total_revenue,
                COUNT(DISTINCT CASE WHEN orders.status NOT IN (?, ?) THEN orders.id END) as orders_count,
                COALESCE(AVG(CASE WHEN orders.status NOT IN (?, ?) THEN orders.total END), 0) as average_order_value,
                COUNT(DISTINCT order_items.product_id) as products_count,
                COALESCE(SUM(order_items.quantity), 0) as items_sold,
                COUNT(DISTINCT CASE WHEN orders.status = ? THEN orders.id END) as completed_orders,
                COUNT(DISTINCT CASE WHEN orders.status IN (?, ?) THEN orders.id END) as cancelled_orders
            ', [
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
            ]);
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
