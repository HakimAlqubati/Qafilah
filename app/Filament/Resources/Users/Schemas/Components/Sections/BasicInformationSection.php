<?php

namespace App\Filament\Resources\Users\Schemas\Components\Sections;

use App\Enums\UserTypes;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class BasicInformationSection
{
    /**
     * Create the Basic Information section
     */
    public static function make(): Section
    {
        return Section::make(__('lang.basic_information'))
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

                Grid::make(3)->schema([
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

                    // User Type Field
                    Select::make('user_type')
                        ->label(__('lang.user_type'))
                        ->options(UserTypes::class)
                        ->required()
                        ->native(false)
                        ->prefixIcon('heroicon-m-user-group'),
                ]),
            ])
            ->columns(1);
    }
}
