<?php

use App\Http\Controllers\Api\Address\CustomerAddressController;
use App\Http\Controllers\Api\Ecommerce\OrderController;
use App\Http\Controllers\Api\PaymentGateway\PaymentGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\BasicDataController;
use App\Http\Controllers\Api\Ecommerce\ProductController;
use App\Http\Controllers\Api\Ecommerce\CartController;
use App\Http\Controllers\Api\Ecommerce\CheckoutController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [LoginController::class, 'me'])->middleware('auth:sanctum');
});
Route::prefix('v1')->group(function () {
    // Reports API Routes
    require __DIR__ . '/api/reports.php';
});
Route::prefix('v1/ecommerce')->group(function () {
    Route::prefix('sync')->group(function () {
        Route::get('basic-data', [BasicDataController::class, 'index']);
        Route::post('categories',       [BasicDataController::class, 'categories']);
        Route::post('attributes',       [BasicDataController::class, 'attributes']);
        Route::post('attribute-values', [BasicDataController::class, 'attributeValues']);
        Route::post('units',            [BasicDataController::class, 'units']);
        Route::post('currencies',            [BasicDataController::class, 'currencies']);
    });
    Route::post('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}/vendor/{vendor_id}/prices', [ProductController::class, 'vendorPrices']);
    Route::get('/vendor-products/{id}', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'show']);
    Route::post('/vendors', [\App\Http\Controllers\Api\Ecommerce\VendorController::class, 'index']);
    Route::get('/vendors/{vendor_id}/products', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'index']);
    Route::post('/products/vendor-count', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'getVendorCount']);
    Route::post('/products/vendor-prices', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'getVendorProductPrices']);
    Route::post('/products/details', [ProductController::class, 'productDetails']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/orders', [OrderController::class, 'index']);
        Route::post('/orders/{id}', [OrderController::class, 'show']);
    });

    Route::middleware('optional.sanctum')->group(function () {
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'show']);
            Route::post('/add', [CartController::class, 'addItem']);
            Route::post('/update', [CartController::class, 'updateItem']);
            Route::post('/delete', [CartController::class, 'removeItem']);
            Route::post('/inc', [CartController::class, 'incItem']); // +
            Route::post('/dec', [CartController::class, 'decItem']); // -
            Route::middleware('auth:sanctum')->post('/claim', [CartController::class, 'claim']);
            Route::post('/checkout', [CheckoutController::class, 'checkout']);
        });
    });


    Route::get('payment-gateways', [PaymentGatewayController::class, 'live']);
    Route::middleware('auth:sanctum')->prefix('address')->group(function () {
        Route::get('/', [CustomerAddressController::class, 'index']);
        Route::post('/', [CustomerAddressController::class, 'store']);
        Route::put('{id}', [CustomerAddressController::class, 'update']);
        Route::delete('{id}', [CustomerAddressController::class, 'destroy']);
        Route::post('/default', [CustomerAddressController::class, 'setDefault']);
    });
});
