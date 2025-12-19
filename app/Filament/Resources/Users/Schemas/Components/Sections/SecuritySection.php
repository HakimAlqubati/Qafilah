<?php

namespace App\Filament\Resources\Users\Schemas\Components\Sections;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;

class SecuritySection
{
    /**
     * Create the Security section
     */
    public static function make(): Section
    {
        return Section::make(__('lang.security'))
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
            ])
            ->columns(1);
    }
}
