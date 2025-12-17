<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackProductViews
{
    /**
     * Handle an incoming request.
     * تتبع المشاهدات للمنتجات والمتغيرات
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // تتبع عند الوصول الناجح فقط
        if ($response->isSuccessful()) {
            $this->trackViews($request);
        }

        return $response;
    }

    /**
     * تتبع المشاهدات بناءً على الـ route parameters
     */
    protected function trackViews(Request $request): void
    {
        // تتبع المنتج
        if ($request->route('product')) {
            $product = $request->route('product');
            if ($product instanceof \App\Models\Product) {
                $product->recordView();
            } elseif (is_numeric($product)) {
                \App\Models\Product::find($product)?->recordView();
            }
        }

        // تتبع المتغير
        if ($request->route('variant')) {
            $variant = $request->route('variant');
            if ($variant instanceof \App\Models\ProductVariant) {
                $variant->recordView();
            } elseif (is_numeric($variant)) {
                \App\Models\ProductVariant::find($variant)?->recordView();
            }
        }

        // تتبع من الـ ProductVendorSku (عند عرض عرض التاجر)
        if ($request->route('productVendorSku') || $request->route('offer')) {
            $sku = $request->route('productVendorSku') ?? $request->route('offer');
            if ($sku instanceof \App\Models\ProductVendorSku) {
                $sku->product?->recordView();
                $sku->variant?->recordView();
            } elseif (is_numeric($sku)) {
                $skuModel = \App\Models\ProductVendorSku::with(['product', 'variant'])->find($sku);
                $skuModel?->product?->recordView();
                $skuModel?->variant?->recordView();
            }
        }
    }
}
