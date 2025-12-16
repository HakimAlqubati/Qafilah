<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Arr;

class ProductShowController extends Controller
{
    public function show(string $idOrSlug)
    {
        $product = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'attributeSet:id,name',
                'attributes.attribute:id,name,input_type',
                'variants' => function ($q) {
                    $q->with([
                        'values.attribute:id,name',
                        'values.attributeValue:id,attribute_id,value',
                        'media',            // images for the variant (if you use them)
                        'values.media',     // swatches/images on ProductVariantValue
                    ])->orderBy('is_default', 'desc');
                },
                'media', // product images
                // eager load ALL vendor offers (both direct product offers and variant offers)
                'offers.vendor',
                'offers.currency',
                'offers.media',
                'offers.units.unit',
            ])
            ->where(function ($query) use ($idOrSlug) {
                // الافتراضي البحث بالـ slug
                $query->where('slug', $idOrSlug);

                // إذا كان المدخل رقمياً، نضيف شرط البحث بالـ id (orWhere)
                if (is_numeric($idOrSlug)) {
                    $query->orWhere('id', $idOrSlug);
                }
            })->firstOrFail();

        // ----- Product attributes (descriptive) as flat rows
        $specs = $product->attributes->map(function ($pa) {
            return [
                'attribute' => optional($pa->attribute)->name,
                'value'     => (string) $pa->value,
            ];
        })->filter(fn($row) => $row['attribute']);

        // ----- Variants grid (each row shows combined option values)
        $variants = $product->variants->map(function ($v) {
            $optionPairs = $v->values->map(function ($pv) {
                return [
                    'attribute' => optional($pv->attribute)->name,
                    'value'     => optional($pv->value)->value,
                    'swatches'  => $pv->getMedia()->map(fn($m) => $m->getUrl())->all(), // optional
                ];
            })->filter(fn($x) => $x['attribute'] && $x['value'])->values();

            return [
                'id'         => $v->id,
                'sku'        => $v->master_sku,
                'barcode'    => $v->barcode,
                'status'     => $v->status,
                'is_default' => (bool) $v->is_default,
                'weight'     => $v->weight,
                'dimensions' => $v->dimensions,  // [length, width, height]
                'images'     => $v->getMedia('variant_images')->map(fn($m) => $m->getUrl())->all(),
                'options'    => $optionPairs,
            ];
        });
        // dd($variants);

        // ----- Group variant options by attribute (for quick filter/legend)
        $optionMatrix = [];
        foreach ($variants as $row) {
            foreach ($row['options'] as $opt) {
                $optionMatrix[$opt['attribute']] = $optionMatrix[$opt['attribute']] ?? [];
                $optionMatrix[$opt['attribute']][] = $opt['value'];
            }
        }
        foreach ($optionMatrix as $attr => $vals) {
            $optionMatrix[$attr] = array_values(array_unique($vals));
        }

        // ----- Default variant (for hero image or default pick)
        $defaultVariant = $variants->firstWhere('is_default', true) ?? $variants->first();

        // ----- Product hero/gallery
        $gallery = $product->getMedia()->map(fn($m) => [
            'url'   => $m->getUrl(),
            // 'thumb' => method_exists($m, 'getUrl') ? $m->getUrl('thumb') : $m->getUrl(),
        ])->all();

        return view('products.show', [
            'product'        => $product,
            'gallery'        => $gallery,
            'specs'          => $specs,
            'variants'       => $variants,
            'optionMatrix'   => $optionMatrix,
            'defaultVariant' => $defaultVariant,
            // group vendor offers by vendor for tabs (includes both direct and variant offers)
            'vendorTabs' => $product->offers->groupBy('vendor_id'),
        ]);
    }
}
