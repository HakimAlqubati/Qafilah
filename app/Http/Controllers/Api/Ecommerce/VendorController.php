<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Api\ApiController;
use App\Models\ProductVendorSku;
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

        // ✅ include_products_count=true => add products_count
        $includeProductsCount = $request->boolean('include_products_count');

        $vendors = Vendor::query()
            ->select('vendors.*')
            ->where('status', 'active')
            ->whereNull('parent_id');

        // ranges on parent vendors
        $applyRange($vendors, 'delivery_rate_per_km', $request->delivery_rate_per_km_min, $request->delivery_rate_per_km_max);
        $applyRange($vendors, 'min_delivery_charge',  $request->min_delivery_charge_min,  $request->min_delivery_charge_max);

        // ✅ only build and attach subquery when requested
        if ($includeProductsCount) {
            $vendors->addSelect([
                'products_count' => ProductVendorSku::query()
                    ->selectRaw('count(distinct product_id)')
                    ->whereColumn('vendor_id', 'vendors.id')
                    ->where('status', ProductVendorSku::$STATUSES['AVAILABLE']),
            ]);
        }

        $vendors->with(['branches' => function ($q) use (
            $request,
            $applyRange,
            $hasCoords,
            $lat,
            $lng,
            $haversine,
            $sortBy,
            $sortDir,
            $includeProductsCount
        ) {
            $q->select('vendors.*')
                ->where('status', 'active');

            $applyRange($q, 'delivery_rate_per_km', $request->delivery_rate_per_km_min, $request->delivery_rate_per_km_max);
            $applyRange($q, 'min_delivery_charge',  $request->min_delivery_charge_min,  $request->min_delivery_charge_max);

            // ✅ only attach subquery for branches when requested
            if ($includeProductsCount) {
                $q->addSelect([
                    'products_count' => ProductVendorSku::query()
                        ->selectRaw('count(distinct product_id)')
                        ->whereColumn('vendor_id', 'vendors.id')
                        ->where('status', ProductVendorSku::$STATUSES['AVAILABLE']),
                ]);
            }

            if ($hasCoords) {
                $q->selectRaw("{$haversine} as distance", [$lat, $lng, $lat])
                    ->orderBy($sortBy === 'distance' ? 'distance' : $sortBy, $sortDir);
            } else {
                $q->orderBy($sortBy === 'distance' ? 'created_at' : $sortBy, $sortDir);
            }
        }]);

        // order vendors (parents)
        if ($hasCoords) {
            $vendors->selectRaw("{$haversine} as distance", [$lat, $lng, $lat])
                ->orderBy($sortBy === 'distance' ? 'distance' : $sortBy, $sortDir);
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
