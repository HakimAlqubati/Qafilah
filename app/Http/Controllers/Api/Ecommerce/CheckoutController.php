<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVendorSkuResource;
use App\Models\Product;
use App\Models\ProductVendorSku;
use App\Models\ProductVendorSkuUnit;
use App\Models\Unit;
use App\Repositories\order\CartRepository;
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
            'seller_id' => ['nullable','integer'],
            'shipping_address_id' => ['required','integer'],
            'billing_address_id' => ['nullable','integer'],
            'notes' => ['nullable','string'],
        ]);

        $order = $this->checkoutRepo->checkout($request->user()->id, $data);

        return new OrderResource($order);
    }
}


