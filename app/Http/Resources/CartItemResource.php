<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'product_vendor_sku_id' => $this->product_vendor_sku_id,
            'product_vendor_sku_unit_id' => $this->product_vendor_sku_unit_id,
            'unit_id' => $this->unit_id,
            'sku' => $this->sku,
            'package_size' => $this->package_size,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'product_name' => $this->product?->name,
            'product_image' => $this->product?->getDefaultImageUrl(),
        ];
    }
}
