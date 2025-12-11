<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Api\ApiController;
use App\Models\ProductVendorSku;
use App\Models\ProductVendorSkuUnit;
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

    public function getVendorCount(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'id' => 'required|exists:product_variants,id'
        ]);


        $count = ProductVendorSku::available()
            ->where('product_id', $request->product_id)
            ->where('variant_id', $request->id)
            ->distinct('vendor_id')
            ->count('vendor_id');

        return $this->successResponse(['count' => $count], "Vendor count retrieved successfully");
    }

    public function getVendorProductPrices(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'id' => 'required|exists:product_variants,id'
        ]);

        $variantId= $request->id;
        $productId = $request->product_id;

        $prices =  ProductVendorSkuUnit::whereHas('productVendorSku', function ($query) use ($variantId, $productId) {
            $query->where('variant_id', $variantId)
                  ->where('product_id', $productId);
        })
            ->with([
                'unit',
                'productVendorSku.vendor',
            ])
            ->paginate(10);


        return $this->successResponse(\App\Http\Resources\ProductVendorSkuUnitResource::collection($prices)->response()->getData(true), "Vendor prices retrieved successfully");
    }
}
