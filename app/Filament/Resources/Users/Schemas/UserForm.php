<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic Information Section
                Section::make(__('lang.basic_information'))
                    ->description(__('lang.user_basic_info_description'))
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)->schema([
                            // Name Field
                            TextInput::make('name')
                                ->label(__('lang.name'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            // Avatar Upload
                            FileUpload::make('avatar')
                                ->label(__('lang.avatar'))
                                ->image()
                                ->avatar()
                                ->directory('avatars')
                                ->maxSize(2048)
                                ->columnSpan(1),
                        ]),

                        Grid::make(2)->schema([
                            // Email Field
                            TextInput::make('email')
                                ->label(__('lang.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->prefixIcon('heroicon-m-envelope'),

                            // Phone Field
                            TextInput::make('phone')
                                ->label(__('lang.phone'))
                                ->tel()
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => __('lang.phone_already_exists'),
                                ])
                                ->maxLength(255)
                                ->prefixIcon('heroicon-m-phone'),
                        ]),
                    ])->columns(1),

                // Security Section
                Section::make(__('lang.security'))
                    ->description(__('lang.user_security_description'))
                    ->icon('heroicon-o-lock-closed')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            // Password Field
                            TextInput::make('password')
                                ->label(__('lang.password'))
                                ->password()
                                ->revealable()
                                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                ->dehydrated(fn($state) => filled($state))
                                ->required(fn(string $context): bool => $context === 'create')
                                ->minLength(6)
                                ->same('password_confirmation')
                                ->prefixIcon('heroicon-m-key'),

                            // Password Confirmation Field
                            TextInput::make('password_confirmation')
                                ->label(__('lang.password_confirmation'))
                                ->password()
                                ->revealable()
                                ->required(fn(string $context): bool => $context === 'create')
                                ->dehydrated(false)
                                ->prefixIcon('heroicon-m-key'),
                        ]),
                    ])->columns(1),

                // Status & Permissions Section
                Section::make(__('lang.status_and_permissions'))
                    ->description(__('lang.user_status_permissions_description'))
                    ->icon('heroicon-o-shield-check')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)->schema([
                            // Status Field
                            Select::make('status')
                                ->label(__('lang.status'))
                                ->options([
                                    User::STATUS_ACTIVE => __('lang.active'),
                                    User::STATUS_INACTIVE => __('lang.inactive'),
                                    User::STATUS_SUSPENDED => __('lang.suspended'),
                                ])
                                ->default(User::STATUS_ACTIVE)
                                ->required()
                                ->native(false),

                            // Is Active Toggle
                            Toggle::make('is_active')
                                ->label(__('lang.is_active'))
                                ->default(true)
                                ->inline(false),

                            // Roles
                            Select::make('roles')
                                ->label(__('lang.roles'))
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->native(false),
                        ]),
                    ])->columns(1),

                // Vendor Assignment Section
                Section::make(__('lang.vendor_assignment'))
                    ->description(__('lang.vendor_assignment_description'))
                    ->icon('heroicon-o-building-storefront')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        // Toggle for Vendor (only visible in create mode)
                        Toggle::make('is_vendor_user')
                            ->label(__('lang.is_vendor_user'))
                            ->helperText(__('lang.is_vendor_user_helper'))
                            ->live()
                            ->default(false)
                            ->dehydrated(false)
                            // ->visible(fn(string $context): bool => $context === 'create')
                            ->columnSpanFull(),

                        // Vendor Select
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->label(__('lang.vendor'))
                            ->placeholder(__('lang.select_vendor'))
                            ->native(false)
                            ->visible(
                                fn($get, string $context, $record): bool =>
                                (bool) $get('is_vendor_user')
                            )
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}
