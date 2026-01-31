<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
            'name' => $this->name,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->getFirstMediaUrl('image'), 
            'sort_order' => $this->sort_order,
            'product_id' => $this->product_id,
            'is_active' => (bool)$this->is_active,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
