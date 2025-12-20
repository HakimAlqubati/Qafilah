<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
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
            'slug' => $this->slug,
            'images' => $this->getMedia()->map(function ($media) {
                return $media->getUrl();
            }),

            // Attributes (Static values assigned to the product)

            'attributes' => AttributeResource::collection($this->whenLoaded('attributesDirect')),

            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),

            // إرجاع عروض البائعين المباشرة فقط إذا كانت المتغيرات فارغة
            'vendor_offers' => $this->when(
                $this->relationLoaded('variants') && $this->variants->isEmpty() && $this->relationLoaded('offers'),
                fn() => ProductVendorSkuResource::collection($this->offers)
            ),
        ];
    }
}
