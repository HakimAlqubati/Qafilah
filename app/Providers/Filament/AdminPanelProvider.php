<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Attributes\AttributeResource;
use App\Filament\Resources\AttributeSets\AttributeSetResource;
use App\Filament\Resources\AttributeValues\AttributeValueResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Districts\DistrictResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Units\UnitResource;
use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Qafilah')
            ->favicon(asset('/imgs/logo.png'))
            ->brandLogo(asset('/imgs/logo.png'))
            ->brandLogoHeight('3.0rem')
            ->colors([
                'primary' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->items([
                    NavigationItem::make('Dashboard')
                        ->icon('heroicon-o-home')
                        // ->isActiveWhen(fn(): bool => original_request()->routeIs('filament.admin.pages.dashboard'))
                        ->url(fn(): string => Dashboard::getUrl()),

                    // ...UserResource::getNavigationItems(),
                    // ...Settings::getNavigationItems(),
                ])->groups([
                    NavigationGroup::make('Vendor Management')
                        ->items([
                            ...VendorResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('Location Management')
                        ->items([
                            ...CountryResource::getNavigationItems(),
                            ...CityResource::getNavigationItems(),
                            ...DistrictResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('Management')
                        ->label(__('lang.management'))
                        ->items([

                            ...AttributeResource::getNavigationItems(),
                            ...AttributeValueResource::getNavigationItems(),
                            // ...AttributeSetResource::getNavigationItems(),
                            ...UnitResource::getNavigationItems(),
                            ...CurrencyResource::getNavigationItems(),

                        ]),
                    NavigationGroup::make('Products')
                        ->items([
                            ...CategoryResource::getNavigationItems(),
                            ...ProductResource::getNavigationItems(),
                        ]),
                ]);
            })
            ->sidebarCollapsibleOnDesktop()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn(): string =>
                view('filament.partials.current-time')->render()
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn(): string =>
                view('filament.partials.welcome')->render()
            )
          
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
