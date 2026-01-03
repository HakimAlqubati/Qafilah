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

//            'attributes' => AttributeResource::collection($this->whenLoaded('attributesDirect')),
//
//            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),

            // إرجاع عروض البائعين المباشرة فقط إذا كانت المتغيرات فارغة و offers محملة
//            'vendor_offers' => $this->when(
//                $this->relationLoaded('offers') &&
//                (!$this->relationLoaded('variants') || $this->variants->isEmpty()),
//                fn() => ProductVendorSkuResource::collection($this->offers)
//            ),
            'units_breakdown' => $this->when(isset($this->units_breakdown), function () {
                return $this->units_breakdown;
            }),
        ];
    }
}
