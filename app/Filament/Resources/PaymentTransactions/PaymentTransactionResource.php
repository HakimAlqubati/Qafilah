<?php

namespace App\Filament\Resources\PaymentTransactions;

use App\Filament\Resources\PaymentTransactions\Pages\CreatePaymentTransaction;
use App\Filament\Resources\PaymentTransactions\Pages\EditPaymentTransaction;
use App\Filament\Resources\PaymentTransactions\Pages\ListPaymentTransactions;
use App\Filament\Resources\PaymentTransactions\Pages\ViewPaymentTransaction;
use App\Filament\Resources\PaymentTransactions\Schemas\PaymentTransactionForm;
use App\Filament\Resources\PaymentTransactions\Schemas\PaymentTransactionInfolist;
use App\Filament\Resources\PaymentTransactions\Tables\PaymentTransactionsTable;
use App\Models\PaymentTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return __('lang.payment_transactions');
    }

    public static function getPluralLabel(): string
    {
        return __('lang.payment_transactions');
    }

    public static function getLabel(): string
    {
        return __('lang.payment_transaction');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lang.payment_gateways');
    }

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return PaymentTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentTransactionsTable::configure($table);
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
            'index' => ListPaymentTransactions::route('/'),
            'create' => CreatePaymentTransaction::route('/create'),
            'view' => ViewPaymentTransaction::route('/{record}'),
            'edit' => EditPaymentTransaction::route('/{record}/edit'),
        ];
    }

    /* ============================================================
     | 🎨 Badge Helper Methods
     |============================================================ */

    /**
     * Get badge color for transaction status
     */
    public static function getStatusBadgeColor(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'gray',
            'reviewing' => 'info',
            default => 'gray',
        };
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    /**
     * Get translated label for transaction status
     */
    public static function getStatusLabel(string $status): string
    {
        return __('lang.' . $status);
    }
}
