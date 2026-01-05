<?php

use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\DocsReports\BranchProposalsController;
use App\Http\Controllers\DocsReports\DefaultUnitReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});


Route::get('/products/{slug}', [ProductShowController::class, 'show'])->name('products.show');

// Documentation Pages
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/default-unit-report', [DefaultUnitReportController::class, 'index'])->name('default-unit-report');
    Route::get('/branch-proposals', [BranchProposalsController::class, 'index'])->name('branch-proposals');
});

// Language Switcher
Route::post('/locale/switch', function (Illuminate\Http\Request $request) {
    $locale = $request->input('locale', 'en');

    if (in_array($locale, ['ar', 'en'])) {
        session()->put('locale', $locale);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('locale.switch');
