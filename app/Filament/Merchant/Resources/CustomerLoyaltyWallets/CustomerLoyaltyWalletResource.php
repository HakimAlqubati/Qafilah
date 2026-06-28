<?php

namespace App\Filament\Merchant\Resources\CustomerLoyaltyWallets;

use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Pages\CreateCustomerLoyaltyWallet;
use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Pages\EditCustomerLoyaltyWallet;
use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Pages\ListCustomerLoyaltyWallets;
use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Schemas\CustomerLoyaltyWalletForm;
use App\Filament\Merchant\Resources\CustomerLoyaltyWallets\Tables\CustomerLoyaltyWalletsTable;
use App\Models\CustomerLoyaltyWallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomerLoyaltyWalletResource extends Resource
{
    protected static ?string $model = CustomerLoyaltyWallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'merchant.name';

    public static function getModelLabel(): string
    {
        return __('lang.customer_loyalty_wallet');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lang.customer_loyalty_wallets');
    }

    public static function getEloquentQuery(): Builder
    {
        $vendorId = Auth::user()?->vendor_id;

        return parent::getEloquentQuery()
            ->where('merchant_id', $vendorId);
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerLoyaltyWalletForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerLoyaltyWalletsTable::configure($table);
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
            'index' => ListCustomerLoyaltyWallets::route('/'),
            'create' => CreateCustomerLoyaltyWallet::route('/create'),
            'edit' => EditCustomerLoyaltyWallet::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
