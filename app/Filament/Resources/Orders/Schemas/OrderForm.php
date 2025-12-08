<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVendorSku;
use App\Models\ProductVendorSkuUnit;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->columnSpanFull()
                    ->skippable()
                    ->steps([
                        // Step 1: معلومات الطلب الأساسية
                        Step::make('order_info')
                            ->label(__('lang.order_info'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('order_number')
                                    ->label(__('lang.order_number'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(__('lang.auto_generated')),

                                Select::make('customer_id')
                                    ->label(__('lang.customer'))
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $customer = Customer::find($state);
                                            if ($customer) {
                                                $defaultAddress = $customer->addresses()->where('is_default', true)->first();
                                                if ($defaultAddress) {
                                                    $set('shipping_address_id', $defaultAddress->id);
                                                    $set('billing_address_id', $defaultAddress->id);
                                                }
                                            }
                                        }
                                    }),

                                Select::make('vendor_id')
                                    ->label(__('lang.vendor'))
                                    ->relationship('vendor', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('currency_id')
                                    ->label(__('lang.currency'))
                                    ->relationship('currency', 'name')
                                    ->default(1)
                                    ->preload(),

                                Select::make('shipping_address_id')
                                    ->label(__('lang.shipping_address'))
                                    ->relationship(
                                        'shippingAddress',
                                        'address',
                                        fn($query, Get $get) => $query->where('customer_id', $get('customer_id'))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn(Get $get) => $get('customer_id')),

                                Select::make('billing_address_id')
                                    ->label(__('lang.billing_address'))
                                    ->relationship(
                                        'billingAddress',
                                        'address',
                                        fn($query, Get $get) => $query->where('customer_id', $get('customer_id'))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn(Get $get) => $get('customer_id')),
                            ])->columns(2),

                        // Step 2: بنود الطلب
                        Step::make('order_items')
                            ->label(__('lang.order_items'))
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->label('')
                                    ->schema([
                                        Select::make('product_id')
                                            ->label(__('lang.product'))
                                            ->options(function (Get $get) {
                                                $vendorId = $get('../../vendor_id');
                                                // if (!$vendorId) {
                                                //     return Product::query()->pluck('name', 'id');
                                                // }

                                                // Get products that have offers from this vendor
                                                return Product::whereHas('vendorOffers', function ($query) use ($vendorId) {
                                                    $query->where('vendor_id', $vendorId);
                                                })->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    $set('product_name', $product?->name ?? '');
                                                    // Reset dependent fields
                                                    $set('product_vendor_sku_id', null);
                                                    $set('product_vendor_sku_unit_id', null);
                                                    $set('package_size', 1);
                                                    $set('unit_price', 0);
                                                }
                                            })
                                            ->columnSpan(2),

                                        Hidden::make('product_name')
                                            ->dehydrated(true),

                                        Select::make('product_vendor_sku_id')
                                            ->label(__('lang.vendor_offer'))
                                            ->options(function (Get $get) {
                                                $productId = $get('product_id');
                                                $vendorId = $get('../../vendor_id');
                                                if (!$productId) return [];

                                                $query = ProductVendorSku::whereHas('variant', fn($q) => $q->where('product_id', $productId))
                                                    ->with('vendor');

                                                // Filter by vendor if selected
                                                if ($vendorId) {
                                                    $query->where('vendor_id', $vendorId);
                                                }

                                                return $query->get()
                                                    ->mapWithKeys(fn($sku) => [
                                                        $sku->id => $sku->vendor?->name . ' - ' . $sku->selling_price
                                                    ]);
                                            })
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $sku = ProductVendorSku::find($state);
                                                    $set('sku', $sku?->vendor_sku);
                                                    $set('unit_price', $sku?->selling_price ?? 0);
                                                    // Reset unit selection
                                                    $set('product_vendor_sku_unit_id', null);
                                                    $set('package_size', 1);
                                                }
                                            })
                                            ->columnSpan(2),

                                        Select::make('product_vendor_sku_unit_id')
                                            ->label(__('lang.unit'))
                                            ->options(function (Get $get) {
                                                $skuId = $get('product_vendor_sku_id');
                                                if (!$skuId) return [];

                                                return ProductVendorSkuUnit::where('product_vendor_sku_id', $skuId)
                                                    ->active()
                                                    ->with('unit')
                                                    ->get()
                                                    ->mapWithKeys(fn($u) => [
                                                        $u->id => $u->unit?->name . ' (' . $u->package_size . ' ' . __('lang.pieces') . ')'
                                                    ]);
                                            })
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $skuUnit = ProductVendorSkuUnit::find($state);
                                                    $set('unit_id', $skuUnit?->unit_id);
                                                    $set('package_size', $skuUnit?->package_size ?? 1);
                                                    $set('unit_price', $skuUnit?->selling_price ?? 0);
                                                }
                                            }),

                                        TextInput::make('sku')
                                            ->label(__('lang.sku'))
                                            ->maxLength(255),

                                        TextInput::make('package_size')
                                            ->label(__('lang.package_size'))
                                            ->numeric()
                                            ->default(1)
                                            ->disabled()
                                            ->dehydrated(true),

                                        TextInput::make('quantity')
                                            ->label(__('lang.quantity'))
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateItemTotal($set, $get)),

                                        TextInput::make('unit_price')
                                            ->label(__('lang.unit_price'))
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateItemTotal($set, $get)),

                                        TextInput::make('discount')
                                            ->label(__('lang.discount'))
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateItemTotal($set, $get)),

                                        TextInput::make('tax')
                                            ->label(__('lang.tax'))
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateItemTotal($set, $get)),

                                        TextInput::make('total')
                                            ->label(__('lang.total'))
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(true),

                                        Textarea::make('notes')
                                            ->label(__('lang.notes'))
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(6)
                                    ->defaultItems(1)
                                    ->addActionLabel(__('lang.add_item'))
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn(array $state): ?string => $state['product_name'] ?? null),
                            ]),

                        // Step 3: الإجماليات والحالة
                        Step::make('totals_status')
                            ->label(__('lang.totals'))
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                Select::make('status')
                                    ->label(__('lang.status'))
                                    ->options(Order::STATUSES)
                                    ->default(Order::STATUS_PENDING)
                                    ->required(),

                                Select::make('payment_status')
                                    ->label(__('lang.payment_status'))
                                    ->options(Order::PAYMENT_STATUSES)
                                    ->default(Order::PAYMENT_PENDING)
                                    ->required(),

                                Select::make('shipping_status')
                                    ->label(__('lang.shipping_status'))
                                    ->options(Order::SHIPPING_STATUSES)
                                    ->default(Order::SHIPPING_PENDING)
                                    ->required(),

                                TextInput::make('subtotal')
                                    ->label(__('lang.subtotal'))
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),

                                TextInput::make('tax_amount')
                                    ->label(__('lang.tax_amount'))
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('discount_amount')
                                    ->label(__('lang.discount_amount'))
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('shipping_amount')
                                    ->label(__('lang.shipping_amount'))
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('total')
                                    ->label(__('lang.total'))
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),
                            ])->columns(4),

                        // Step 4: الملاحظات
                        Step::make('notes')
                            ->label(__('lang.notes'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Textarea::make('notes')
                                    ->label(__('lang.customer_notes'))
                                    ->rows(3),

                                Textarea::make('internal_notes')
                                    ->label(__('lang.internal_notes'))
                                    ->rows(3),
                            ])->columns(2),
                    ]),
            ]);
    }

    protected static function calculateItemTotal(Set $set, Get $get): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $unitPrice = floatval($get('unit_price') ?? 0);
        $discount = floatval($get('discount') ?? 0);
        $tax = floatval($get('tax') ?? 0);

        $total = ($quantity * $unitPrice) - $discount + $tax;
        $set('total', round($total, 2));
    }
}
