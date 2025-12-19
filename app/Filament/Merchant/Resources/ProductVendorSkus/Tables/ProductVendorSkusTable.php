<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductVendorSkusTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('lang.product'))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Bold),

                TextColumn::make('variant_details')
                    ->label(__('lang.variant'))
                    ->state(function ($record) {
                        return $record->variant?->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(', ');
                    })
                    ->color('primary')
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Bold),

                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('lang.images'))
                    ->collection('default')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText()
                    ->size(40)
                    ->alignCenter()->hidden(),

                TextColumn::make('vendor_sku')
                    ->label(__('lang.sku'))
                    ->searchable(),

                // عمود الأسعار - ملخص مع عدد الوحدات
                TextColumn::make('units_count')
                    ->label(__('lang.prices'))
                    ->state(function ($record) {
                        $unitsCount = $record->units->count();
                        if ($unitsCount === 0) {
                            return __('lang.no_units');
                        }
                        $defaultUnit = $record->units->where('is_default', true)->first()
                            ?? $record->units->first();
                        $currencyCode = $record->currency->code ?? 'SAR';
                        return number_format($defaultUnit->selling_price, 2) . ' ' . $currencyCode . ' (' . $unitsCount . ' ' . __('lang.units') . ')';
                    })
                    ->color(Color::Green)
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Bold)
                    ->badge()->hidden(),

                // عمود المخزون - ملخص
                TextColumn::make('total_stock')
                    ->label(__('lang.stock'))
                    ->state(function ($record) {
                        $totalStock = $record->units->sum('stock');
                        return $totalStock;
                    })
                    ->numeric()
                    ->color(fn($state) => $state > 0 ? Color::Green : Color::Red)
                    ->badge()->hidden(),

                TextColumn::make('status')
                    ->label(__('lang.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'out_of_stock' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('lang.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                // زر عرض تفاصيل الوحدات
                Action::make('view_units')->button()
                    ->label(__('lang.view_units'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn($record) => __('lang.unit_details') . ': ' . $record->product->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('lang.close'))
                    ->schema(function ($record) {
                        return [
                            TextEntry::make('currency')
                                ->label(__('lang.currency'))
                                ->state(fn() => $record->currency->code ?? 'SAR')
                                ->badge()
                                ->color('primary'),

                            RepeatableEntry::make('units')
                                ->label(__('lang.units'))
                                ->table([
                                    TableColumn::make(__('lang.unit')),
                                    TableColumn::make(__('lang.selling_price')),
                                    TableColumn::make(__('lang.stock')),
                                    TableColumn::make(__('lang.package_size')),
                                ])
                                ->schema([

                                    TextEntry::make('unit.name')
                                        ->label(__('lang.unit'))
                                        ->weight(FontWeight::Bold)
                                        ->alignCenter(),
                                    TextEntry::make('selling_price')
                                        ->label(__('lang.selling_price'))
                                        ->money(fn() => $record->currency->code ?? 'SAR')
                                        ->color(Color::Green)
                                        ->weight(FontWeight::Bold)
                                        ->alignCenter(),
                                    TextEntry::make('stock')
                                        ->label(__('lang.stock'))
                                        ->numeric()
                                        ->badge()
                                        ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                                        ->alignCenter(),
                                    TextEntry::make('package_size')
                                        ->label(__('lang.package_size'))
                                        ->alignCenter(),

                                ])
                                ->columns(1),
                        ];
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
