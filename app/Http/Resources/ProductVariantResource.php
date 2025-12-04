<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'master_sku' => $this->master_sku,
            'barcode' => $this->barcode,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'status' => $this->status,
            'is_default' => $this->is_default,
            'images' => $this->getMedia('variant_images')->map(function ($media) {
                return $media->getUrl();
            }),
            'variant_values' => AttributeValueResource::collection($this->whenLoaded('variantValues')),
            'vendor_offers' => ProductVendorSkuResource::collection($this->whenLoaded('vendorOffers')),
        ];
    }
}
