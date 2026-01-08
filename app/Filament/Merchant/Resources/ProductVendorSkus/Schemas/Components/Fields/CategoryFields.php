<?php

namespace App\Filament\Merchant\Resources\ProductVendorSkus\Schemas\Components\Fields;

use App\Models\Category;
use Filament\Forms\Components\Select;

class CategoryFields
{
    /**
     * Get the main category select field
     */
    public static function mainCategory(): Select
    {
        return Select::make('main_category_id')
            ->label(__('lang.main_category'))
            ->searchable()
            ->options(function () {
                return Category::whereNull('parent_id')
                    ->active()
                    ->pluck('name', 'id');
            })
            ->getOptionLabelUsing(fn($value) => Category::find($value)?->name)
            ->live()
            ->afterStateUpdated(function ($set) {
                $set('sub_category_id', null);
                $set('product_id', null);
                $set('attributes', []);
            })
            ->required();
    }

    /**
     * Get the sub category select field
     */
    public static function subCategory(): Select
    {
        return Select::make('sub_category_id')
            ->label(__('lang.sub_category'))
            ->searchable()
            ->options(function ($get, $state) {
                $mainCategoryId = $get('main_category_id');

                $query = Category::query()->whereNotNull('parent_id');

                if ($mainCategoryId) {
                    $query->where('parent_id', $mainCategoryId);
                }

                // Include currently selected subcategory
                if ($state) {
                    $query->orWhere('id', $state);
                }

                return $query->active()->pluck('name', 'id');
            })
            ->getOptionLabelUsing(fn($value) => Category::find($value)?->name)
            ->live()
            ->afterStateUpdated(function ($set) {
                $set('product_id', null);
                $set('attributes', []);
            });
    }

    /**
     * Get all category fields
     */
    public static function make(): array
    {
        return [
            self::mainCategory(),
            self::subCategory(),
        ];
    }
}
