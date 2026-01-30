<?php

declare(strict_types=1);

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
        // Get aggregated sales summary
        Route::get('summary', [SalesReportController::class, 'summary'])
            ->name('summary');

        // GET /api/v1/reports/sales/by-vendor
        // Get sales breakdown by vendor/merchant
        Route::get('by-vendor', [SalesReportController::class, 'byVendor'])
            ->name('by-vendor');

        // GET /api/v1/reports/sales/vendor/{vendorId}
        // Get specific vendor sales summary
        Route::get('vendor/{vendorId}', [SalesReportController::class, 'vendorSummary'])
            ->where('vendorId', '[0-9]+')
            ->name('vendor-summary');

        // GET /api/v1/reports/sales/trends
        // Get sales trends over time (daily/weekly/monthly)
        Route::get('trends', [SalesReportController::class, 'trends'])
            ->name('trends');

        // GET /api/v1/reports/sales/top-products
        // Get top selling products
        Route::get('top-products', [SalesReportController::class, 'topProducts'])
            ->name('top-products');

        // GET /api/v1/reports/sales/compare
        // Compare two time periods
        Route::get('compare', [SalesReportController::class, 'compare'])
            ->name('compare');

        // GET /api/v1/reports/sales/customer-metrics
        // Get customer acquisition and retention metrics
        Route::get('customer-metrics', [SalesReportController::class, 'customerMetrics'])
            ->name('customer-metrics');

        // GET /api/v1/reports/sales/dashboard
        // Get comprehensive dashboard data (all reports combined)
        Route::get('dashboard', [SalesReportController::class, 'dashboard'])
            ->name('dashboard');
    });
});
