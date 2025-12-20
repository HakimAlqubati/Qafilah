<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * يُرجع بيانات البائع مع الوحدات والأسعار الخاصة به
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vendor = $this->whenLoaded('vendor');
        
        if ($vendor) {
            return [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'slug' => $this->vendor->slug,
                'phone' => $this->vendor->phone,
                'email' => $this->vendor->email,
                'logo' => $this->vendor->logo_url,
                'latitude' => $this->vendor->latitude,
                'longitude' => $this->vendor->longitude,
                'units' => ProductVendorSkuUnitResource::collection($this->whenLoaded('units')),
            ];
        }

        return [];
    }
}
