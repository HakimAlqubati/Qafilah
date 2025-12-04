<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Api\ApiController;
use App\Models\ProductVendorSku;
use Illuminate\Http\Request;

class VendorProductController extends ApiController
{
    public function index(Request $request, $vendorId)
    {
        $perPage = $request->integer('per_page', 10);

        $products = \App\Models\Product::whereHas('offers', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })
            ->active()
            ->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            return new \App\Http\Resources\ProductResource($product);
        });

        return $this->successResponse($products, "");
    }

    public function show($id)
    {
        $productVendorSku = ProductVendorSku::with([
            'product.media',
            'product.variants.media',
            'product.variants.variantValues',
            'product.attributesDirect' => function ($query) {
                $query->with('values')->orderByPivot('sort_order');
            },
            'variant',
            'vendor'
        ])
            ->available()
            ->findOrFail($id);

        return $this->successResponse(new \App\Http\Resources\ProductVendorSkuResource($productVendorSku), "");
    }
}
