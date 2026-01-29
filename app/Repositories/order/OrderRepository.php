<?php

namespace App\Repositories\order;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    /**
     * Get orders for a specific customer.
     *
     * @param int $customerId
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getOrder(int $customerId, int $limit = 10): LengthAwarePaginator
    {
        return Order::query()
            ->where('customer_id', $customerId)
            ->paginate($limit);
    }

    /**
     * Find a specific order for a customer.
     *
     * @param int $customerId
     * @param string|int $orderId
     * @return Order|null
     */
    public function findForCustomer(int $customerId, string|int $orderId): ?Order
    {
        return Order::query()
            ->where('customer_id', $customerId)
            ->where(function (Builder $query) use ($orderId) {
                $query->where('id', $orderId)
                      ->orWhere('order_number', $orderId);
            })
            ->with(['items', 'shippingAddress', 'billingAddress', 'paymentTransactions'])
            ->first();
    }
}
