<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use BackedEnum;
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

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Vendor'),

               Select::make('variant_id')
                    ->label('Variant')
                    ->options(function (RelationManager $livewire) {
                        return ProductVariant::where('product_id', $livewire->getOwnerRecord()->id)
                            ->get()
                            ->mapWithKeys(function ($variant) {
                                // Create a descriptive label for the variant
                                $name = $variant->sku ?? 'Variant #' . $variant->id;
                                // You might want to append attributes here if available
                                return [$variant->id => $name];
                            });
                    })
                    ->required()
                    ->searchable(),

               TextInput::make('cost_price')
                    ->label('Cost Price')
                    ->numeric()
                    ->prefix('SAR'),

               TextInput::make('selling_price')
                    ->label('Selling Price')
                    ->numeric()
                    ->required()
                    ->prefix('SAR'),

               Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->label('Currency')
                    ->searchable()
                    ->preload(),

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
            ->recordTitleAttribute('vendor_sku')
            ->columns([
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('variant.sku')
                    ->label('Variant SKU')
                    ->sortable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Price')
                    ->money(fn($record) => $record->currency?->code ?? 'SAR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default_offer')
                    ->boolean()
                    ->label('Default'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
              CreateAction::make()
                    ->using(function (array $data, string $model) {
                        return $model::create($data);
                    }),
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
