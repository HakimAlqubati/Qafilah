<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class VendorOffersRelationManager extends RelationManager
{
    protected static string $relationship = 'vendorOffers';

    protected static ?string $title = 'Vendor Offers';

    protected static string|BackedEnum|null $icon = Heroicon::BuildingStorefront;

    /**
     * توليد vendor_sku تلقائياً بناءً على التاجر والمتغير
     */
    protected static function generateVendorSku($vendorId, $variantId): ?string
    {
        if (!$vendorId || !$variantId) {
            return null;
        }

        $vendor = \App\Models\Vendor::find($vendorId);
        $variant = ProductVariant::find($variantId);

        if (!$vendor || !$variant) {
            return null;
        }

        // صيغة: اختصار اسم التاجر-SKU المتغير-رقم عشوائي
        $vendorPrefix = strtoupper(substr($vendor->name, 0, 3));
        $variantSku = $variant->master_sku ?? 'VAR' . $variant->id;
        $randomSuffix = rand(100, 999);

        return $vendorPrefix . '-' . $variantSku . '-' . $randomSuffix;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Vendor')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // عند اختيار التاجر، نضع عملته الافتراضية
                        if ($state) {
                            $vendor = \App\Models\Vendor::find($state);
                            if ($vendor && $vendor->default_currency_id) {
                                $set('currency_id', $vendor->default_currency_id);
                            }
                        }

                        // توليد vendor_sku إذا كان المتغير محدداً
                        $variantId = $get('variant_id');
                        if ($state && $variantId) {
                            $generatedSku = self::generateVendorSku($state, $variantId);
                            if ($generatedSku) {
                                $set('vendor_sku', $generatedSku);
                            }
                        }
                    }),

                Select::make('variant_id')
                    ->label('Variant')
                    ->options(function (RelationManager $livewire) {
                        return ProductVariant::where('product_id', $livewire->getOwnerRecord()->id)
                            ->with('variantValues.attribute') // Eager load attributes
                            ->get()
                            ->mapWithKeys(function ($variant) {
                                // بناء اسم المتغير مع الخصائص
                                $sku = "";

                                // جلب الخصائص
                                $attributes = $variant->variantValues
                                    ->map(function ($attributeValue) {
                                        return $attributeValue->attribute->name . ': ' . $attributeValue->value;
                                    })
                                    ->implode(', ');

                                // دمج SKU مع الخصائص
                                $label = $sku;
                                if ($attributes) {
                                    $label .= ' (' . $attributes . ')';
                                }

                                return [$variant->id => $label];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // توليد vendor_sku تلقائياً
                        $vendorId = $get('vendor_id');
                        if ($state && $vendorId) {
                            $generatedSku = self::generateVendorSku($vendorId, $state);
                            if ($generatedSku) {
                                $set('vendor_sku', $generatedSku);
                            }
                        }
                    }),

                TextInput::make('vendor_sku')
                    ->label('Vendor SKU')
                    ->maxLength(255)->required()
                    ->helperText('Auto-generated, but you can modify it'),

                TextInput::make('cost_price')
                    ->label('Cost Price')
                    ->numeric()->required(),

                TextInput::make('selling_price')
                    ->label('Selling Price')
                    ->numeric()
                    ->required(),

                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->label('Currency')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('stock')
                    ->label('Stock Quantity')
                    ->numeric()
                    ->default(0)
                    ->required(),

                TextInput::make('moq')
                    ->label('Minimum Order Qty')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Toggle::make('is_default_offer')
                    ->label('Default Offer'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->recordTitleAttribute('vendor_sku')
            ->columns([
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vendor_sku')
                    ->label('Variant SKU')->alignCenter()
                    ->default('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Price')->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency.name')
                    ->label('Currency')->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()->alignCenter(),

                Tables\Columns\IconColumn::make('is_default_offer')
                    ->boolean()->alignCenter()
                    ->label('Default'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

                CreateAction::make()

            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
