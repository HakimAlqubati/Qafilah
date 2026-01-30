<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

// Auth Repositories
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\AuthRepository;

// Sales Report Repositories
use App\Repositories\Reports\Sales\SalesRepositoryInterface;
use App\Repositories\Reports\Sales\EloquentSalesRepository;

// Products Report Repositories
use App\Repositories\Reports\Products\ProductsReportRepositoryInterface;
use App\Repositories\Reports\Products\EloquentProductsReportRepository;

// Vendors Report Repositories
use App\Repositories\Reports\Vendors\VendorsReportRepositoryInterface;
use App\Repositories\Reports\Vendors\EloquentVendorsReportRepository;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Auth
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);

        // Reports - Sales
        $this->app->bind(SalesRepositoryInterface::class, EloquentSalesRepository::class);

        // Reports - Products
        $this->app->bind(ProductsReportRepositoryInterface::class, EloquentProductsReportRepository::class);

        // Reports - Vendors
        $this->app->bind(VendorsReportRepositoryInterface::class, EloquentVendorsReportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en']); // also accepts a closure
        });
    }
}
