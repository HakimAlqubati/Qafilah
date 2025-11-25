<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas;

use App\Models\Currency;
use App\Models\ProductVariant;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;                // إبقاء نفس الـ Schema wrapper الخاص بمشروعك


class ProductVendorSkuForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Grid::make(2)->columnSpanFull()
                    ->schema([
                        Select::make('variant_id')
                            ->label('Product')
                            ->searchable()
                            ->options(function () {
                                return ProductVariant::query()
                                    ->whereHas('product')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($variant) {
                                        return [$variant->id => $variant->product->name . ' (' . $variant->master_sku . ')'];
                                    });
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return ProductVariant::query()
                                    ->whereHas('product', function (Builder $query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($variant) {
                                        return [$variant->id => $variant->product->name . ' (' . $variant->master_sku . ')'];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $variant = ProductVariant::with('product')->find($value);
                                return $variant ? $variant->product->name . ' (' . $variant->master_sku . ')' : null;
                            })
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('vendor_sku')
                            ->label('My SKU')
                            ->required(),

                        Select::make('currency_id')
                            ->label('Currency')
                            ->options(Currency::active()->pluck('code', 'id'))
                            ->default(fn() => Currency::default()->first()?->id)
                            ->required(),

                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->required(),

                        TextInput::make('selling_price')
                            ->label('Selling Price')
                            ->numeric()
                            ->required(),

                        TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('moq')
                            ->label('MOQ')
                            ->numeric()
                            ->default(1)
                            ->required(),

                      

                        Hidden::make('vendor_id')
                            ->default(fn() => auth()->user()->vendor_id),
                    ])
            ]);
    }
}
