<?php

use App\Http\Controllers\ProductShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});


Route::get('/products/{slug}', [ProductShowController::class, 'show'])->name('products.show');

// Language Switcher
Route::post('/locale/switch', function (Illuminate\Http\Request $request) {
    $locale = $request->input('locale', 'en');

    if (in_array($locale, ['ar', 'en'])) {
        session()->put('locale', $locale);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('locale.switch');
