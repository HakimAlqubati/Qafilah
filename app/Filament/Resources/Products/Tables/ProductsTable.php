<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Product; // Ensure the Product model is imported
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        $product = Product::find(22);
        // dd($product->default_image);
        return $table->striped()
            ->columns([
                // 1. Name & SKU (Primary identifiers)
                TextColumn::make('id')
                    ->label(__('lang.id'))
                    ->searchable()->alignCenter()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('lang.product_name'))
                    ->searchable()
                    ->sortable()
                // ->description(fn(Product $record): string => __('lang.sku') . ": {$record->sku}")
                , // Display SKU beneath the name
                SpatieMediaLibraryImageColumn::make('default')->label(__('lang.images'))->imageSize(50)
                    ->circular()->alignCenter(true)->getStateUsing(function () {
                        return null;
                    })->limit(2)
                    ->stacked()
                    ->limitedRemainingText(),

                // الصورة الافتراضية (الأولى في الترتيب)
                ImageColumn::make('default_image')
                    ->label(__('lang.default_image'))
                    ->imageSize(50)
                    ->circular()
                    ->alignCenter(true),

                TextColumn::make('slug')
                    ->label(__('lang.slug'))
                    // ->icon(Heroicon::)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Slug copied to clipboard!')
                    ->copyMessageDuration(1500)
                    ->limit(40),

                // 2. Status (Visibility and workflow)
                TextColumn::make('status')
                    ->label(__('lang.status'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        Product::$STATUSES['ACTIVE'] => 'success',
                        Product::$STATUSES['DRAFT'] => 'warning',
                        Product::$STATUSES['INACTIVE'] => 'danger',
                        default => 'secondary',
                    }),

                // 3. Categorization
                TextColumn::make('category.name')
                    ->label(__('lang.category'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('brand.name')
                    ->label(__('lang.brand'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),



                // 5. Inventory (Variants or simple stock count)
                TextColumn::make('variants_count')
                    ->label(__('lang.variants'))->alignCenter()
                    ->counts('variants') // Count the number of associated variants
                    ->sortable()->toggleable(),

                // عدد الخصائص (Attributes)
                TextColumn::make('attributes_direct_count')
                    ->label(__('lang.attributes_count'))
                    ->alignCenter()
                    ->counts('attributesDirect')
                    ->sortable()
                    ->toggleable(),

                // عدد الوحدات (Units)
                TextColumn::make('units_count')
                    ->label(__('lang.units_count'))
                    ->alignCenter()
                    ->counts('units')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 6. Featured Status
                IconColumn::make('is_featured')
                    ->label(__('lang.featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 7. Audit: Soft Deletes
                TextColumn::make('deleted_at')
                    ->label(__('lang.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Status
                SelectFilter::make('status')
                    ->options(Product::statusOptions()),

                // Filter by Category
                SelectFilter::make('category_id')
                    ->label(__('lang.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                // Trashed filter (for Soft Deletes)
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(), // Explicitly added for single record soft delete
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
