<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\BasicDataController;
use App\Http\Controllers\Api\Ecommerce\ProductController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [LoginController::class, 'me'])->middleware('auth:sanctum');
});
Route::prefix('v1')->group(function () {

});
Route::prefix('v1/ecommerce')->group(function () {
    Route::prefix('sync')->group(function () {
        Route::get('basic-data', [BasicDataController::class, 'index']);
        Route::post('categories',       [BasicDataController::class, 'categories']);
        Route::post('attributes',       [BasicDataController::class, 'attributes']);
        Route::post('attribute-values', [BasicDataController::class, 'attributeValues']);
        Route::post('units',            [BasicDataController::class, 'units']);
    });
    Route::post('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/{id}/details', [ProductController::class, 'details']);
    Route::get('/products/{id}/vendor/{vendor_id}/prices', [ProductController::class, 'vendorPrices']);
    Route::get('/vendor-products/{id}', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'show']);
    Route::get('/vendors/{vendor_id}/products', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'index']);
    Route::post('/products/vendor-count', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'getVendorCount']);
    Route::post('/products/vendor-prices', [\App\Http\Controllers\Api\Ecommerce\VendorProductController::class, 'getVendorProductPrices']);
});
