<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\order\OrderRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $orders = $this->orderRepository->getOrder(auth()->id(), $perPage);

        $orders->getCollection()->transform(function ($order) {
            return new OrderResource($order);
        });

        return $this->successResponse($orders, 'Orders fetched successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = $this->orderRepository->findForCustomer(auth()->id(), $id);

        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        return $this->successResponse(
           new OrderResource($order),
            'Order details fetched successfully.'
        );
    }
}
