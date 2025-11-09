{{-- resources/views/products/show.blade.php --}}
@php
    // Tiny helpers
    $title = $product->name;
    $badgeClasses = [
        'active'   => 'bg-emerald-100 text-emerald-700',
        'draft'    => 'bg-amber-100 text-amber-700',
        'inactive' => 'bg-gray-200 text-gray-700',
    ];
    $statusClass = $badgeClasses[$product->status] ?? 'bg-gray-200 text-gray-700';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-semibold">{{ $product->name }}</h1>
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
        <div class="rounded-2xl border border-gray-100 p-3">
            <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-gray-50 flex items-center justify-center">
                @if(($gallery ?? null) && count($gallery))
                    <img src="{{ $gallery[0]['url'] }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @elseif(($defaultVariant ?? null) && count($defaultVariant['images']))
                    <img src="{{ $defaultVariant['images'][0] }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @else
                    <div class="text-gray-400">No image</div>
                @endif
            </div>
            @if(($gallery ?? null) && count($gallery) > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach($gallery as $g)
                        <img src="{{ $g['url'] }}" class="h-16 w-full rounded-lg object-cover border border-gray-100" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Summary --}}
        <div class="rounded-2xl border border-gray-100 p-6">
            @if($product->short_description)
                <p class="text-gray-700 leading-relaxed">{{ $product->short_description }}</p>
            @endif

            @if(($optionMatrix ?? null) && count($optionMatrix))
                <div class="mt-6 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-800">Variant Options</h3>
                    @foreach($optionMatrix as $attr => $vals)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ $attr }}</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($vals as $v)
                                    <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700">
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

    {{-- Tabs --}}
    <div
        x-data="{ tab: 'details' }"
        x-cloak
        class="rounded-2xl border border-gray-100"
    >
        <div class="flex flex-wrap gap-2 border-b border-gray-100 px-4 pt-3">
            <button
                class="px-3 py-2 text-sm font-medium"
                :class="tab === 'details' ? 'border-b-2 border-indigo-600 text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
                @click="tab = 'details'">
                Details
            </button>
            <button
                class="px-3 py-2 text-sm font-medium"
                :class="tab === 'specs' ? 'border-b-2 border-indigo-600 text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
                @click="tab = 'specs'">
                Specifications
            </button>
            <button
                class="px-3 py-2 text-sm font-medium"
                :class="tab === 'variants' ? 'border-b-2 border-indigo-600 text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
                @click="tab = 'variants'">
                Variants
            </button>
            <button
                class="px-3 py-2 text-sm font-medium"
                :class="tab === 'media' ? 'border-b-2 border-indigo-600 text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
                @click="tab = 'media'">
                Media
            </button>
        </div>

        {{-- Tab: Details --}}
        <div x-show="tab === 'details'" class="p-6 space-y-4">
            @if($product->description)
                <article class="prose max-w-none">
                    {!! $product->description !!}
                </article>
            @else
                <p class="text-gray-500 text-sm">No detailed description.</p>
            @endif
        </div>

        {{-- Tab: Specs (Product attributes) --}}
        <div x-show="tab === 'specs'" class="p-6">
            @if(($specs ?? null) && $specs->count())
                <div class="overflow-hidden rounded-xl border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Attribute</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($specs as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['attribute'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row['value'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-sm">No specifications provided.</p>
            @endif
        </div>

        {{-- Tab: Variants --}}
        <div x-show="tab === 'variants'" class="p-6">
            @if(($variants ?? null) && $variants->count())
                <div class="overflow-hidden rounded-xl border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Default</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Options</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Weight</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Dimensions (L×W×H)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Images</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($variants as $v)
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        @if($v['is_default'])
                                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Default</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $v['sku'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $v['barcode'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5
                                            {{ ($v['status'] ?? '') === 'active' ? 'bg-emerald-100 text-emerald-700' :
                                               (($v['status'] ?? '') === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-gray-200 text-gray-700') }}">
                                            {{ ucfirst($v['status'] ?? 'unknown') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($v['options'] as $opt)
                                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-2 py-0.5 text-xs text-gray-700">
                                                    {{ $opt['attribute'] }}: <span class="ml-1 font-medium">{{ $opt['value'] }}</span>
                                                </span>
                                            @empty
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $v['weight'] ? $v['weight'].' kg' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        @php
                                            $d = $v['dimensions'] ?? [];
                                            $L = $d['length'] ?? null;
                                            $W = $d['width'] ?? null;
                                            $H = $d['height'] ?? null;
                                        @endphp
                                        {{ $L && $W && $H ? "{$L}×{$W}×{$H} cm" : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            @forelse($v['images'] as $img)
                                                <img src="{{ $img }}" class="h-10 w-10 rounded object-cover border border-gray-100" />
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
                <p class="text-gray-500 text-sm">No variants defined.</p>
            @endif
        </div>

        {{-- Tab: Media --}}
        <div x-show="tab === 'media'" class="p-6">
            @if(($gallery ?? null) && count($gallery))
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($gallery as $g)
                        <img src="{{ $g['url'] }}" class="h-36 w-full rounded-lg object-cover border border-gray-100" />
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No media available.</p>
            @endif
        </div>
    </div>
</div>

{{-- Optional: Alpine for tabs (safe no-op if not loaded) --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
