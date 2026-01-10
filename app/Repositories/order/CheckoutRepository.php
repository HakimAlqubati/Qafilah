<?php

namespace App\Repositories\order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\OrderItem;
class CheckoutRepository
{
    private ?int $buyerId = null;

    private ?int $sellerId = null;
    private ?int $shippingAddressId = null;
    private ?int $billingAddressId = null;
    private ?string $notes = null;

    public function forBuyer(int $buyerId): self
    {
        $self = clone $this;
        $self->buyerId = $buyerId;
        return $self;
    }

    public function withSellerId(?int $sellerId): self
    {
        $self = clone $this;
        $self->sellerId = $sellerId;
        return $self;
    }

    public function withShippingAddressId(int $shippingAddressId): self
    {
        $self = clone $this;
        $self->shippingAddressId = $shippingAddressId;
        return $self;
    }

    public function withBillingAddressId(?int $billingAddressId): self
    {
        $self = clone $this;
        $self->billingAddressId = $billingAddressId;
        return $self;
    }

    public function withNotes(?string $notes): self
    {
        $self = clone $this;
        $self->notes = $notes;
        return $self;
    }

    public function checkout(): Order
    {
        if (!$this->buyerId) {
            throw ValidationException::withMessages(['buyer_id' => 'buyer_id is required.']);
        }
        if (!$this->shippingAddressId) {
            throw ValidationException::withMessages(['shipping_address_id' => 'shipping_address_id is required.']);
        }

        $billingAddressId = $this->billingAddressId ?? $this->shippingAddressId;

        return DB::transaction(function () use ($billingAddressId) {
            $cart = Cart::where('buyer_id', $this->buyerId)
                ->where('status', 'active')
                ->when($this->sellerId !== null, fn($q) => $q->where('seller_id', $this->sellerId))
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
                'order_number'       => 'TMP-' . uniqid(),
                'customer_id'        => $cart->buyer_id,
                'vendor_id'          => $cart->seller_id,
                'status'             => 'pending',
                'payment_status'     => 'pending',
                'shipping_status'    => 'pending',
                'subtotal'           => $cart->subtotal,
                'tax_amount'         => $cart->tax_amount,
                'discount_amount'    => $cart->discount_amount,
                'shipping_amount'    => $cart->shipping_amount,
                'total'              => $cart->total,
                'shipping_address_id'=> $this->shippingAddressId,
                'billing_address_id' => $billingAddressId,
                'notes'              => $this->notes,
                'placed_at'          => now(),
            ]);

            $order->order_number = 'ORD-' . $order->id . '-' . now()->format('Ymd');
            $order->save();

            foreach ($cart->items as $ci) {
                $productName = 'Product #' . $ci->product_id; // الأفضل تجيبه من products

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

