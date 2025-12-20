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

        if (empty($vendorId)) {
            $product = Product::with([
                'media',
                'attributesDirect' => function ($query) {
                    $query->with('values')->orderByPivot('sort_order');
                },
                'variants.media',
                'variants.variantValues',
            ])
                ->active()
                ->findOrFail($productId);

            return $this->successResponse(
                new \App\Http\Resources\ProductDetailsResource($product),
                ""
            );
        }

         $vendorVariantIds = \App\Models\ProductVendorSku::where('product_id', $productId)
            ->where('vendor_id', $vendorId)
            ->available()
            ->whereNotNull('variant_id')
            ->pluck('variant_id')
            ->unique()
            ->toArray();

        $product = Product::with([
            'media',
            'offers' => function ($query) use ($vendorId) {
                $query->whereNull('variant_id')
                    ->where('vendor_id', $vendorId)
                    ->available()
                    ->with([
                        'units' => function ($q) {
                            $q->active()->orderBy('sort_order');
                        },
                        'units.unit'
                    ]);
            },
        ])
            ->active()
            ->findOrFail($productId);

        $vendorVariants = \App\Models\ProductVariant::whereIn('id', $vendorVariantIds)
            ->with([
                'media',
                'variantValues.attribute',
                'vendorOffers' => function ($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId)
                        ->available()
                        ->with([
                            'units' => function ($q) {
                                $q->active()->orderBy('sort_order');
                            },
                            'units.unit'
                        ]);
                }
            ])
            ->get();

        // استخراج attributes/values بشكل أنظف
        $vals = $vendorVariants->pluck('variantValues')->flatten();

        $attributeValueIds = $vals->pluck('id')->unique()->values()->all();
        $attributeIds = $vals->map(function ($v) {
            return $v->pivot->attribute_id ?? $v->attribute_id ?? null;
        })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $attributes = \App\Models\Attribute::whereIn('id', $attributeIds)
            ->with(['values' => function ($query) use ($attributeValueIds) {
                $query->whereIn('id', $attributeValueIds)->orderBy('sort_order');
            }])
            ->get();

        $product->setRelation('variants', $vendorVariants);
        $product->setRelation('attributesDirect', $attributes);

        return $this->successResponse(
            new \App\Http\Resources\ProductDetailsResource($product),
            "Vendor product details retrieved successfully"
        );
    }



//    public function vendorProductDetails(Request $request)
//    {
//        $request->validate([
//            'product_id' => 'required|exists:products,id',
//            'vendor_id' => 'required|exists:vendors,id',
//        ]);
//
//        $productId = $request->product_id;
//        $vendorId = $request->vendor_id;
//
//        // Get vendor's variant IDs
//        $vendorVariantIds = \App\Models\ProductVendorSku::where('product_id', $productId)
//            ->where('vendor_id', $vendorId)
//            ->available()
//            ->whereNotNull('variant_id')
//            ->pluck('variant_id')
//            ->unique()
//            ->toArray();
//
//        // Get product with media only (we'll add filtered variants and attributes)
//        $product = Product::with(['media'])
//            ->active()
//            ->findOrFail($productId);
//
//        // Get vendor's variants with their values and units
//        $vendorVariants = \App\Models\ProductVariant::whereIn('id', $vendorVariantIds)
//            ->with([
//                'media',
//                'variantValues.attribute', // Load attribute for each value
//                'vendorOffers' => function ($query) use ($vendorId) {
//                    $query->where('vendor_id', $vendorId)
//                        ->available()
//                        ->with([
//                            'units' => function ($q) {
//                                $q->active()->orderBy('sort_order');
//                            },
//                            'units.unit'
//                        ]);
//                }
//            ])
//            ->get();
//
//        // Extract unique attributes and their values from vendor's variant values
//        $attributeIds = [];
//        $attributeValueIds = [];
//        foreach ($vendorVariants as $variant) {
//            foreach ($variant->variantValues as $value) {
//                $attributeValueIds[] = $value->id;
//                if ($value->pivot && $value->pivot->attribute_id) {
//                    $attributeIds[] = $value->pivot->attribute_id;
//                } elseif ($value->attribute_id) {
//                    $attributeIds[] = $value->attribute_id;
//                }
//            }
//        }
//        $attributeIds = array_unique($attributeIds);
//        $attributeValueIds = array_unique($attributeValueIds);
//
//        // Get attributes with only the values that vendor has
//        $attributes = \App\Models\Attribute::whereIn('id', $attributeIds)
//            ->with(['values' => function ($query) use ($attributeValueIds) {
//                $query->whereIn('id', $attributeValueIds)->orderBy('sort_order');
//            }])
//            ->get();
//
//        // Set the filtered variants to product
//        $product->setRelation('variants', $vendorVariants);
//        $product->setRelation('attributesDirect', $attributes);
//
//        return $this->successResponse(new \App\Http\Resources\ProductDetailsResource($product), "Vendor product details retrieved successfully");
//    }
}
