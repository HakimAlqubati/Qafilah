<?php

namespace App\Filament\Resources\Users\Schemas\Components\Sections;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class StatusPermissionsSection
{
    /**
     * Create the Status & Permissions section
     */
    public static function make(): Section
    {
        return Section::make(__('lang.status_and_permissions'))
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
            ])
            ->columns(1);
    }
}
