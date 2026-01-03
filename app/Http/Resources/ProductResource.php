<?php

namespace App\Http\Resources;

use App\Filament\Resources\Units\UnitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'label_attribute' => $this->label_attribute,
            'images' => $this->getMedia()->map(function ($media) {
                return $media->getUrl();
            }),
            'units' => UnitResource::collection($this->whenLoaded('units')),
//            'attributes' => AttributeResource::collection($this->whenLoaded('attributesDirect')),
//            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
