<?php

namespace App\Providers\Filament;

use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\CustomerLoyaltyWalletResource;
use App\Filament\Merchant\Resources\MerchantLoyaltySettings\MerchantLoyaltySettingResource;
use App\Filament\Merchant\Resources\Orders\MerchantOrderResource;
use App\Filament\Merchant\Resources\ProductVendorSkus\ProductVendorSkuResource;
use App\Filament\Merchant\Resources\Vendors\MerchantVendorResource;
use App\Http\Middleware\CustomFilamentAuthenticate;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MerchantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('merchant')
            ->path('merchant')
            ->login()
            ->brandName('Qafilah')
            ->favicon(asset('/imgs/logo.png'))
            ->brandLogo(asset('/imgs/logo.png'))
            ->brandLogoHeight('3.0rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Merchant/Resources'), for: 'App\Filament\Merchant\Resources')
            ->discoverPages(in: app_path('Filament/Merchant/Pages'), for: 'App\Filament\Merchant\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Merchant/Widgets'), for: 'App\Filament\Merchant\Widgets')
            ->widgets([
                // AccountWidget::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
                CustomFilamentAuthenticate::class,
            ])

            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->items([
                    NavigationItem::make(__('lang.dashboard'))
                        ->icon('heroicon-o-home')
                        ->url(fn (): string => Dashboard::getUrl()),

                    // رابط مباشر لصفحة تعديل بيانات التاجر الحالي
                    NavigationItem::make(__('lang.vendor'))
                        ->icon('heroicon-o-building-storefront')
                        ->url(fn (): string => MerchantVendorResource::getUrl('edit', [
                            'record' => auth()->user()?->vendor_id,
                        ]))
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.merchant.resources.vendors.merchant-vendors.edit')),

                    ...MerchantOrderResource::getNavigationItems(),
                    ...ProductVendorSkuResource::getNavigationItems(),
                ])
                    ->groups([
                        NavigationGroup::make(__('lang.loyalty_management'))
                            ->items([
                                ...MerchantLoyaltySettingResource::getNavigationItems(),
                                ...CustomerLoyaltyWalletResource::getNavigationItems(),   ]),

                    ]);
            })
            // ->topNavigation()
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings([])
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): string => view('filament.partials.current-time')->render()
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): string => view('filament.partials.welcome')->render()
            );
    }
}
