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
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Districts\DistrictResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use App\Filament\Resources\PaymentTransactions\PaymentTransactionResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Sliders\SliderResource;
use App\Filament\Resources\Units\UnitResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use App\Http\Middleware\CustomAdminFilamentAuthenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
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
                    NavigationItem::make(__('lang.dashboard'))
                        ->icon('heroicon-o-home')
                        // ->isActiveWhen(fn(): bool => original_request()->routeIs('filament.admin.pages.dashboard'))
                        ->url(fn(): string => Dashboard::getUrl()),

                    // ...UserResource::getNavigationItems(),
                    // ...Settings::getNavigationItems(),
                ])->groups([
                    NavigationGroup::make(__('lang.vendor_management'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Vendor') ? VendorResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.customer_management'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Customer') ? CustomerResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.sales'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Order') ? OrderResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.location_management'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Country') ? CountryResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:City') ? CityResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:District') ? DistrictResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make('Management')
                        ->label(__('lang.management'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Attribute') ? AttributeResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:AttributeValue') ? AttributeValueResource::getNavigationItems() : [],
                            // ...AttributeSetResource::getNavigationItems(),
                            ...auth()->user()->can('ViewAny:Unit') ? UnitResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:Currency') ? CurrencyResource::getNavigationItems() : [],

                        ]),
                    NavigationGroup::make(__('lang.products'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Category') ? CategoryResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:Product') ? ProductResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:Slider') ? SliderResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.users_management'))
                        ->items([
                            ...auth()->user()->can('ViewAny:User') ? UserResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:Role') ? RoleResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.payment_gateways'))
                        ->items([
                            ...auth()->user()->can('ViewAny:PaymentGateway') ? PaymentGatewayResource::getNavigationItems() : [],
                            ...auth()->user()->can('ViewAny:PaymentTransaction') ? PaymentTransactionResource::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.reports'))
                        ->items([
                            ...auth()->user()->can('ViewAny:SalesReport') ? \App\Filament\Pages\Reports\SalesReportPage::getNavigationItems() : [],
                        ]),
                    NavigationGroup::make(__('lang.settings'))
                        ->items([
                            ...auth()->user()->can('ViewAny:Setting') ? SettingResource::getNavigationItems() : [],
                        ]),
                ]);
            })
            ->plugin(FilamentShieldPlugin::make())
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
                CustomAdminFilamentAuthenticate::class,
            ]);
    }
}
