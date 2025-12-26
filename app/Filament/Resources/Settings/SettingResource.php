<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?int $navigationSort = 100;

    public static function getModelLabel(): string
    {
        return __('lang.settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lang.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('lang.settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lang.management');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSettings::route('/'),
        ];
    }
}
