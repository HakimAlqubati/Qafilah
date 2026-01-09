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
            'unit_id' => $this->unit_id,
            'product_vendor_sku_id'=> $this->product_vendor_sku_id,
            'unit_name' => $this->unit?->name,
            'package_size' => $this->package_size,
            'selling_price' => $this->selling_price,
            'cost_price' => $this->cost_price,
            'stock' => $this->stock,
            'sort_order' => $this->sort_order,
            'moq' => $this->moq,
            'is_default' => $this->is_default,
//            'unit' => $this->whenLoaded('unit', fn() => [
//                'id' => $this->unit->id,
//                'name' => $this->unit->name,
//                'symbol' => $this->unit->symbol ?? null,
//            ]),
//            'units' => $this->whenLoaded('unit', function () {
//                return $this->unit->map(fn ($u) => [
//                    'id'            => $u->id,
//                    'name'          => $u->name,
//                    'is_default'     => $u->is_default,
//                    'vendors_count' => $u->vendors_count,
//                ]);
//            }),
        ];

    }
}
