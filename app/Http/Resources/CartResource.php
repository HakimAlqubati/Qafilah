<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'cart_token' => $this->cart_token,
            'status' => $this->status,

            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'total' => (float) $this->total,

            'notes' => $this->notes,
            'expires_at' => optional($this->expires_at)->toISOString(),
            'converted_order_id' => $this->converted_order_id,

            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
