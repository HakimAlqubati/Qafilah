<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SettingResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected  string $view = 'filament.resources.settings.pages.manage-settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('lang.settings');
    }

    public function getTitle(): string
    {
        return __('lang.settings');
    }

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings_tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make(__('lang.general_settings'))
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Section::make(__('lang.site_information'))
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label(__('lang.site_name'))

                                            ->required()->columnSpanFull()
                                            ->prefixIcon(Heroicon::OutlinedBuildingOffice)
                                            ->maxLength(255),


                                        Grid::make()->columns(2)->schema([
                                            TextInput::make('site_email')
                                                ->label(__('lang.site_email'))
                                                ->email()
                                                ->prefixIcon(Heroicon::Inbox),

                                            TextInput::make('site_phone')
                                                ->label(__('lang.site_phone'))
                                                ->tel()
                                                ->prefixIcon(Heroicon::OutlinedPhone),
                                        ]),
                                        Textarea::make('site_description')
                                            ->label(__('lang.site_description'))
                                            ->columnSpanFull()

                                            ->rows(3),
                                    ]),

                                Section::make(__('lang.regional_settings'))
                                    ->schema([
                                        Select::make('default_country_id')
                                            ->label(__('lang.default_country'))
                                            ->options(\App\Models\Country::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->helperText(__('lang.default_country_helper')),

                                        TextInput::make('default_timezone')
                                            ->label(__('lang.default_timezone'))
                                            ->default('Asia/Riyadh'),

                                        TextInput::make('default_locale')
                                            ->label(__('lang.default_locale'))
                                            ->default('ar'),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make(__('lang.merchant_settings'))
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                // Section::make(__('lang.product_form_settings'))
                                //     ->schema([
                                Toggle::make('merchant_show_product_units_only')
                                    ->label(__('lang.merchant_show_product_units_only'))
                                    ->helperText(__('lang.merchant_show_product_units_only_helper')),
                                // ]),
                            ]),

                        Tab::make(__('lang.business_settings'))
                            ->hidden()
                            ->icon(Heroicon::OutlinedBuildingOffice)
                            ->schema([
                                Section::make(__('lang.tax_settings'))
                                    ->schema([
                                        Toggle::make('enable_tax')
                                            ->label(__('lang.enable_tax')),

                                        TextInput::make('default_tax_rate')
                                            ->label(__('lang.default_tax_rate'))
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(15),

                                        TextInput::make('tax_number')
                                            ->label(__('lang.tax_number')),
                                    ])
                                    ->columns(3),

                                Section::make(__('lang.order_settings'))
                                    ->schema([
                                        TextInput::make('min_order_amount')
                                            ->label(__('lang.min_order_amount'))
                                            ->numeric()
                                            ->prefix('SAR'),

                                        Toggle::make('allow_guest_checkout')
                                            ->label(__('lang.allow_guest_checkout')),

                                        Toggle::make('enable_stock_management')
                                            ->label(__('lang.enable_stock_management'))
                                            ->default(true),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make(__('lang.notification_settings'))
                            ->hidden()
                            ->icon(Heroicon::OutlinedBell)
                            ->schema([
                                Section::make(__('lang.email_notifications'))
                                    ->schema([
                                        Toggle::make('notify_on_new_order')
                                            ->label(__('lang.notify_on_new_order'))
                                            ->default(true),

                                        Toggle::make('notify_on_new_customer')
                                            ->label(__('lang.notify_on_new_customer'))
                                            ->default(true),

                                        Toggle::make('notify_on_low_stock')
                                            ->label(__('lang.notify_on_low_stock'))
                                            ->default(true),

                                        TextInput::make('low_stock_threshold')
                                            ->label(__('lang.low_stock_threshold'))
                                            ->numeric()
                                            ->default(10),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(__('lang.social_media'))
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                Section::make(__('lang.social_links'))
                                    ->schema([
                                        TextInput::make('facebook_url')
                                            ->label(__('lang.facebook_url'))
                                            ->url()
                                            ->prefixIcon(Heroicon::GlobeAlt)
                                            ->prefix('https://'),

                                        TextInput::make('twitter_url')
                                            ->label(__('lang.twitter_url'))
                                            ->url()
                                            ->prefixIcon(Heroicon::GlobeAlt)
                                            ->prefix('https://'),

                                        TextInput::make('instagram_url')
                                            ->label(__('lang.instagram_url'))
                                            ->url()
                                            ->prefixIcon(Heroicon::FaceFrown)
                                            ->prefix('https://'),

                                        TextInput::make('whatsapp_number')
                                            ->label(__('lang.whatsapp_number'))
                                            ->tel()
                                            ->prefixIcon(Heroicon::GlobeAlt),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $finalSettings = [];
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $finalSettings[] = ["key" => $key, "value" => is_bool($value) ? ($value ? '1' : '0') : $value];
            }
        }

        Setting::upsert($finalSettings, ["key"], ["value"]);

        Notification::make()
            ->title(__('lang.settings_saved'))
            ->success()
            ->send();
    }
}
