<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gateway_id' => $this->gateway_id,
            'reference_id' => $this->reference_id,
            'has_otp' => $this->supportsOtp(),
        ];
    }
}
