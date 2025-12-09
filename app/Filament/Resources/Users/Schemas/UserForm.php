<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('lang.user_details'))->columnSpanFull()
                    ->description(__('lang.user_details_description'))
                    ->schema([
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

                        // Password Field
                        TextInput::make('password')
                            ->label(__('lang.password'))
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            // ->rule(Password::default())
                            ->minLength(6),

                        // Vendor Relationship (Based on vendor_id)
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->label(__('lang.vendor'))
                            ->placeholder(__('lang.select_vendor')),

                        // Roles (Based on HasRoles trait)
                        Select::make('roles')
                            ->label(__('lang.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
            ]);
    }
}
