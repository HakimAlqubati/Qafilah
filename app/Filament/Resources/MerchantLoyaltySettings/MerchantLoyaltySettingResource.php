<?php

namespace App\Filament\Resources\MerchantLoyaltySettings;

use App\Filament\Resources\MerchantLoyaltySettings\Pages\CreateMerchantLoyaltySetting;
use App\Filament\Resources\MerchantLoyaltySettings\Pages\EditMerchantLoyaltySetting;
use App\Filament\Resources\MerchantLoyaltySettings\Pages\ListMerchantLoyaltySettings;
use App\Filament\Resources\MerchantLoyaltySettings\Schemas\MerchantLoyaltySettingForm;
use App\Filament\Resources\MerchantLoyaltySettings\Tables\MerchantLoyaltySettingsTable;
use App\Models\MerchantLoyaltySetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MerchantLoyaltySettingResource extends Resource
{
    protected static ?string $model = MerchantLoyaltySetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'merchant.name';

    public static function getModelLabel(): string
    {
        return __('lang.merchant_loyalty_setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lang.merchant_loyalty_settings');
    }

    public static function form(Schema $schema): Schema
    {
        return MerchantLoyaltySettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantLoyaltySettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantLoyaltySettings::route('/'),
            'create' => CreateMerchantLoyaltySetting::route('/create'),
            'edit' => EditMerchantLoyaltySetting::route('/{record}/edit'),
        ];
    }
}
