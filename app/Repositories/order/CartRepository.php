<?php

namespace App\Repositories\order;
use App\Models\ProductVendorSkuUnit;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartRepository
{
    // سياق الطلب
    private ?int $buyerId = null;
    private ?string $cartToken = null;
    private ?int $sellerId = null;

    // حماية اختيارية (للـ update/remove)
    private ?int $expectedCartId = null;

    // بيانات item
    private ?int $productId = null;
    private ?int $variantId = null;
    private ?int $productVendorSkuId = null;
    private ?int $productVendorSkuUnitId = null;
    private int $quantity = 1;
    private ?string $notes = null;

    // claim
    private ?string $claimCartToken = null;


    public function context(?int $buyerId, ?string $cartToken, ?int $sellerId = null): self
    {
        $self = clone $this;
        $self->buyerId = $buyerId;
        $self->cartToken = $cartToken;
        $self->sellerId = $sellerId;
        return $self;
    }

    public function forBuyer(int $buyerId): self
    {
        $self = clone $this;
        $self->buyerId = $buyerId;
        return $self;
    }

    public function expectCartId(int $cartId): self
    {
        $self = clone $this;
        $self->expectedCartId = $cartId;
        return $self;
    }

    public function withItemData(array $data): self
    {
        $self = clone $this;

        $self->productId = (int) $data['product_id'];
        $self->variantId = isset($data['variant_id']) ? (int) $data['variant_id'] : null;
        $self->productVendorSkuId = isset($data['product_vendor_sku_id']) ? (int) $data['product_vendor_sku_id'] : null;
        $self->productVendorSkuUnitId = isset($data['product_vendor_sku_unit_id']) ? (int) $data['product_vendor_sku_unit_id'] : null;
        $self->quantity = (int) $data['quantity'];
        $self->notes = $data['notes'] ?? null;

        return $self;
    }

    public function withQuantity(int $quantity): self
    {
        $self = clone $this;
        $self->quantity = $quantity;
        return $self;
    }

    public function withClaimToken(string $token): self
    {
        $self = clone $this;
        $self->claimCartToken = $token;
        return $self;
    }

    public function show(): Cart
    {
        $cart = $this->getOrCreateActiveCart();

        $this->assertExpectedCart($cart);

        return $cart->load('items');
    }

    public function addItem(): Cart
    {
        $this->requireItemData();

        return DB::transaction(function () {
            $cart = $this->getOrCreateActiveCart();

            $this->assertExpectedCart($cart);

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $qty = $this->quantity;

            $discount = 0;
            $tax = 0;

            $itemQuery = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $this->productId);

            $productVendorSkuUnit = $this->productVendorSkuUnitId
                ? ProductVendorSkuUnit::where('id', $this->productVendorSkuUnitId)->first()
                : null;

            $unitPrice = (float) ($productVendorSkuUnit->selling_price ?? 0);
            $unitId    = (int) ($productVendorSkuUnit->unit_id ?? 0) ?: null;

            $itemQuery = $this->whereNullSafe($itemQuery, 'product_vendor_sku_id', $this->productVendorSkuId);
            $itemQuery = $this->whereNullSafe($itemQuery, 'product_vendor_sku_unit_id', $this->productVendorSkuUnitId);

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
                    'product_id' => $this->productId,

                    'variant_id' => $this->variantId,
                    'unit_id'    => $unitId,

                    'product_vendor_sku_id' => $this->productVendorSkuId,
                    'product_vendor_sku_unit_id' => $this->productVendorSkuUnitId,

                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $this->calcLineTotal($unitPrice, $qty, $discount, $tax),
                    'notes' => $this->notes,
                ]);
            }

            $this->recalcTotals($cart);

            return $cart->fresh()->load('items');
        });
    }

    public function updateItemQuantity(int $itemId): Cart
    {
        return DB::transaction(function () use ($itemId) {
            $cart = $this->getOrCreateActiveCart();

            $this->assertExpectedCart($cart);

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $item = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->lockForUpdate()
                ->firstOrFail();

            $item->quantity = $this->quantity;
            $item->total = $this->calcLineTotal($item->unit_price, $item->quantity, $item->discount, $item->tax);
            $item->save();

            $this->recalcTotals($cart);

            return $cart->fresh()->load('items');
        });
    }

    public function removeItem(int $itemId): Cart
    {
        return DB::transaction(function () use ($itemId) {
            $cart = $this->getOrCreateActiveCart();

            $this->assertExpectedCart($cart);

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->delete();

            $this->recalcTotals($cart);

            return $cart->fresh();
        });
    }

    public function claimGuestCart(): Cart
    {
        if (!$this->claimCartToken) {
            throw ValidationException::withMessages([
                'cart_token' => 'cart_token is required.',
            ]);
        }
        if (!$this->buyerId) {
            throw ValidationException::withMessages([
                'buyer_id' => 'buyer_id is required.',
            ]);
        }

        $cartToken = $this->claimCartToken;
        $buyerId = $this->buyerId;

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
                    'buyer_id' => $buyerId
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

    private function getOrCreateActiveCart(): Cart
    {
        $q = Cart::query()->where('status', 'active');

        // Token-first: إذا موجود توكن استخدمه حتى لو buyerId موجود
        if ($this->cartToken) {
            $q->where('cart_token', $this->cartToken);
        } elseif ($this->buyerId) {
            $q->where('buyer_id', $this->buyerId);
        } else {
            throw ValidationException::withMessages([
                'cart_token' => 'cart_token is required for guest cart.',
            ]);
        }

        $cart = $q->latest('id')->first();

        if ($cart) {
            if ($this->buyerId && is_null($cart->buyer_id)) {
                $cart->update(['buyer_id' => $this->buyerId]);
            }
            return $cart;
        }

        return Cart::create([
            'buyer_id'   => $this->buyerId,
            'seller_id'  => $this->sellerId,
            'cart_token' => $this->cartToken,
            'status'     => 'active',
        ]);
    }

    private function assertExpectedCart(Cart $cart): void
    {
        if ($this->expectedCartId !== null && $cart->id !== $this->expectedCartId) {
            throw ValidationException::withMessages([
                'cart_id' => 'Provided cart_id does not match the active cart.',
            ]);
        }
    }

    private function requireItemData(): void
    {
        if (!$this->productId) {
            throw ValidationException::withMessages([
                'product_id' => 'product_id is required.',
            ]);
        }
        if ($this->quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'quantity must be at least 1.',
            ]);
        }
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



    public function incrementItemById(int $cartItemId): array
    {
        return DB::transaction(function () use ($cartItemId) {
            $cart = $this->getActiveCartByExpectedIdOrFail();

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $item = CartItem::where('cart_id', $cart->id)
                ->where('id', $cartItemId)
                ->lockForUpdate()
                ->firstOrFail();

            $item->quantity += 1;
            $item->total = $this->calcLineTotal($item->unit_price, $item->quantity, $item->discount, $item->tax);
            $item->save();

            $this->recalcTotals($cart);
            $cart = $cart->fresh(); // أحدث قيم totals

            return [
                'id' => $cart->id,
                'buyer_id' => $cart->buyer_id,
                'seller_id' => $cart->seller_id,
                'cart_token' => $cart->cart_token,
                'status' => $cart->status,
                'subtotal' => (float) $cart->subtotal,
                'tax_amount' => (float) $cart->tax_amount,
                'discount_amount' => (float) $cart->discount_amount,
                'shipping_amount' => (float) $cart->shipping_amount,
                'total' => (float) $cart->total,
                'notes' => $cart->notes,
                'expires_at' => $cart->expires_at,
                'converted_order_id' => $cart->converted_order_id,
                'item' => $this->itemPayload($item->fresh()),
            ];
        });
    }

    public function decrementItemById(int $cartItemId): array
    {
        return DB::transaction(function () use ($cartItemId) {
            $cart = $this->getActiveCartByExpectedIdOrFail();

            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $item = CartItem::where('cart_id', $cart->id)
                ->where('id', $cartItemId)
                ->lockForUpdate()
                ->firstOrFail();

            $item->quantity -= 1;

            $returnedItem = null;

            if ($item->quantity <= 0) {
                $item->delete();
            } else {
                $item->total = $this->calcLineTotal($item->unit_price, $item->quantity, $item->discount, $item->tax);
                $item->save();
                $returnedItem = $item->fresh();
            }

            $this->recalcTotals($cart);
            $cart = $cart->fresh();

            return [
                'id' => $cart->id,
                'buyer_id' => $cart->buyer_id,
                'seller_id' => $cart->seller_id,
                'cart_token' => $cart->cart_token,
                'status' => $cart->status,
                'subtotal' => (float) $cart->subtotal,
                'tax_amount' => (float) $cart->tax_amount,
                'discount_amount' => (float) $cart->discount_amount,
                'shipping_amount' => (float) $cart->shipping_amount,
                'total' => (float) $cart->total,
                'notes' => $cart->notes,
                'expires_at' => $cart->expires_at,
                'converted_order_id' => $cart->converted_order_id,
                'item' => $returnedItem ? $this->itemPayload($returnedItem) : null,
            ];
        });
    }


    private function getActiveCartByExpectedIdOrFail(): Cart
    {
        if (!$this->expectedCartId) {
            throw ValidationException::withMessages(['cart_id' => 'cart_id is required.']);
        }

        $q = Cart::query()
            ->where('id', $this->expectedCartId)
            ->where('status', 'active')
            ->lockForUpdate();

        // حماية الملكية
        if ($this->cartToken) {
            $q->where('cart_token', $this->cartToken);
        } elseif ($this->buyerId) {
            $q->where('buyer_id', $this->buyerId);
        } else {
            throw ValidationException::withMessages([
                'cart_token' => 'cart_token is required for guest cart.',
            ]);
        }

        $cart = $q->first();

        if (!$cart) {
            throw ValidationException::withMessages(['cart' => 'No active cart found for this cart_id.']);
        }

        // لو كان ضيف ثم سجل دخول (اختياري)
        if ($this->buyerId && is_null($cart->buyer_id)) {
            $cart->update(['buyer_id' => $this->buyerId]);
        }

        return $cart;
    }

    private function cartSummary(Cart $cart): array
    {
        return [
            'id' => $cart->id,
            'buyer_id' => $cart->buyer_id,
            'seller_id' => $cart->seller_id,
            'cart_token' => $cart->cart_token,
            'status' => $cart->status,
            'subtotal' => (float) $cart->subtotal,
            'discount_amount' => (float) $cart->discount_amount,
            'tax_amount' => (float) $cart->tax_amount,
            'shipping_amount' => (float) $cart->shipping_amount,
            'total' => (float) $cart->total,
        ];
    }

    private function itemPayload(CartItem $i): array
    {
        return [
            'id' => $i->id,
            'product_id' => $i->product_id,
            'variant_id' => $i->variant_id,
            'product_vendor_sku_id' => $i->product_vendor_sku_id,
            'product_vendor_sku_unit_id' => $i->product_vendor_sku_unit_id,
            'unit_id' => $i->unit_id,
            'sku' => $i->sku,
            'package_size' => (int) $i->package_size,
            'quantity' => (int) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'discount' => (float) $i->discount,
            'tax' => (float) $i->tax,
            'total' => (float) $i->total,
            'notes' => $i->notes,
        ];
    }



}

