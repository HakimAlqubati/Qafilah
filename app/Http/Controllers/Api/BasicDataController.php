<?php

namespace App\Http\Controllers\Api;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class BasicDataController extends ApiController
{
    public function index(): JsonResponse
    {
//         $categories = Category::active()
//             ->orderBy('sort_order')
//            ->get();
//        $attributes = Attribute::active()
//            ->orderBy('sort_order')
//            ->get();
//        $attributeValues = AttributeValue::active()
//            ->orderBy('attribute_id')
//            ->orderBy('sort_order')
//            ->get();
        $currency = Currency::active()->get();
        $units = Unit::active()
            ->orderBy('name')
            ->get();

        return $this->successResponse([
//            'categories' => $categories,
//            'attributes' => $attributes,
            'currency' => $currency,
            'units' => $units,
            'shipping_status' => Order::STATUSES,
         ], 'Basic ecommerce data');
    }

    protected function syncByUpdatedAt(Request $request, Builder $query, string $message): JsonResponse
    {
        $request->validate([
            'updated_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        if ($request->filled('updated_at')) {
            $query->where('updated_at', '>', $request->input('updated_at'));
        }

        $items = $query->get();

        return $this->successResponse($items, $message);
    }

    public function categories(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $deviceToken = $request->header('x-device-token');
        if ($user && is_string($deviceToken)) {
            $deviceToken = trim($deviceToken);

            if ($deviceToken !== '' && $deviceToken !== 'null' && $deviceToken !== 'undefined') {
                if ($user->fcm_token !== $deviceToken) {
                    $user->forceFill([
                        'fcm_token' => $deviceToken,
                        'fcm_token_updated_at' => now(),
                    ])->save();
                }
            }
        }

        $query = Category::active();



        return $this->syncByUpdatedAt($request, $query, 'Categories sync data');
    }

    public function attributes(Request $request): JsonResponse
    {
        $query = Attribute::active()->orderBy('sort_order');

        return $this->syncByUpdatedAt($request, $query, 'Attributes sync data');
    }

    public function attributeValues(Request $request): JsonResponse
    {
        $query = AttributeValue::active()
            ->orderBy('sort_order');

        return $this->syncByUpdatedAt($request, $query, 'Attribute values sync data');
    }

    public function units(Request $request): JsonResponse
    {
        $query = Unit::active()->orderBy('sort_order');

        return $this->syncByUpdatedAt($request, $query, 'Units sync data');
    }
    public function currencies(Request $request): JsonResponse
    {
        $query = Currency::active();
        return $this->syncByUpdatedAt($request, $query, 'currencies sync data');
    }
    public function shippingStatus(Request $request): JsonResponse
    {
        return $this->successResponse(Order::STATUSES, 'Shipping statuses');
    }

    public function locations(Request $request): JsonResponse
    {
        return $this->successResponse([
            'countries' => Country::active()->get(),
            'cities' => City::active()->get(),
        ], 'Locations sync data');
    }
}
