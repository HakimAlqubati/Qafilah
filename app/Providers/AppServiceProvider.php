<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Reports\SalesRepositoryInterface;
use App\Repositories\Reports\EloquentSalesRepository;
use App\Repositories\Reports\ProductsReportRepositoryInterface;
use App\Repositories\Reports\EloquentProductsReportRepository;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(SalesRepositoryInterface::class, EloquentSalesRepository::class);
        $this->app->bind(ProductsReportRepositoryInterface::class, EloquentProductsReportRepository::class);
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
