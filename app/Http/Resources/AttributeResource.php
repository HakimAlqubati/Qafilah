<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
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
            'code' => $this->code,
            'type' => $this->type,
            // Include pivot data if available
            'value' => $this->whenPivotLoaded('product_set_attributes', function () {
                 // If there's specific value logic in pivot, add it here.
                 // For now, returning the pivot data itself might be useful or just specific fields
                 return $this->pivot;
            }),
            'values' => AttributeValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
