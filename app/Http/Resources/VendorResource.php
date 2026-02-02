<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'slug'                   => $this->slug,
            'contact_person'         => $this->contact_person,
            'email'                  => $this->email,
            'phone'                  => $this->phone,
            'vat_id'                 => $this->vat_id,
            'status'                 => $this->status,
            'description'            => $this->description,
            'latitude'               => $this->latitude,
            'longitude'              => $this->longitude,
            'delivery_rate_per_km'   => $this->delivery_rate_per_km,
            'min_delivery_charge'    => $this->min_delivery_charge,
            'max_delivery_distance'  => $this->max_delivery_distance,
            'default_currency_id'    => $this->default_currency_id,
            'logo'    => $this->logo_url,
            'products_count' => $this->when(
                $this->offsetExists('products_count'),
                (int) $this->products_count,
                0
            ),
            'branches' => VendorResource::collection($this->whenLoaded('branches')),

        ];
    }
}
