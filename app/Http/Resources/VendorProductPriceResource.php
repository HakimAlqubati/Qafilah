<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProductPriceResource extends JsonResource
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
            'vendor_sku' => $this->vendor_sku,
            'status' => $this->status,
            'is_default_offer' => $this->is_default_offer,
            'product' => new ProductResource($this->whenLoaded('product')),
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->name,
                    'parent_id' => $this->vendor->parent_id,
                    'country_id' => $this->vendor->country_id,
                    'city_id' => $this->vendor->city_id,
                    'district_id' => $this->vendor->district_id,
                    'contact_person' => $this->vendor->contact_person,
                    'email' => $this->vendor->email,
                    'phone' => $this->vendor->phone,
                    'vat_id' => $this->vendor->vat_id,
                    'status' => $this->vendor->status,
                    'logo_path' => $this->vendor->logo_path,
                    'latitude' => $this->vendor->latitude,
                    'longitude' => $this->vendor->longitude,
                    'delivery_rate_per_km' => $this->vendor->delivery_rate_per_km,
                    'min_delivery_charge' => $this->vendor->min_delivery_charge,
                    'max_delivery_distance' => $this->vendor->max_delivery_distance,
                    'delivery_time_value' => $this->vendor->delivery_time_value,
                    'delivery_time_unit' => $this->vendor->delivery_time_unit,
                ];
            }),

            'product_vendor_sku_unit' => $this->whenLoaded('productVendorSkuUnits', function () {
                $productVendorSkuUnits = $this->productVendorSkuUnits->first();
                return $productVendorSkuUnits ? new ProductVendorSkuUnitResource($productVendorSkuUnits) : null;
            }),
        ];
    }
}
