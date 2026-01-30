<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Reports\ProductsReportController;
use App\Http\Controllers\Api\Reports\SalesReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reports API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for all report-related endpoints.
| All routes are prefixed with 'api/v1/reports' and require authentication.
|
*/

Route::prefix('reports')->name('reports.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Sales Reports
    |--------------------------------------------------------------------------
    */
    Route::prefix('sales')->name('sales.')->group(function () {

        // GET /api/v1/reports/sales/summary
        Route::get('summary', [SalesReportController::class, 'summary'])
            ->name('summary');

        // GET /api/v1/reports/sales/by-vendor
        Route::get('by-vendor', [SalesReportController::class, 'byVendor'])
            ->name('by-vendor');

        // GET /api/v1/reports/sales/vendor/{vendorId}
        Route::get('vendor/{vendorId}', [SalesReportController::class, 'vendorSummary'])
            ->where('vendorId', '[0-9]+')
            ->name('vendor-summary');

        // GET /api/v1/reports/sales/trends
        Route::get('trends', [SalesReportController::class, 'trends'])
            ->name('trends');

        // GET /api/v1/reports/sales/top-products
        Route::get('top-products', [SalesReportController::class, 'topProducts'])
            ->name('top-products');

        // GET /api/v1/reports/sales/compare
        Route::get('compare', [SalesReportController::class, 'compare'])
            ->name('compare');

        // GET /api/v1/reports/sales/customer-metrics
        Route::get('customer-metrics', [SalesReportController::class, 'customerMetrics'])
            ->name('customer-metrics');

        // GET /api/v1/reports/sales/dashboard
        Route::get('dashboard', [SalesReportController::class, 'dashboard'])
            ->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Products Reports - تقرير أفضل المنتجات مبيعاً
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->name('products.')->group(function () {

        // GET /api/v1/reports/products/top
        // Get top selling products with full details
        Route::get('top', [ProductsReportController::class, 'topProducts'])
            ->name('top');

        // GET /api/v1/reports/products/summary
        // Get products summary statistics
        Route::get('summary', [ProductsReportController::class, 'summary'])
            ->name('summary');

        // GET /api/v1/reports/products/by-category
        // Get products breakdown by category
        Route::get('by-category', [ProductsReportController::class, 'byCategory'])
            ->name('by-category');

        // GET /api/v1/reports/products/by-vendor
        // Get products breakdown by vendor
        Route::get('by-vendor', [ProductsReportController::class, 'byVendor'])
            ->name('by-vendor');

        // GET /api/v1/reports/products/trends
        // Get product sales trends over time
        Route::get('trends', [ProductsReportController::class, 'trends'])
            ->name('trends');

        // GET /api/v1/reports/products/slow-moving
        // Get slow moving products (lowest sales)
        Route::get('slow-moving', [ProductsReportController::class, 'slowMoving'])
            ->name('slow-moving');

        // GET /api/v1/reports/products/compare
        // Compare product performance between periods
        Route::get('compare', [ProductsReportController::class, 'compare'])
            ->name('compare');

        // GET /api/v1/reports/products/dashboard
        // Get comprehensive products dashboard
        Route::get('dashboard', [ProductsReportController::class, 'dashboard'])
            ->name('dashboard');
    });
});
