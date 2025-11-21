<?php

use App\Http\Controllers\ProductShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin'); 
});


Route::get('/products/{slug}', [ProductShowController::class, 'show'])->name('products.show');
