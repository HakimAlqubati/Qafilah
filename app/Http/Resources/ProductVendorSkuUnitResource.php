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
            'unit_name' => $this->unit_name,
            'unit_id' => $this->unit_id,
            'package_size' => $this->package_size,
            'selling_price' => $this->selling_price,
            'cost_price' => $this->cost_price,
            'stock' => $this->stock,
            'sort_order' => $this->sort_order,
            'moq' => $this->moq,
            'is_default' => $this->is_default,
//            'unit'  => new ProductUnitResource($this->whenLoaded('unit')),
            'vendor' => new VendorResource($this->whenLoaded('productVendorSku')?->vendor),
        ];

    }
}
