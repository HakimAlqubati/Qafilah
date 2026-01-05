<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Tables;

use App\Models\ProductVendorSku;
use App\Models\Unit;
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
use Filament\Schemas\Components\Section;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
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

                TextColumn::make('variants_count')
                    ->label(__('lang.variants'))
                    ->state(function ($record) {
                        $count = ProductVendorSku::where('vendor_id', $record->vendor_id)
                            ->where('product_id', $record->product_id)
                            ->whereNotNull('variant_id')
                            ->count();
                        return $count > 0 ? $count . ' ' . __('lang.variants') : __('lang.not_variants');
                    })
                    ->badge()
                    ->color(fn($state) => str_contains($state, __('lang.simple_product')) ? 'gray' : 'info')
                    ->hidden(),

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
                    ->searchable()
                    ->hidden(),

                TextColumn::make('currency.code')
                    ->label(__('lang.currency'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                // سعر البيع
                TextColumn::make('selling_price')
                    ->label(__('lang.selling_price'))
                    ->state(fn($record) => $record->units->first()?->selling_price ?? 0)
                    ->money(fn($record) => $record->currency->code )
                    ->color(Color::Green)
                    ->weight(FontWeight::Bold)
                    ->sortable(
                        query: fn($query, $direction) =>
                        $query->orderBy(
                            \App\Models\ProductVendorSkuUnit::select('selling_price')
                                ->whereColumn('product_vendor_sku_id', 'product_vendor_skus.id')
                                ->limit(1),
                            $direction
                        )
                    ),

                // سعر الشراء (التكلفة)
                TextColumn::make('cost_price')
                    ->label(__('lang.cost_price'))
                    ->state(fn($record) => $record->units->first()?->cost_price ?? 0)
                    ->money(fn($record) => $record->currency->code ?? 'SAR')
                    ->color(Color::Gray),

                // المخزون
                TextColumn::make('stock')
                    ->label(__('lang.stock'))->alignCenter()
                    ->state(fn($record) => $record->units->first()?->stock ?? 0)
                    ->numeric()
                    ->color(fn($state) => $state > 0 ? Color::Green : Color::Red)
                    ->badge(),

                TextColumn::make('status')
                    ->label(__('lang.status'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                \Filament\Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('lang.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->label(__('lang.category'))
                    ->options(fn() => \App\Models\Category::whereNull('parent_id')->active()->pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $categoryIds = \App\Models\Category::where('id', $data['value'])
                                ->orWhere('parent_id', $data['value'])
                                ->pluck('id');
                            $query->whereHas('product', fn($q) => $q->whereIn('category_id', $categoryIds));
                        }
                    }),

                \Filament\Tables\Filters\SelectFilter::make('currency_id')
                    ->label(__('lang.currency'))
                    ->relationship('currency', 'code')
                    ->preload(),

                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label(__('lang.status'))
                    ->options([
                        'available' => __('lang.available'),
                        'out_of_stock' => __('lang.out_of_stock'),
                        'inactive' => __('lang.inactive'),
                    ]),

                TrashedFilter::make(),
            ], FiltersLayout::Modal)->filtersFormColumns(4)

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
                        $allSkus = ProductVendorSku::with(['variant.values.attribute', 'units.unit', 'currency'])
                            ->where('vendor_id', $record->vendor_id)
                            ->where('product_id', $record->product_id)
                            ->get();

                        $currencyCode = $record->currency->code ?? 'SAR';
                        $schemas = [];

                        foreach ($allSkus as $sku) {
                            $variantLabel = $sku->variant
                                ? $sku->variant->values->map(fn($v) => $v->attribute->name . ': ' . $v->displayValue())->join(' | ')
                                : __('lang.simple_product');

                            // Build units info as simple text entries
                            $unitEntries = [];
                            foreach ($sku->units as $unitIndex => $unit) {
                                $unitName = $unit->unit?->name ?? '-';
                                $unitEntries[] = Grid::make(4)->schema([
                                    TextEntry::make("unit_name_{$sku->id}_{$unitIndex}")
                                        ->label(__('lang.unit'))
                                        ->default($unitName)
                                        ->weight(FontWeight::Bold),
                                    TextEntry::make("selling_price_{$sku->id}_{$unitIndex}")
                                        ->label(__('lang.selling_price'))
                                        ->default(number_format($unit->selling_price, 2) . ' ' . $currencyCode)
                                        ->color(Color::Green)
                                        ->weight(FontWeight::Bold),
                                    TextEntry::make("stock_{$sku->id}_{$unitIndex}")
                                        ->label(__('lang.stock'))
                                        ->default($unit->stock)
                                        ->badge()
                                        ->color($unit->stock > 0 ? 'success' : 'danger'),
                                    TextEntry::make("package_size_{$sku->id}_{$unitIndex}")
                                        ->label(__('lang.package_size'))
                                        ->default($unit->package_size ?? 1),
                                ]);
                            }

                            $schemas[] = Section::make($variantLabel)
                                ->schema($unitEntries)
                                ->collapsible()
                                ->collapsed(false);
                        }

                        return $schemas;
                    })->hidden(),
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
