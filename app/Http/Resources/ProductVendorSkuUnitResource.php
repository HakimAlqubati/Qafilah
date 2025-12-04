<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVendorSkuUnitResource extends JsonResource
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
            'unit_name' => $this->unit ? $this->unit->name : null,
            'package_size' => $this->package_size,
            'selling_price' => $this->selling_price,
            'stock' => $this->stock,
            'moq' => $this->moq,
            'is_default' => $this->is_default,
        ];
    }
}
