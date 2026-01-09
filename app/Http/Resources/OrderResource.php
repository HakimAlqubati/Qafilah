<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'vendor_id' => $this->vendor_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'shipping_status' => $this->shipping_status,

            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'total' => (float) $this->total,

            'shipping_address_id' => $this->shipping_address_id,
            'billing_address_id' => $this->billing_address_id,
            'notes' => $this->notes,
            'placed_at' => optional($this->placed_at)->toISOString(),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
