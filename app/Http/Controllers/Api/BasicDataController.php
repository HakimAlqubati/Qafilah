<?php

namespace App\Http\Controllers\Api;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
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
        // فقط الفئات الـ active
        $query = Category::active();

        // لو تريد فقط الرئيسية مع الأبناء، أضِف:
        // $query->whereNull('parent_id')->with(['children' => fn ($q) => $q->active()]);

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

    }
}
