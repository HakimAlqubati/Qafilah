<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('lang.order_info'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('order_number')
                                ->label(__('lang.order_number'))
                                ->weight(FontWeight::Bold)
                                ->copyable(),

                            TextEntry::make('customer.name')
                                ->label(__('lang.customer')),

                            TextEntry::make('vendor.name')
                                ->label(__('lang.vendor'))
                                ->default('-'),

                            TextEntry::make('placed_at')
                                ->label(__('lang.placed_at'))
                                ->dateTime(),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->label(__('lang.status'))
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    Order::STATUS_PENDING => 'warning',
                                    Order::STATUS_CONFIRMED => 'info',
                                    Order::STATUS_PROCESSING => 'primary',
                                    Order::STATUS_SHIPPED => 'info',
                                    Order::STATUS_DELIVERED => 'success',
                                    Order::STATUS_COMPLETED => 'success',
                                    Order::STATUS_CANCELLED => 'danger',
                                    Order::STATUS_RETURNED => 'gray',
                                    default => 'secondary',
                                })
                                ->formatStateUsing(fn(string $state): string => Order::STATUSES[$state] ?? $state),

                            TextEntry::make('payment_status')
                                ->label(__('lang.payment_status'))
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    Order::PAYMENT_PENDING => 'warning',
                                    Order::PAYMENT_PARTIAL => 'info',
                                    Order::PAYMENT_PAID => 'success',
                                    Order::PAYMENT_REFUNDED => 'danger',
                                    default => 'secondary',
                                })
                                ->formatStateUsing(fn(string $state): string => Order::PAYMENT_STATUSES[$state] ?? $state),

                            TextEntry::make('shipping_status')
                                ->label(__('lang.shipping_status'))
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    Order::SHIPPING_PENDING => 'warning',
                                    Order::SHIPPING_PREPARING => 'info',
                                    Order::SHIPPING_SHIPPED => 'primary',
                                    Order::SHIPPING_IN_TRANSIT => 'info',
                                    Order::SHIPPING_DELIVERED => 'success',
                                    default => 'secondary',
                                })
                                ->formatStateUsing(fn(string $state): string => Order::SHIPPING_STATUSES[$state] ?? $state),
                        ]),
                    ]),

                Section::make(__('lang.addresses'))
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)->schema([
                            Group::make([
                                TextEntry::make('shippingAddress.address')
                                    ->label(__('lang.shipping_address')),

                                TextEntry::make('shippingAddress.city.name')
                                    ->label(__('lang.city')),
                            ]),

                            Group::make([
                                TextEntry::make('billingAddress.address')
                                    ->label(__('lang.billing_address')),

                                TextEntry::make('billingAddress.city.name')
                                    ->label(__('lang.city')),
                            ]),
                        ]),
                    ])->collapsible(),

                Section::make(__('lang.order_items'))
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label(__('lang.product')),

                                TextEntry::make('sku')
                                    ->label(__('lang.sku'))
                                    ->default('-'),

                                TextEntry::make('unit.name')
                                    ->label(__('lang.unit'))
                                    ->default('-'),

                                TextEntry::make('package_size')
                                    ->label(__('lang.package_size')),

                                TextEntry::make('quantity')
                                    ->label(__('lang.quantity')),

                                TextEntry::make('unit_price')
                                    ->label(__('lang.unit_price'))
                                    ->money('YER'),

                                TextEntry::make('discount')
                                    ->label(__('lang.discount'))
                                    ->money('YER'),

                                TextEntry::make('total')
                                    ->label(__('lang.total'))
                                    ->money('YER')
                                    ->weight(FontWeight::Bold),
                            ])
                            ->columns(8),
                    ]),

                Section::make(__('lang.totals'))
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(5)->schema([
                            TextEntry::make('subtotal')
                                ->label(__('lang.subtotal'))
                                ->money('YER'),

                            TextEntry::make('tax_amount')
                                ->label(__('lang.tax_amount'))
                                ->money('YER'),

                            TextEntry::make('discount_amount')
                                ->label(__('lang.discount_amount'))
                                ->money('YER'),

                            TextEntry::make('shipping_amount')
                                ->label(__('lang.shipping_amount'))
                                ->money('YER'),

                            TextEntry::make('total')
                                ->label(__('lang.total'))
                                ->money('YER')
                                ->weight(FontWeight::Bold)
                                ->size(TextEntry\TextEntrySize::Large),
                        ]),
                    ]),

                Section::make(__('lang.notes'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('notes')
                                ->label(__('lang.customer_notes'))
                                ->default('-'),

                            TextEntry::make('internal_notes')
                                ->label(__('lang.internal_notes'))
                                ->default('-'),
                        ]),
                    ])->collapsible(),

                Section::make(__('lang.status_history'))
                    ->icon('heroicon-o-clock')
                    ->schema([
                        RepeatableEntry::make('statusHistory')
                            ->label('')
                            ->schema([
                                TextEntry::make('status')
                                    ->label(__('lang.status'))
                                    ->formatStateUsing(fn(string $state): string => Order::STATUSES[$state] ?? $state),

                                TextEntry::make('comment')
                                    ->label(__('lang.comment'))
                                    ->default('-'),

                                TextEntry::make('changedBy.name')
                                    ->label(__('lang.changed_by'))
                                    ->default('النظام'),

                                TextEntry::make('created_at')
                                    ->label(__('lang.date'))
                                    ->dateTime(),
                            ])
                            ->columns(4),
                    ])->collapsible(),
            ]);
    }
}
