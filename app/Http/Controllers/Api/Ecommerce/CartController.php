<?php

namespace App\Http\Controllers\Api\Ecommerce;
use App\Repositories\order\CartRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
class CartController extends Controller
{
    use ApiResponse;
    public function __construct(private CartRepository $cartRepo) {}

    private function cartToken(Request $request): ?string
    {
        return $request->header('X-Cart-Token') ?: $request->input('cart_token');
    }

    public function show(Request $request)
    {
        $buyerId   = optional($request->user())->id;
        $cartToken = $this->cartToken($request);
        $sellerId  = $request->input('seller_id');

        $cart = $this->cartRepo
            ->context($buyerId, $cartToken, $sellerId)
            ->show();
        if(!$cart){
            return $this->successResponse(null, "");
        }

        return new CartResource($cart);
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'seller_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'product_vendor_sku_id' => ['nullable', 'integer'],
            'product_vendor_sku_unit_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);
        $buyerId   = optional($request->user())->id;
        $cartToken = $this->cartToken($request);
        $sellerId  = $data['seller_id'] ?? null;

        $cart = $this->cartRepo
            ->context($buyerId, $cartToken, $sellerId)
            ->withItemData($data)
            ->addItem();

        return new CartResource($cart);
    }

    public function updateItem(Request $request)
    {
        $data = $request->validate([
            'cart_id'  => ['required','integer'],
            'item_id'  => ['required','integer'],
            'quantity' => ['required','integer','min:1'],
        ]);

        $buyerId   = optional($request->user())->id;
        $cartToken = $this->cartToken($request);

        $cart = $this->cartRepo
            ->context($buyerId, $cartToken, null)
            ->expectCartId((int) $data['cart_id'])
            ->withQuantity((int) $data['quantity'])
            ->updateItemQuantity((int) $data['item_id']);

        return new CartResource($cart);
    }

    public function removeItem(Request $request)
    {
        $data = $request->validate([
            'cart_id' => ['required', 'integer', 'exists:carts,id'],
            'item_id' => ['required', 'integer'],
        ]);

        $buyerId   = optional($request->user())->id;
        $cartToken = $this->cartToken($request);

        $cart = $this->cartRepo
            ->context($buyerId, $cartToken, null)
            ->expectCartId((int) $data['cart_id'])
            ->removeItem((int) $data['item_id']);

        return new CartResource($cart);
    }
    public function incItem(Request $request)
    {
        $data = $request->validate([
            'cart_id' => ['required', 'integer', 'exists:carts,id'],
            'item_id' => ['required','integer'],
        ]);

        $buyerId   = optional($request->user())->id;
        $cartToken = $this->cartToken($request);

        $payload = $this->cartRepo
            ->context($buyerId, $cartToken, null)
            ->expectCartId((int) $data['cart_id'])
            ->incrementItemById((int) $data['item_id']);
        return $this->successResponse($payload, "");
    }

    public function decItem(Request $request)
    {
        $data = $request->validate([
            'cart_id' => ['required', 'integer', 'exists:carts,id'],
            'item_id' => ['required','integer'],
        ]);

        $buyerId   = optional($request->user())->id;
        $cartToken = $this->cartToken($request);

        $payload = $this->cartRepo
            ->context($buyerId, $cartToken, null)
            ->expectCartId((int) $data['cart_id'])
            ->decrementItemById((int) $data['item_id']);
        return $this->successResponse($payload, "");
    }

    public function claim(Request $request)
    {
        $data = $request->validate([
            'cart_token' => ['required', 'string'],
        ]);

        $buyerId = (int) $request->user()->id;

        $cart = $this->cartRepo
            ->forBuyer($buyerId)
            ->withClaimToken($data['cart_token'])
            ->claimGuestCart();

        return new CartResource($cart);
    }
}


