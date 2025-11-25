<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus;

use App\Filament\Merchant\Resources\ProductVendorSkus\Pages\CreateProductVendorSku;
use App\Filament\Merchant\Resources\ProductVendorSkus\Pages\EditProductVendorSku;
use App\Filament\Merchant\Resources\ProductVendorSkus\Pages\ListProductVendorSkus;
use App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\ProductVendorSkuForm;
use App\Filament\Merchant\Resources\ProductVendorSkus\Tables\ProductVendorSkusTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ProductVendorSku;

class ProductVendorSkuResource extends Resource
{
    protected static ?string $model = ProductVendorSku::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'vendor_id';

    public static function form(Schema $schema): Schema
    {
        return ProductVendorSkuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductVendorSkusTable::configure($table);
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
            'index' => ListProductVendorSkus::route('/'),
            'create' => CreateProductVendorSku::route('/create'),
            'edit' => EditProductVendorSku::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('vendor_id',auth()->user()->vendor_id);
    }
}
