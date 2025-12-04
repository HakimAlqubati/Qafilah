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
            'input_type' => $this->input_type,
            'type' => $this->type,
            // Include pivot data if available

            'values' => AttributeValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
