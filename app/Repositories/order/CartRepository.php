<?php

namespace App\Repositories\order;
use App\Models\ProductVendorSkuUnit;
use App\Models\User;
use App\Repositories\Auth\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartRepository
{
    public function getOrCreateActiveCart(?int $buyerId, ?string $cartToken, ?int $sellerId = null): Cart
    {
        $q = Cart::query()->where('status', 'active');

        if ($cartToken) {
            $q->where('cart_token', $cartToken);
        } elseif ($buyerId) {
            $q->where('buyer_id', $buyerId);
        } else {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cart_token' => 'cart_token is required for guest cart.',
            ]);
        }


        $cart = $q->latest('id')->first();
        if ($cart) {
             if ($buyerId && is_null($cart->buyer_id)) {
                $cart->update(['buyer_id' => $buyerId]);
            }
            return $cart;
        }


        return Cart::create([
            'buyer_id'   => $buyerId,
            'seller_id'  => $sellerId,
            'cart_token' => $cartToken,
            'status'     => 'active',
        ]);
    }

    public function addItem(Cart $cart, array $data): Cart
    {
        return DB::transaction(function () use ($cart, $data) {

             Cart::where('id', $cart->id)->lockForUpdate()->first();

            $qty = (int) $data['quantity'];

            $discount  =   0;
            $tax       = 0;

            $itemQuery = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $data['product_id']);

            $productVendorSkuUnit = ProductVendorSkuUnit::where('id', $data['product_vendor_sku_unit_id'])->first();
            $unitPrice = (float) ( $productVendorSkuUnit->selling_price ?? 0);
            $itemQuery = $this->whereNullSafe($itemQuery, 'product_vendor_sku_id', $data['product_vendor_sku_id'] ?? null);
            $itemQuery = $this->whereNullSafe($itemQuery, 'product_vendor_sku_unit_id', $data['product_vendor_sku_unit_id'] ?? null);

            $item = $itemQuery->lockForUpdate()->first();
             if ($item) {
                $item->quantity += $qty;

                $item->unit_price = $unitPrice;
                $item->discount   = $discount;
                $item->tax        = $tax;

                $item->total = $this->calcLineTotal($item->unit_price, $item->quantity, $item->discount, $item->tax);
                $item->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $data['product_id'],

                    'variant_id' => $data['variant_id'] ?? null,
                    'unit_id'    => $productVendorSkuUnit->unit_id ?? null,

                    'product_vendor_sku_id' => $data['product_vendor_sku_id'] ?? null,
                    'product_vendor_sku_unit_id' => $data['product_vendor_sku_unit_id'] ?? null,

                    'sku' => $data['sku'] ?? null,
                    'package_size' => $data['package_size'] ?? 1,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $this->calcLineTotal($unitPrice, $qty, $discount, $tax),
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            $this->recalcTotals($cart);

            return $cart->fresh()->load('items');
        });
    }

    public function updateItemQuantity(Cart $cart, int $itemId, int $quantity): Cart
    {
        return DB::transaction(function () use ($cart, $itemId, $quantity) {

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $item = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->lockForUpdate()
                ->firstOrFail();

            $item->quantity = $quantity;
            $item->total = $this->calcLineTotal($item->unit_price, $item->quantity, $item->discount, $item->tax);
            $item->save();

            $this->recalcTotals($cart);

            return $cart->fresh()->load('items');
        });
    }

    public function removeItem(Cart $cart, int $itemId): Cart
    {
        return DB::transaction(function () use ($cart, $itemId) {

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->delete();

            $this->recalcTotals($cart);

            return $cart->fresh()->load('items');
        });
    }

    public function claimGuestCart(string $cartToken, int $buyerId): Cart
    {
        return DB::transaction(function () use ($cartToken, $buyerId) {

            $guestCart = Cart::where('cart_token', $cartToken)
                ->where('status', 'active')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$guestCart) {
                throw ValidationException::withMessages(['cart_token' => 'No active guest cart found.']);
            }

            $userCart = Cart::where('buyer_id', $buyerId)
                ->where('status', 'active')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$userCart) {
                $guestCart->update([
                    'buyer_id' => $buyerId,
                    'cart_token' => null,
                ]);

                $this->recalcTotals($guestCart);

                return $guestCart->fresh()->load('items');
            }

            $guestItems = $guestCart->items()->lockForUpdate()->get();

            foreach ($guestItems as $gi) {

                $existsQ = CartItem::query()
                    ->where('cart_id', $userCart->id)
                    ->where('product_id', $gi->product_id);

                $existsQ = $this->whereNullSafe($existsQ, 'product_vendor_sku_id', $gi->product_vendor_sku_id);
                $existsQ = $this->whereNullSafe($existsQ, 'product_vendor_sku_unit_id', $gi->product_vendor_sku_unit_id);

                $exists = $existsQ->lockForUpdate()->first();

                if ($exists) {
                    $exists->quantity += $gi->quantity;

                    $exists->total = $this->calcLineTotal($exists->unit_price, $exists->quantity, $exists->discount, $exists->tax);
                    $exists->save();

                    $gi->delete();
                } else {
                    $gi->cart_id = $userCart->id;
                    $gi->save();
                }
            }

            $guestCart->update(['status' => 'abandoned']);

            $this->recalcTotals($userCart);

            return $userCart->fresh()->load('items');
        });
    }

    public function recalcTotals(Cart $cart): Cart
    {
        $items = $cart->items()->get();

        $subtotal = $items->sum(fn($i) => (float)$i->unit_price * (int)$i->quantity);
        $discount = $items->sum(fn($i) => (float)$i->discount);
        $tax      = $items->sum(fn($i) => (float)$i->tax);

        $itemsTotal = $items->sum(fn($i) => (float)$i->total);
        $computedTotal = ($subtotal - $discount) + $tax + (float)$cart->shipping_amount;

        $total = $itemsTotal > 0 ? $itemsTotal : $computedTotal;

        $cart->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => $total,
        ]);

        return $cart;
    }

    private function calcLineTotal(float $unitPrice, int $qty, float $discount, float $tax): float
    {
        return (($unitPrice * $qty) - $discount) + $tax;
    }

    private function whereNullSafe($query, string $column, $value)
    {
        return is_null($value)
            ? $query->whereNull($column)
            : $query->where($column, $value);
    }

}
