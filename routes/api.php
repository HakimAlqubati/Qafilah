<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [LoginController::class, 'me'])->middleware('auth:sanctum');
});
