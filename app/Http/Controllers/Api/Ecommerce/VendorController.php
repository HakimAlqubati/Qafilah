<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Api\ApiController;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Resources\VendorResource;

class VendorController extends ApiController
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $sortDir = strtolower((string) $request->get('sort_dir', 'desc'));
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $allowedSorts = [
            'created_at'           => 'created_at',
            'name'                 => 'name',
            'delivery_rate_per_km' => 'delivery_rate_per_km',
            'min_delivery_charge'  => 'min_delivery_charge',
            'distance'             => 'distance',
        ];

        $sortBy = (string) $request->get('sort_by', 'created_at');
        $sortBy = $allowedSorts[$sortBy] ?? 'created_at';

        $applyRange = function ($query, string $column, $min, $max) {
            $hasMin = $min !== null && $min !== '';
            $hasMax = $max !== null && $max !== '';

            if ($hasMin && $hasMax) {
                $query->whereBetween($column, [$min, $max]);
            } elseif ($hasMin) {
                $query->where($column, '>=', $min);
            } elseif ($hasMax) {
                $query->where($column, '<=', $max);
            }

            return $query;
        };

        $hasCoords = $request->filled('latitude') && $request->filled('longitude');
        $lat = $hasCoords ? (float) $request->latitude : null;
        $lng = $hasCoords ? (float) $request->longitude : null;

        $haversine = "(6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
        + sin(radians(?)) * sin(radians(latitude))
    ))";

        $vendors = Vendor::query()
            ->where('status', 'active')
            ->whereNull('parent_id'); // ✅ فقط التجار الرئيسيين

        // range filters على الرئيسي
        $applyRange($vendors, 'delivery_rate_per_km', $request->delivery_rate_per_km_min, $request->delivery_rate_per_km_max);
        $applyRange($vendors, 'min_delivery_charge', $request->min_delivery_charge_min, $request->min_delivery_charge_max);

        // تحميل الأبناء بنفس الفلاتر (وبالمسافة إذا موجودة)
        $vendors->with(['branches' => function ($q) use ($request, $applyRange, $hasCoords, $lat, $lng, $haversine, $sortBy, $sortDir) {
            $q->where('status', 'active');

            $applyRange($q, 'delivery_rate_per_km', $request->delivery_rate_per_km_min, $request->delivery_rate_per_km_max);
            $applyRange($q, 'min_delivery_charge', $request->min_delivery_charge_min, $request->min_delivery_charge_max);

            if ($hasCoords) {
                $q->addSelect('*')
                    ->selectRaw("{$haversine} as distance", [$lat, $lng, $lat]);

                $q->orderBy($sortBy === 'distance' ? 'distance' : $sortBy, $sortDir);
            } else {
                $q->orderBy($sortBy === 'distance' ? 'created_at' : $sortBy, $sortDir);
            }
        }]);

        // distance + sort على الرئيسي
        if ($hasCoords) {
            $vendors->addSelect('*')
                ->selectRaw("{$haversine} as distance", [$lat, $lng, $lat]);

            $vendors->orderBy($sortBy === 'distance' ? 'distance' : $sortBy, $sortDir);
        } else {
            $vendors->orderBy($sortBy === 'distance' ? 'created_at' : $sortBy, $sortDir);
        }

        $vendorsData = $vendors->paginate($perPage);

        return $this->successResponse(
            VendorResource::collection($vendorsData)->response()->getData(true),
            "Vendors retrieved successfully"
        );
    }

}
