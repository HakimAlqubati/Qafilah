<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVendorSkuResource;
use App\Models\Product;
use App\Models\ProductVendorSku;
use App\Models\ProductVendorSkuUnit;
use App\Http\Resources\SliderResource;
use App\Models\Slider;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class ProductController extends  ApiController
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $productsIds = $request->input('products_id');
        $isFeatured = filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOL);
        $isLast     = filter_var($request->input('is_last'), FILTER_VALIDATE_BOOL);

        $query = Product::query()
            ->with(['media'])
            ->active();

        if (is_array($productsIds) && count($productsIds)) {
            $ids = array_values(array_filter(array_map('intval', $productsIds)));
            if (count($ids)) {
                $query->whereIn('id', $ids);
                $query->orderByRaw('FIELD(id,' . implode(',', $ids) . ')');
            }
        }
        if ($isFeatured) {
            $query->where('is_featured', 1);
        }

        // 3) is_last (latest 10)
        if ($isLast) {
            $perPage = 10; // force last 10
            $query->latest('id'); // أو created_at لو عندك أدق
        }

        $products = $query->paginate($perPage);

        $products->getCollection()->transform(fn ($product) => new \App\Http\Resources\ProductResource($product));

        return $this->successResponse($products, "");
    }



    public function vendorPrices($id, $vendorId)
    {
        $productVendorSkus = \App\Models\ProductVendorSku::where('product_id', $id)
            ->where('vendor_id', $vendorId)
            ->with(['variant', 'units' => function ($query) {
                $query->active()->orderBy('sort_order');
            }, 'units.unit'])
            ->get();

        return $this->successResponse(\App\Http\Resources\ProductVendorSkuResource::collection($productVendorSkus), "");
    }

    public function productDetails(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'vendor_id'  => 'nullable|exists:vendors,id',
        ]);

        $productId = $request->product_id;
        $vendorId  = $request->vendor_id;
        if (!empty($vendorId)) {
            $skus = ProductVendorSku::query()
                ->where('product_id', $productId)
                ->where('vendor_id', $vendorId)
                ->where('status', ProductVendorSku::$STATUSES['AVAILABLE'])
                ->with(['productVendorSkuUnits.unit'])
                ->get();

            return $this->successResponse(
                ProductVendorSkuResource::collection($skus),
                "Vendor product SKUs retrieved successfully"
            );
        }
        $product = Product::findOrFail($productId);

        $units = Unit::query()
            ->join('product_vendor_sku_units as pvsu', 'pvsu.unit_id', '=', 'units.id')
            ->join('product_vendor_skus as pvs', 'pvs.id', '=', 'pvsu.product_vendor_sku_id')
            ->where('pvs.product_id', $productId)
            ->where('pvs.status', ProductVendorSku::$STATUSES['AVAILABLE'])
            ->select(['units.id', 'units.name', 'units.is_default'])
            ->selectRaw('COUNT(DISTINCT pvs.vendor_id) as vendors_count')
            ->groupBy('units.id', 'units.name', 'units.is_default')
            ->orderBy('units.id')
            ->get();

        $product->setRelation('units', $units);

        return $this->successResponse(
            new ProductResource($product),
            "Vendor product details retrieved successfully"
        );
    }


    public function slider(Request $request)
    {
        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['product'])
            ->get();

        return $this->successResponse(
            SliderResource::collection($sliders),
            "Sliders retrieved successfully"
        );
    }
}
