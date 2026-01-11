<?php

namespace App\Repositories\PaymentGateway;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Collection;

class PaymentGatewayRepository
{
    public function getLive(): Collection
    {
        return PaymentGateway::query()
            ->where('mode', PaymentGateway::MODE_LIVE)
            ->orderByDesc('id')
            ->get();
    }
}


