<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVendorSkuResource;
use App\Models\Product;
use App\Models\ProductVendorSku;
use App\Models\ProductVendorSkuUnit;
use App\Models\Unit;
use App\Repositories\order\CartRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    public function __construct(private CartRepository $cartRepo) {}

    private function cartToken(Request $request): ?string
    {
        return $request->header('X-Cart-Token') ?: $request->input('cart_token');
    }

    public function show(Request $request)
    {
        $buyerId  = optional($request->user())->id;
        $cartToken = $request->header('X-Cart-Token') ?: $request->input('cart_token');

        // Token-first: إذا موجود توكن استخدمه حتى لو buyerId موجود
        $cart = $this->cartRepo->getOrCreateActiveCart(
            $buyerId,
            $cartToken,
            $request->input('seller_id')
        );
        return new CartResource($cart->load('items'));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'seller_id' => ['nullable','integer'],
            'product_id' => ['required','integer'],
            'variant_id' => ['nullable','integer'],
            'product_vendor_sku_id' => ['nullable','integer'],
            'product_vendor_sku_unit_id' => ['nullable','integer'],
            'unit_id' => ['nullable','integer'],
            'sku' => ['nullable','string','max:191'],
            'package_size' => ['nullable','integer','min:1'],
            'quantity' => ['required','integer','min:1'],
            'unit_price' => ['nullable','numeric','min:0'],
            'discount' => ['nullable','numeric','min:0'],
            'tax' => ['nullable','numeric','min:0'],
            'notes' => ['nullable','string'],
        ]);

        $buyerId = optional($request->user())->id;
        $cart = $this->cartRepo->getOrCreateActiveCart($buyerId, $this->cartToken($request), $data['seller_id'] ?? null);
        $cart = $this->cartRepo->addItem($cart, $data);

        return new CartResource($cart);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $data = $request->validate([
            'seller_id' => ['nullable','integer'],
            'quantity' => ['required','integer','min:1'],
        ]);

        $buyerId = optional($request->user())->id;
        $cart = $this->cartRepo->getOrCreateActiveCart($buyerId, $this->cartToken($request), $data['seller_id'] ?? null);
        $cart = $this->cartRepo->updateItemQuantity($cart, $itemId, (int)$data['quantity']);

        return new CartResource($cart);
    }

    public function removeItem(Request $request, int $itemId)
    {
        $data = $request->validate([
            'seller_id' => ['nullable','integer'],
        ]);

        $buyerId = optional($request->user())->id;
        $cart = $this->cartRepo->getOrCreateActiveCart($buyerId, $this->cartToken($request), $data['seller_id'] ?? null);
        $cart = $this->cartRepo->removeItem($cart, $itemId);

        return new CartResource($cart);
    }

    public function claim(Request $request)
    {
        $data = $request->validate([
            'cart_token' => ['required','string'],
        ]);

        $cart = $this->cartRepo->claimGuestCart($data['cart_token'], $request->user()->id);

        return new CartResource($cart);
    }
}

