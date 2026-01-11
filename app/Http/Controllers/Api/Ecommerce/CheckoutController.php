<?php

namespace App\Http\Controllers\Api\Ecommerce;
use App\Repositories\order\CheckoutRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
class CheckoutController extends Controller
{
    public function __construct(private CheckoutRepository $checkoutRepo) {}

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'cart_id'              => ['required', 'integer'],
            'shipping_address_id'  => ['required', 'integer'],
            'billing_address_id'   => ['nullable', 'integer'],
            'notes'                => ['nullable', 'string'],
        ]);

        $order = $this->checkoutRepo->checkout(
            cartId: (int) $data['cart_id'],
            buyerId: (int) $request->user()->id,
            shippingAddressId: (int) $data['shipping_address_id'],
            billingAddressId: isset($data['billing_address_id']) ? (int) $data['billing_address_id'] : null,
            notes: $data['notes'] ?? null,
        );

        return new OrderResource($order);
    }
}

