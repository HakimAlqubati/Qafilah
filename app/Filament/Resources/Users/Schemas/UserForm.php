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
                Section::make(__('lang.user_details'))->columnSpanFull()
                    ->description(__('lang.user_details_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            // Name Field
                            TextInput::make('name')
                                ->label(__('lang.name'))
                                ->required()
                                ->maxLength(255),

                            // Email Field
                            TextInput::make('email')
                                ->label(__('lang.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ]),

                        Grid::make(2)->schema([
                            // Phone Field
                            TextInput::make('phone')
                                ->label(__('lang.phone'))
                                ->tel()
                                ->maxLength(255),

                            // Avatar Upload
                            FileUpload::make('avatar')
                                ->label(__('lang.avatar'))
                                ->image()
                                ->avatar()
                                ->directory('avatars')
                                ->maxSize(2048),
                        ]),

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
                                ->same('password_confirmation'),

                            // Password Confirmation Field
                            TextInput::make('password_confirmation')
                                ->label(__('lang.password_confirmation'))
                                ->password()
                                ->revealable()
                                ->required(fn(string $context): bool => $context === 'create')
                                ->dehydrated(false),
                        ]),

                        Grid::make(2)->schema([
                            // Status Field
                            Select::make('status')
                                ->label(__('lang.status'))
                                ->options([
                                    User::STATUS_ACTIVE => __('lang.active'),
                                    User::STATUS_INACTIVE => __('lang.inactive'),
                                    User::STATUS_SUSPENDED => __('lang.suspended'),
                                ])
                                ->default(User::STATUS_ACTIVE)
                                ->required(),

                            // Is Active Toggle
                            Toggle::make('is_active')
                                ->label(__('lang.is_active'))
                                ->default(true)
                                ->inline(false),
                        ]),

                        Grid::make(2)->schema([
                            // Roles (Based on HasRoles trait)
                            Select::make('roles')
                                ->label(__('lang.roles'))
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                        ]),

                        // Toggle for Vendor (only visible in create mode)
                        Toggle::make('is_vendor_user')
                            ->label(__('lang.is_vendor_user'))
                            ->helperText(__('lang.is_vendor_user_helper'))
                            ->live()
                            ->default(false)
                            ->dehydrated(false)
                            ->visible(fn(string $context): bool => $context === 'create'),

                        // Vendor Select - visible based on toggle in create, or if has vendor in edit
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->label(__('lang.vendor'))
                            ->placeholder(__('lang.select_vendor'))
                            ->visible(
                                fn($get, string $context, $record): bool =>
                                $context === 'create'
                                    ? (bool) $get('is_vendor_user')
                                    : (bool) $record?->vendor_id
                            ),
                    ]),
            ]);
    }
}
