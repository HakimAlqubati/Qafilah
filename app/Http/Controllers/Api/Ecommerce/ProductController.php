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
            ->with('media')
            ->active()
            ->paginate($perPage);

        return $this->successResponse($products, "");
     }
}
