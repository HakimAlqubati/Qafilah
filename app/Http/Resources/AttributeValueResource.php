<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attribute_value_id' => $this->id,
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'formatted_value' => $this->formattedValue(),
        ];
    }
}
