<?php

namespace App\Filament\Resources\PaymentGateways;

use App\Filament\Resources\PaymentGateways\Pages\CreatePaymentGateway;
use App\Filament\Resources\PaymentGateways\Pages\EditPaymentGateway;
use App\Filament\Resources\PaymentGateways\Pages\ListPaymentGateways;
use App\Filament\Resources\PaymentGateways\Schemas\PaymentGatewayForm;
use App\Filament\Resources\PaymentGateways\Tables\PaymentGatewaysTable;
use App\Models\PaymentGateway;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationLabel(): string
    {
        return __('lang.payment_gateways');
    }

    public static function getPluralLabel(): string
    {
        return __('lang.payment_gateways');
    }

    public static function getLabel(): string
    {
        return __('lang.payment_gateway');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewaysTable::configure($table);
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
            'index' => ListPaymentGateways::route('/'),
            'create' => CreatePaymentGateway::route('/create'),
            'edit' => EditPaymentGateway::route('/{record}/edit'),
        ];
    }

    /* ============================================================
     | 🎨 Badge Helper Methods
     |============================================================ */

    /**
     * Get badge color for gateway type
     */
    public static function getTypeBadgeColor(string $type): string
    {
        return match ($type) {
            'electronic' => 'success',
            'cash' => 'warning',
            'transfer' => 'info',
            default => 'gray',
        };
    }

    /**
     * Get icon for gateway type
     */
    public static function getTypeIcon(string $type): string
    {
        return match ($type) {
            'electronic' => 'heroicon-o-device-phone-mobile',
            'cash' => 'heroicon-o-banknotes',
            'transfer' => 'heroicon-o-building-library',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    /**
     * Get translated label for gateway type
     */
    public static function getTypeLabel(string $type): string
    {
        return match ($type) {
            'electronic' => __('lang.gateway_type_electronic'),
            'cash' => __('lang.gateway_type_cash'),
            'transfer' => __('lang.gateway_type_transfer'),
            default => $type,
        };
    }

    /**
     * Get badge color for gateway mode
     */
    public static function getModeBadgeColor(string $mode): string
    {
        return match ($mode) {
            'sandbox' => 'warning',
            'live' => 'success',
            default => 'gray',
        };
    }

    /**
     * Get translated label for gateway mode
     */
    public static function getModeLabel(string $mode): string
    {
        return match ($mode) {
            'sandbox' => __('lang.gateway_mode_sandbox'),
            'live' => __('lang.gateway_mode_live'),
            default => $mode,
        };
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
