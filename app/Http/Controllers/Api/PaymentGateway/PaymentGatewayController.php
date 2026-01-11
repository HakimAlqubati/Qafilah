<?php

namespace App\Http\Controllers\Api\PaymentGateway;
use App\Http\Resources\PaymentGatewayResource;
use App\Repositories\PaymentGateway\PaymentGatewayRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// Controller
class PaymentGatewayController extends Controller
{
    public function __construct(private PaymentGatewayRepository $repo) {}

    // GET /api/payment-gateways/live
    public function live()
    {
        $gateways = $this->repo->getLive();

        return PaymentGatewayResource::collection($gateways);
    }
}

