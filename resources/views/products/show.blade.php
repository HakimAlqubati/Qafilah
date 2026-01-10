{{-- resources/views/products/show.blade.php --}}
@php
// Tiny helpers
$title = $product->name;
$badgeClasses = [
'active' => 'bg-emerald-100 text-emerald-700',
'draft' => 'bg-amber-100 text-amber-700',
'inactive' => 'bg-gray-200 text-gray-700',
];
$statusClass = $badgeClasses[$product->status] ?? 'bg-gray-200 text-gray-700';

// نجهز أول ID للتاجر ليكون هو المختار افتراضياً عند فتح تاب التجار
$firstVendorId = ($vendorTabs ?? null) && $vendorTabs->count() ? $vendorTabs->keys()->first() : 0;
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">{{ $product->name }}</h1>
            <div class="mt-2 text-sm text-gray-500 space-x-2">
                @if($product->brand) <span>Brand: <span class="font-medium text-gray-700">{{ $product->brand->name }}</span></span>@endif
                @if($product->category) <span>• Category: <span class="font-medium text-gray-700">{{ $product->category->name }}</span></span>@endif
                @if($product->attributeSet) <span>• Attribute Set: <span class="font-medium text-gray-700">{{ $product->attributeSet->name }}</span></span>@endif
            </div>
        </div>
        <div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                {{ ucfirst($product->status) }}
            </span>
            @if($product->is_featured)
            <span class="ml-2 inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">
                Featured
            </span>
            @endif
        </div>
    </div>

    {{-- Top section: gallery + summary --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- Gallery --}}
        <div class="rounded-2xl border border-gray-100 p-3 bg-white shadow-sm">
            <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-gray-50 flex items-center justify-center relative group">
                @if(($gallery ?? null) && count($gallery))
                <img src="{{ $gallery[0]['url'] }}" alt="{{ $product->name }}" class="h-full w-full object-contain">
                @elseif(($defaultVariant ?? null) && count($defaultVariant['images']))
                <img src="{{ $defaultVariant['images'][0] }}" alt="{{ $product->name }}" class="h-full w-full object-contain">
                @else
                <div class="text-gray-400 flex flex-col items-center">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>No image</span>
                </div>
                @endif
            </div>
            @if(($gallery ?? null) && count($gallery) > 1)
            <div class="mt-3 grid grid-cols-5 gap-2">
                @foreach($gallery as $g)
                <img src="{{ $g['url'] }}" class="h-16 w-full rounded-lg object-cover border border-gray-100 cursor-pointer hover:border-indigo-500 transition-colors" />
                @endforeach
            </div>
            @endif
        </div>

        {{-- Summary --}}
        <div class="rounded-2xl border border-gray-100 p-6 bg-white shadow-sm">
            @if($product->short_description)
            <p class="text-gray-700 leading-relaxed">{{ $product->short_description }}</p>
            @endif

            @if(($optionMatrix ?? null) && count($optionMatrix))
            <div class="mt-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Variant Options</h3>
                @foreach($optionMatrix as $attr => $vals)
                <div>
                    <div class="text-xs text-gray-500 mb-1.5 font-medium">{{ $attr }}</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($vals as $v)
                        <span class="inline-flex items-center rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1 text-sm text-gray-700">
                            {{ $v }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Default Image Section --}}
    @if($product->default_image)
    <div class="mb-8">
        <div class="rounded-2xl border border-gray-100 p-6 bg-white shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('lang.default_image') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('lang.default_image_description') }}</p>
                </div>
            </div>
            <div class="flex justify-center">
                <div class="relative group">
                    <img
                        src="{{ $product->default_image }}"
                        alt="{{ $product->name }} - {{ __('lang.default_image') }}"
                        class="max-w-md w-full h-auto rounded-xl border border-gray-200 shadow-sm object-contain transition-transform duration-300 group-hover:scale-105" />
                    <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-medium text-white shadow-lg">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        {{ __('lang.default_image') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabs Section --}}
    <div
        x-data="{ tab: 'details' }"
        x-cloak
        class="rounded-2xl border border-gray-100 shadow-sm bg-white overflow-hidden">

        {{-- Main Tab Navigation (Horizontal) --}}
        <div class="flex flex-wrap gap-2 border-b border-gray-100 px-4 pt-3 bg-gray-50/50">
            <button
                class="px-4 py-2.5 text-sm font-medium transition-colors duration-200 border-b-2 rounded-t-lg"
                :class="tab === 'details' ? 'border-indigo-600 text-indigo-700 bg-white shadow-sm' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                @click="tab = 'details'">
                Details
            </button>
            <button
                class="px-4 py-2.5 text-sm font-medium transition-colors duration-200 border-b-2 rounded-t-lg"
                :class="tab === 'specs' ? 'border-indigo-600 text-indigo-700 bg-white shadow-sm' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                @click="tab = 'specs'">
                Specifications
            </button>
            <button
                class="px-4 py-2.5 text-sm font-medium transition-colors duration-200 border-b-2 rounded-t-lg"
                :class="tab === 'variants' ? 'border-indigo-600 text-indigo-700 bg-white shadow-sm' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                @click="tab = 'variants'">
                Variants
            </button>
            <button
                class="px-4 py-2.5 text-sm font-medium transition-colors duration-200 border-b-2 rounded-t-lg"
                :class="tab === 'media' ? 'border-indigo-600 text-indigo-700 bg-white shadow-sm' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                @click="tab = 'media'">
                Media
            </button>

            {{-- Vendors Tab (Main) --}}
            <button
                class="px-4 py-2.5 text-sm font-medium transition-colors duration-200 border-b-2 rounded-t-lg flex items-center gap-2"
                :class="tab === 'vendors' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                @click="tab = 'vendors'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Vendors
                @if(($vendorTabs ?? null) && $vendorTabs->count())
                <span class="ml-1 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ $vendorTabs->count() }}</span>
                @endif
            </button>
        </div>

        {{-- Tab Contents --}}
        <div class="min-h-[300px]">

            {{-- 1. Details --}}
            <div x-show="tab === 'details'" class="p-6 md:p-8 animate-fade-in">
                @if($product->description)
                <article class="prose prose-indigo max-w-none text-gray-600">
                    {!! $product->description !!}
                </article>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-400 italic">No detailed description provided.</p>
                </div>
                @endif
            </div>

            {{-- 2. Specs --}}
            <div x-show="tab === 'specs'" class="p-6 md:p-8" style="display: none;">
                @if(($specs ?? null) && $specs->count())
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Attribute</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($specs as $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-700 bg-gray-50/30 w-1/3">{{ $row['attribute'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $row['value'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-400 italic">No specifications provided.</p>
                </div>
                @endif
            </div>

            {{-- 3. Variants --}}
            <div x-show="tab === 'variants'" class="p-6 md:p-8" style="display: none;">
                @if(($variants ?? null) && $variants->count())
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Options</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Weight / Dims</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Images</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($variants as $v)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $v['sku'] ?? '-' }}
                                    @if($v['is_default'])
                                    <span class="ml-2 inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Default</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($v['options'] as $opt)
                                        <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700 border border-gray-200">
                                            {{ $opt['attribute'] }}: <b>{{ $opt['value'] }}</b>
                                        </span>
                                        @empty
                                        <span class="text-gray-400 text-xs">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <div class="flex flex-col text-xs">
                                        <span>{{ $v['weight'] ? $v['weight'].' kg' : '-' }}</span>
                                        @php
                                        $d = $v['dimensions'] ?? [];
                                        @endphp
                                        <span class="text-gray-400">{{ ($d['length'] ?? false) ? "{$d['length']}×{$d['width']}×{$d['height']} cm" : '' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-medium
                                                {{ ($v['status'] ?? '') === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' :
                                                  (($v['status'] ?? '') === 'draft' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20') }}">
                                        {{ ucfirst($v['status'] ?? 'unknown') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex -space-x-2 overflow-hidden hover:space-x-1 transition-all">
                                        @forelse($v['images'] as $img)
                                        <img src="{{ $img }}" class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover" />
                                        @empty
                                        <span class="text-gray-400 text-xs">—</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-400 italic">No variants defined.</p>
                </div>
                @endif
            </div>

            {{-- 4. Media --}}
            <div x-show="tab === 'media'" class="p-6 md:p-8" style="display: none;">
                @if(($gallery ?? null) && count($gallery))
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($gallery as $g)
                    <div class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 border border-gray-200">
                        <img src="{{ $g['url'] }}" class="h-full w-full object-cover object-center transition duration-300 group-hover:scale-110" />
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-400 italic">No additional media available.</p>
                </div>
                @endif
            </div>

            {{-- 5. Vendors (Nested Layout) --}}
            <div x-show="tab === 'vendors'" style="display: none;">
                @if(($vendorTabs ?? null) && $vendorTabs->count())
                {{-- Alpine Component for Vertical Tabs --}}
                <div x-data="{ activeVendor: {{ $firstVendorId }} }" class="flex flex-col md:flex-row min-h-[400px]">

                    {{-- Sidebar (Vertical Tabs) --}}
                    <div class="w-full md:w-64 flex-shrink-0 border-b md:border-b-0 md:border-r border-gray-100 bg-gray-50/50">
                        <div class="p-4">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Available Vendors</h3>
                            <div class="space-y-1">
                                @foreach($vendorTabs as $vendorId => $offers)
                                @php $vendorName = $offers->first()->vendor->name ?? 'Vendor #'.$vendorId; @endphp
                                <button
                                    @click="activeVendor = {{ $vendorId }}"
                                    class="w-full text-left px-3 py-2.5 text-sm font-medium rounded-lg transition-all flex items-center justify-between group"
                                    :class="activeVendor === {{ $vendorId }} ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:bg-white hover:text-gray-900'">
                                    <span>{{ $vendorName }}</span>
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full"
                                        :class="activeVendor === {{ $vendorId }} ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-200 text-gray-500 group-hover:bg-gray-100'">
                                        {{ $offers->count() }}
                                    </span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Content Area --}}
                    <div class="flex-1 p-6 bg-white">
                        @foreach($vendorTabs as $vendorId => $offers)
                        @php $vendorObj = $offers->first()->vendor; @endphp
                        <div x-show="activeVendor === {{ $vendorId }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

                            {{-- Selected Vendor Header --}}
                            <div class="flex items-start justify-between mb-6 pb-4 border-b border-gray-100">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ $vendorObj->name ?? 'Vendor' }}</h2>
                                    <p class="text-sm text-gray-500 mt-1">Showing all available offers for this product from this vendor.</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Authorized Vendor</span>
                                </div>
                            </div>

                            {{-- Offers Table --}}
                            <div class="overflow-hidden rounded-xl border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendor SKU</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cost & Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Preview</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($offers as $offer)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            {{-- Variant Info --}}


                                            {{-- Vendor SKU --}}
                                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                                {{ $offer->vendor_sku ?? '-' }}
                                            </td>

                                            {{-- Pricing --}}
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900">
                                                    {{ number_format($offer->selling_price, 2) }}
                                                    <span class="text-xs font-normal text-gray-500">{{ $offer->currency->code ?? 'USD' }}</span>
                                                </div>
                                                @if($offer->cost_price > 0)
                                                <div class="text-xs text-gray-400 mt-0.5">Cost: {{ number_format($offer->cost_price, 2) }}</div>
                                                @endif
                                            </td>

                                            {{-- Stock --}}
                                            <td class="px-6 py-4">
                                                @if($offer->isAvailable())
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    {{ $offer->stock }} in stock
                                                </span>
                                                @else
                                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                                    {{ $offer->getStatusLabel() }}
                                                </span>
                                                @endif
                                                @if($offer->moq > 1)
                                                <div class="text-[10px] text-gray-500 mt-1 uppercase tracking-wide">MOQ: {{ $offer->moq }}</div>
                                                @endif
                                            </td>

                                            {{-- Images --}}
                                            <td class="px-6 py-4">
                                                @php $offerImages = $offer->getMedia('images'); @endphp
                                                @if($offerImages->count())
                                                <div class="flex -space-x-2 overflow-hidden hover:space-x-1 transition-all">
                                                    @foreach($offerImages as $img)
                                                    <img
                                                        class="inline-block h-10 w-10 rounded-lg ring-2 ring-white object-cover cursor-pointer hover:z-10 hover:scale-125 transition-transform shadow-sm"
                                                        src="{{ $img->getUrl() }}" />
                                                    @endforeach
                                                </div>
                                                @else
                                                <span class="text-xs text-gray-400 italic">No images</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="text-center py-16">
                    <div class="mx-auto h-12 w-12 text-gray-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No Vendors</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no vendor offers associated with this product yet.</p>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Optional: Alpine for tabs (safe no-op if not loaded) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</div>
@endsection