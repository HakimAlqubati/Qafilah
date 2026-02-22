<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVendorSkuResource;
use App\Models\Product;
use App\Models\ProductVendorSku;
use App\Models\ProductVendorSkuUnit;
use App\Models\Unit;
use Illuminate\Http\Request;

class VendorProductController extends ApiController
{
    public function index(Request $request, $vendorId)
    {
        $perPage = $request->integer('per_page', 10);

        $products = Product::whereHas('offers', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })
            ->active()
            ->paginate($perPage);

        $products->getCollection()->transform(function ($product) use ($vendorId) {
            $resource = new  ProductResource($product);
            $data = $resource->toArray(request());

            $skus = \App\Models\ProductVendorSku::where('vendor_id', $vendorId)
                ->where('product_id', $product->id)
                ->available()
                ->with(['productVendorSkuUnits.unit'])
                ->get();

            $data['product_vendor_skus'] = ProductVendorSkuResource::collection($skus);

            return $data;
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
            'variant_id' => 'nullable|exists:product_variants,id'
        ]);


        $count = ProductVendorSku::available()
            ->where('product_id', $request->product_id)
            ->where('variant_id', $request->variant_id)
            ->distinct('vendor_id')
            ->count('vendor_id');

        return $this->successResponse(['count' => $count], "Vendor count retrieved successfully");
    }

    public function getVendorProductPrices(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_id'    => 'nullable|exists:units,id',
        ]);

        $unitId = $request->unit_id
            ?? Unit::defaultActive()->value('id');

        $productId = $request->product_id;

        $vendorSkus = ProductVendorSku::where('product_id', $productId)
            ->available()
            ->when($unitId, function ($q) use ($unitId) {
                $q->whereHas('productVendorSkuUnits', function ($subQ) use ($unitId) {
                    $subQ->where('unit_id', $unitId)->active();
                });
            })
            ->with([
                'vendor',
                'productVendorSkuUnits' => function ($q) use ($unitId) {
                    $q->active()
                        ->when($unitId, fn ($qq) => $qq->where('unit_id', $unitId))
                        ->orderBy('sort_order')
                        ->with('unit')
                        ->limit(1);
                },
            ])
            ->paginate(10);

        return \App\Http\Resources\VendorProductPriceResource::collection($vendorSkus)->response()->getData(true);
    }
}
