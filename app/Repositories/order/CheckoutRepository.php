<?php

namespace App\Repositories\order;
use App\Models\User;
use App\Repositories\Auth\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutRepository
{
    public function checkout(int $buyerId, array $data): Order
    {
        return DB::transaction(function () use ($buyerId, $data) {
            $cart = Cart::where('buyer_id', $buyerId)
                ->where('status', 'active')
                ->when(isset($data['seller_id']), fn($q) => $q->where('seller_id', $data['seller_id']))
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$cart) {
                throw ValidationException::withMessages(['cart' => 'No active cart found.']);
            }

            $cart->load('items');
            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Cart is empty.']);
            }

            $order = Order::create([
                'order_number' => 'TMP-' . uniqid(),
                'customer_id' => $cart->buyer_id,
                'vendor_id' => $cart->seller_id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_status' => 'pending',
                'subtotal' => $cart->subtotal,
                'tax_amount' => $cart->tax_amount,
                'discount_amount' => $cart->discount_amount,
                'shipping_amount' => $cart->shipping_amount,
                'total' => $cart->total,
                'shipping_address_id' => $data['shipping_address_id'],
                'billing_address_id' => $data['billing_address_id'] ?? $data['shipping_address_id'],
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            $order->order_number = 'ORD-' . $order->id . '-' . now()->format('Ymd');
            $order->save();

            foreach ($cart->items as $ci) {
                // IMPORTANT: product_name is NOT NULL in your order_items table.
                // Replace this with actual product name from DB.
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
                'status' => 'converted',
                'converted_order_id' => $order->id,
                'cart_token' => null,
            ]);

            return $order->fresh()->load('items');
        });
    }
}

