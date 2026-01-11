<?php

namespace App\Repositories\order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutRepository
{
    public function checkout(
        int $cartId,
        int $buyerId,
        int $shippingAddressId,
        ?int $billingAddressId = null,
        ?string $notes = null,
    ): Order {
        if ($cartId <= 0) {
            throw ValidationException::withMessages(['cart_id' => 'cart_id is required.']);
        }
        if ($buyerId <= 0) {
            throw ValidationException::withMessages(['buyer_id' => 'buyer_id is required.']);
        }
        if ($shippingAddressId <= 0) {
            throw ValidationException::withMessages(['shipping_address_id' => 'shipping_address_id is required.']);
        }

        $billingAddressId = $billingAddressId ?? $shippingAddressId;

        return DB::transaction(function () use (
            $cartId,
            $buyerId,
            $shippingAddressId,
            $billingAddressId,
            $notes
        ) {

            $cart = Cart::query()
                ->whereKey($cartId)
                ->where('buyer_id', $buyerId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart not found or not active (or not owned by you).',
                ]);
            }

            $cart->load('items');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Cart is empty.']);
            }

            if ($cart->converted_order_id) {
                $existing = Order::query()
                    ->whereKey($cart->converted_order_id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if ($existing->payment_status !== 'paid') {
                        $this->syncDraftOrderFromCart(
                            order: $existing,
                            cart: $cart,
                            shippingAddressId: $shippingAddressId,
                            billingAddressId: $billingAddressId,
                            notes: $notes,
                        );

                        return $existing->fresh()->load('items');
                    }

                    throw ValidationException::withMessages([
                        'cart' => 'This cart is already linked to a paid order.',
                    ]);
                }


                $cart->update(['converted_order_id' => null]);
            }
            $order = Order::create([
                'order_number'         => 'TMP-' . uniqid(),
                'customer_id'          => $cart->buyer_id,
                'vendor_id'            => $cart->seller_id ?? null,
                'status'               => 'pending',
                'payment_status'       => 'pending',
                'shipping_status'      => 'pending',
                'subtotal'             => $cart->subtotal,
                'tax_amount'           => $cart->tax_amount,
                'discount_amount'      => $cart->discount_amount,
                'shipping_amount'      => $cart->shipping_amount,
                'total'                => $cart->total,
                'shipping_address_id'  => $shippingAddressId,
                'billing_address_id'   => $billingAddressId,
                'notes'                => $notes,
                'placed_at'            => now(),
            ]);

            $order->order_number = 'ORD-' . $order->id . '-' . now()->format('Ymd');
            $order->save();

            foreach ($cart->items as $ci) {
                $productName = 'Product #' . $ci->product_id;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $ci->product_id,
                    'variant_id' => $ci->variant_id,
                    'product_vendor_sku_id' => $ci->product_vendor_sku_id,
                    'product_vendor_sku_unit_id' => $ci->product_vendor_sku_unit_id,
                    'unit_id' => $ci->unit_id,

                    'product_name' => $productName,
                    'sku' => $ci->sku,
                    'package_size' => $ci->package_size,
                    'quantity' => $ci->quantity,

                    'unit_price' => $ci->unit_price,
                    'discount' => $ci->discount,
                    'tax' => $ci->tax,
                    'total' => $ci->total,

                    'notes' => $ci->notes,
                ]);
            }

            $cart->update([
                'converted_order_id' => $order->id,
            ]);

            return $order->fresh()->load('items');
        });
    }

    private function syncDraftOrderFromCart(
        Order $order,
        Cart $cart,
        int $shippingAddressId,
        int $billingAddressId,
        ?string $notes,
    ): void {
        $order->fill([
            'vendor_id'            => $cart->seller_id ?? null,
            'subtotal'             => $cart->subtotal,
            'tax_amount'           => $cart->tax_amount,
            'discount_amount'      => $cart->discount_amount,
            'shipping_amount'      => $cart->shipping_amount,
            'total'                => $cart->total,
            'shipping_address_id'  => $shippingAddressId,
            'billing_address_id'   => $billingAddressId,
            'notes'                => $notes,
        ])->save();
        $order->items()->delete();

        foreach ($cart->items as $ci) {
            $productName = 'Product #' . $ci->product_id;

            $order->items()->create([
                'product_id' => $ci->product_id,
                'variant_id' => $ci->variant_id,
                'product_vendor_sku_id' => $ci->product_vendor_sku_id,
                'product_vendor_sku_unit_id' => $ci->product_vendor_sku_unit_id,
                'unit_id' => $ci->unit_id,

                'product_name' => $productName,
                'sku' => $ci->sku,
                'package_size' => $ci->package_size,
                'quantity' => $ci->quantity,

                'unit_price' => $ci->unit_price,
                'discount' => $ci->discount,
                'tax' => $ci->tax,
                'total' => $ci->total,

                'notes' => $ci->notes,
            ]);
        }
    }
}


