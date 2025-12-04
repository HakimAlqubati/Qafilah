<?php
namespace App\Http\Controllers\Api\Ecommerce;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class ProductController extends  ApiController
{
    public function index(Request $request)
    {
         $perPage = $request->integer('per_page', 10);

        $products = Product::query()
            ->with(['media', 'variants.media'])
            ->active()
            ->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            return new \App\Http\Resources\ProductResource($product);
        });

        return $this->successResponse($products, "");
     }

    public function show($id)
    {
        $product = Product::with(['media', 'variants.media', 'variants.variantValues', 'attributesDirect' => function($query) {
                $query->with('values')->orderByPivot('sort_order');
            }])
            ->active()
            ->findOrFail($id);

        return $this->successResponse(new \App\Http\Resources\ProductResource($product), "");
    }

    public function details($id)
    {

        $product = Product::with([
            'media',
            'attributesDirect' => function($query) {
                $query->with('values')->orderByPivot('sort_order');
            },
            'variants.media',
            'variants.variantValues',

        ])
        ->active()
        ->findOrFail($id);

        return $this->successResponse(new \App\Http\Resources\ProductDetailsResource($product), "");
    }

    public function vendorPrices($id, $vendorId)
    {
        $productVendorSkus = \App\Models\ProductVendorSku::where('product_id', $id)
            ->where('vendor_id', $vendorId)
            ->with(['variant', 'units' => function($query) {
                $query->active()->orderBy('sort_order');
            }, 'units.unit'])
            ->get();

        return $this->successResponse(\App\Http\Resources\ProductVendorSkuResource::collection($productVendorSkus), "");
    }
}
