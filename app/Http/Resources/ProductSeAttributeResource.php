<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSeAttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'attribute_id' => $this->attribute_id,
            'is_variant_option' => $this->is_variant_option,
            'sort_order' => $this->sort_order,
            'attribute' => new AttributeResource($this->whenLoaded('attribute')),
        ];
    }
}
