<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Reports\ProductsReportController;
use App\Http\Controllers\Api\Reports\SalesReportController;
use App\Http\Controllers\Api\Reports\VendorsReportController;
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
    | Sales Reports - تقرير المبيعات
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
        Route::get('top', [ProductsReportController::class, 'topProducts'])
            ->name('top');

        // GET /api/v1/reports/products/summary
        Route::get('summary', [ProductsReportController::class, 'summary'])
            ->name('summary');

        // GET /api/v1/reports/products/by-category
        Route::get('by-category', [ProductsReportController::class, 'byCategory'])
            ->name('by-category');

        // GET /api/v1/reports/products/by-vendor
        Route::get('by-vendor', [ProductsReportController::class, 'byVendor'])
            ->name('by-vendor');

        // GET /api/v1/reports/products/trends
        Route::get('trends', [ProductsReportController::class, 'trends'])
            ->name('trends');

        // GET /api/v1/reports/products/slow-moving
        Route::get('slow-moving', [ProductsReportController::class, 'slowMoving'])
            ->name('slow-moving');

        // GET /api/v1/reports/products/compare
        Route::get('compare', [ProductsReportController::class, 'compare'])
            ->name('compare');

        // GET /api/v1/reports/products/dashboard
        Route::get('dashboard', [ProductsReportController::class, 'dashboard'])
            ->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Vendors Reports - تقرير أداء الموردين
    |--------------------------------------------------------------------------
    */
    Route::prefix('vendors')->name('vendors.')->group(function () {

        // GET /api/v1/reports/vendors/top
        // Get top performing vendors
        Route::get('top', [VendorsReportController::class, 'topVendors'])
            ->name('top');

        // GET /api/v1/reports/vendors/summary
        // Get vendors summary statistics
        Route::get('summary', [VendorsReportController::class, 'summary'])
            ->name('summary');

        // GET /api/v1/reports/vendors/{vendorId}
        // Get specific vendor performance
        Route::get('{vendorId}', [VendorsReportController::class, 'vendorPerformance'])
            ->where('vendorId', '[0-9]+')
            ->name('vendor-performance');

        // GET /api/v1/reports/vendors/{vendorId}/trends
        // Get specific vendor trends
        Route::get('{vendorId}/trends', [VendorsReportController::class, 'vendorTrends'])
            ->where('vendorId', '[0-9]+')
            ->name('vendor-trends');

        // GET /api/v1/reports/vendors/by-category
        // Get vendors breakdown by category
        Route::get('by-category', [VendorsReportController::class, 'byCategory'])
            ->name('by-category');

        // GET /api/v1/reports/vendors/low-performing
        // Get low performing vendors
        Route::get('low-performing', [VendorsReportController::class, 'lowPerforming'])
            ->name('low-performing');

        // GET /api/v1/reports/vendors/compare
        // Compare vendor performance between periods
        Route::get('compare', [VendorsReportController::class, 'compare'])
            ->name('compare');

        // GET /api/v1/reports/vendors/growth
        // Get vendors growth ranking
        Route::get('growth', [VendorsReportController::class, 'growthRanking'])
            ->name('growth');

        // GET /api/v1/reports/vendors/dashboard
        // Get comprehensive vendors dashboard
        Route::get('dashboard', [VendorsReportController::class, 'dashboard'])
            ->name('dashboard');
    });
});
