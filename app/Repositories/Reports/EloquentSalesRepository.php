<?php

declare(strict_types=1);

namespace App\Repositories\Reports;

use App\DTOs\Reports\SalesFilterDTO;
use App\DTOs\Reports\SalesSummaryDTO;
use App\DTOs\Reports\VendorSalesDTO;
use App\Models\Order;
use App\ValueObjects\Money;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Implementation of Sales Repository
 * 
 * Optimized for performance using database-level aggregations.
 * Follows SOLID principles and uses strict type hinting.
 */
class EloquentSalesRepository implements SalesRepositoryInterface
{
    /**
     * Excluded statuses for revenue calculations
     * 
     * @var array<int, string>
     */
    private const EXCLUDED_REVENUE_STATUSES = [
        Order::STATUS_CANCELLED,
        Order::STATUS_RETURNED,
    ];

    public function __construct(
        private readonly Order $orderModel,
    ) {}

    /**
     * @inheritDoc
     */
    public function getSalesSummary(SalesFilterDTO $filter): SalesSummaryDTO
    {
        $result = $this->getBaseQuery($filter)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN status NOT IN (?, ?) THEN total ELSE 0 END), 0) as total_revenue,
                COUNT(CASE WHEN status NOT IN (?, ?) THEN 1 END) as orders_count,
                COALESCE(AVG(CASE WHEN status NOT IN (?, ?) THEN total END), 0) as average_order_value,
                COALESCE(SUM(CASE WHEN status NOT IN (?, ?) THEN tax_amount ELSE 0 END), 0) as total_tax,
                COALESCE(SUM(CASE WHEN status NOT IN (?, ?) THEN discount_amount ELSE 0 END), 0) as total_discount,
                COALESCE(SUM(CASE WHEN status NOT IN (?, ?) THEN shipping_amount ELSE 0 END), 0) as total_shipping,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as cancelled_orders_count,
                COALESCE(SUM(CASE WHEN status IN (?, ?) THEN total ELSE 0 END), 0) as cancelled_amount
            ', [
                // For total_revenue
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For orders_count
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For average_order_value
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For total_tax
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For total_discount
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For total_shipping
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For cancelled_orders_count
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                // For cancelled_amount
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
            ])
            ->first();

        if ($result === null) {
            return SalesSummaryDTO::empty($filter->getDateRangeLabel());
        }

        // Get items count from order_items
        $itemsCount = $this->getItemsCountQuery($filter)->value('items_count') ?? 0;

        $result->items_count = $itemsCount;

        return SalesSummaryDTO::fromQueryResult($result, $filter->getDateRangeLabel());
    }

    /**
     * @inheritDoc
     */
    public function getSalesByVendor(SalesFilterDTO $filter, int $limit = 10): Collection
    {
        $results = $this->getBaseQuery($filter)
            ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->whereNotIn('orders.status', self::EXCLUDED_REVENUE_STATUSES)
            ->groupBy('orders.vendor_id', 'vendors.name')
            ->selectRaw('
                orders.vendor_id,
                vendors.name as vendor_name,
                SUM(orders.total) as total_revenue,
                COUNT(*) as orders_count,
                AVG(orders.total) as average_order_value,
                COUNT(DISTINCT orders.id) as unique_orders
            ')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        // Calculate total for percentage
        $totalRevenue = $results->sum('total_revenue');

        return $results->map(function (object $row, int $index) use ($totalRevenue): VendorSalesDTO {
            $dto = VendorSalesDTO::fromQueryResult($row);
            return $dto->withRankAndPercentage($index + 1, $totalRevenue);
        });
    }

    /**
     * @inheritDoc
     */
    public function getVendorSalesSummary(int $vendorId, SalesFilterDTO $filter): SalesSummaryDTO
    {
        $vendorFilter = new SalesFilterDTO(
            startDate: $filter->startDate,
            endDate: $filter->endDate,
            vendorId: $vendorId,
            status: $filter->status,
            paymentStatus: $filter->paymentStatus,
            customerId: $filter->customerId,
            categoryId: $filter->categoryId,
        );

        return $this->getSalesSummary($vendorFilter);
    }

    /**
     * @inheritDoc
     */
    public function getSalesTrends(SalesFilterDTO $filter, string $groupBy = 'daily'): Collection
    {
        $dateFormat = match ($groupBy) {
            'weekly' => "DATE_FORMAT(placed_at, '%Y-%u')",
            'monthly' => "DATE_FORMAT(placed_at, '%Y-%m')",
            default => 'DATE(placed_at)',
        };

        $labelFormat = match ($groupBy) {
            'weekly' => "CONCAT('Week ', WEEK(placed_at), ' - ', YEAR(placed_at))",
            'monthly' => "DATE_FORMAT(placed_at, '%Y-%m')",
            default => 'DATE(placed_at)',
        };

        $results = $this->getBaseQuery($filter)
            ->whereNotIn('status', self::EXCLUDED_REVENUE_STATUSES)
            ->groupByRaw($dateFormat)
            ->selectRaw("
                {$labelFormat} as period_label,
                {$dateFormat} as period_key,
                SUM(total) as total_revenue,
                COUNT(*) as orders_count,
                AVG(total) as average_order_value,
                SUM(tax_amount) as total_tax,
                SUM(discount_amount) as total_discount,
                SUM(shipping_amount) as total_shipping
            ")
            ->orderByRaw("{$dateFormat} ASC")
            ->get();

        return $results->map(
            fn(object $row): SalesSummaryDTO =>
            SalesSummaryDTO::fromQueryResult($row, (string) $row->period_label)
        );
    }

    /**
     * @inheritDoc
     */
    public function getTopProducts(SalesFilterDTO $filter, int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.placed_at', [
                $filter->startDate->toDateTimeString(),
                $filter->endDate->toDateTimeString(),
            ])
            ->whereNotIn('orders.status', self::EXCLUDED_REVENUE_STATUSES)
            ->when($filter->vendorId, fn(Builder $q) => $q->where('orders.vendor_id', $filter->vendorId))
            ->when($filter->categoryId, fn(Builder $q) => $q->where('products.category_id', $filter->categoryId))
            ->groupBy('order_items.product_id', 'products.name')
            ->selectRaw('
                order_items.product_id,
                products.name as product_name,
                SUM(order_items.quantity) as quantity_sold,
                SUM(order_items.total) as total_revenue,
                AVG(order_items.unit_price) as average_price
            ')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn(object $row): array => [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'quantity_sold' => (int) $row->quantity_sold,
                'total_revenue' => (string) Money::make((float) $row->total_revenue),
                'average_price' => (string) Money::make((float) $row->average_price),
            ]);
    }

    /**
     * @inheritDoc
     */
    public function comparePeriods(
        SalesFilterDTO $currentPeriod,
        SalesFilterDTO $previousPeriod
    ): array {
        $current = $this->getSalesSummary($currentPeriod);
        $previous = $this->getSalesSummary($previousPeriod);

        return [
            'current' => $current->toArray(),
            'previous' => $previous->toArray(),
            'changes' => [
                'revenue_change' => $this->calculatePercentageChange(
                    $previous->totalRevenue,
                    $current->totalRevenue
                ) . '%',
                'orders_change' => $this->calculatePercentageChange(
                    (float) $previous->ordersCount,
                    (float) $current->ordersCount
                ) . '%',
                'average_change' => $this->calculatePercentageChange(
                    $previous->averageOrderValue,
                    $current->averageOrderValue
                ) . '%',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getCustomerMetrics(SalesFilterDTO $filter): array
    {
        // Find customers who made their first order in this period
        $newCustomersData = $this->getBaseQuery($filter)
            ->whereNotIn('status', self::EXCLUDED_REVENUE_STATUSES)
            ->whereIn('customer_id', function ($query) use ($filter) {
                $query->select('customer_id')
                    ->from('orders')
                    ->groupBy('customer_id')
                    ->havingRaw('MIN(placed_at) >= ?', [$filter->startDate->toDateTimeString()])
                    ->havingRaw('MIN(placed_at) <= ?', [$filter->endDate->toDateTimeString()]);
            })
            ->selectRaw('
                COUNT(DISTINCT customer_id) as count,
                COALESCE(SUM(total), 0) as revenue
            ')
            ->first();

        // Calculate returning customers
        $returningCustomersData = $this->getBaseQuery($filter)
            ->whereNotIn('status', self::EXCLUDED_REVENUE_STATUSES)
            ->whereIn('customer_id', function ($query) use ($filter) {
                $query->select('customer_id')
                    ->from('orders')
                    ->groupBy('customer_id')
                    ->havingRaw('MIN(placed_at) < ?', [$filter->startDate->toDateTimeString()]);
            })
            ->selectRaw('
                COUNT(DISTINCT customer_id) as count,
                COALESCE(SUM(total), 0) as revenue
            ')
            ->first();

        return [
            'new_customers' => (int) ($newCustomersData?->count ?? 0),
            'returning_customers' => (int) ($returningCustomersData?->count ?? 0),
            'new_customer_revenue' => (string) Money::make((float) ($newCustomersData?->revenue ?? 0)),
            'returning_customer_revenue' => (string) Money::make((float) ($returningCustomersData?->revenue ?? 0)),
        ];
    }

    /**
     * Build base query with common filters applied
     */
    private function getBaseQuery(SalesFilterDTO $filter): \Illuminate\Database\Eloquent\Builder
    {
        return $this->orderModel->newQuery()
            ->whereBetween('placed_at', [
                $filter->startDate->toDateTimeString(),
                $filter->endDate->toDateTimeString(),
            ])
            ->when($filter->vendorId, fn($q) => $q->where('vendor_id', $filter->vendorId))
            ->when($filter->customerId, fn($q) => $q->where('customer_id', $filter->customerId))
            ->when($filter->status, fn($q) => $q->where('status', $filter->status))
            ->when($filter->paymentStatus, fn($q) => $q->where('payment_status', $filter->paymentStatus));
    }

    /**
     * Get items count query
     */
    private function getItemsCountQuery(SalesFilterDTO $filter): \Illuminate\Database\Query\Builder
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.placed_at', [
                $filter->startDate->toDateTimeString(),
                $filter->endDate->toDateTimeString(),
            ])
            ->whereNotIn('orders.status', self::EXCLUDED_REVENUE_STATUSES)
            ->when($filter->vendorId, fn(Builder $q) => $q->where('orders.vendor_id', $filter->vendorId))
            ->when($filter->customerId, fn(Builder $q) => $q->where('orders.customer_id', $filter->customerId))
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as items_count');
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
