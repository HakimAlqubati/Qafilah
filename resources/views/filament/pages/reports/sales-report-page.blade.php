<x-filament-panels::page>
    {{ $this->form }}

    @php
    $data = $this->reportData;
    $summary = $data['summary'];
    @endphp

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-filament::section class="border-l-4 border-primary-500">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.total_revenue') }}</p>
                    <h3 class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $summary->getFormattedRevenue() }}</h3>
                </div>
                <div class="flex items-center justify-center p-3 rounded-full bg-primary-50 dark:bg-primary-900/50">
                    <x-heroicon-o-currency-dollar class="w-6 h-6 text-primary-600 dark:text-primary-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

        <x-filament::section class="border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.total_orders') }}</p>
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $summary->ordersCount }}</h3>
                </div>
                <div class="flex items-center justify-center p-3 rounded-full bg-blue-50 dark:bg-blue-900/50">
                    <x-heroicon-o-shopping-cart class="w-6 h-6 text-blue-600 dark:text-blue-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

        <x-filament::section class="border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.average_order_value') }}</p>
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $summary->getFormattedAverageValue() }}</h3>
                </div>
                <div class="flex items-center justify-center p-3 rounded-full bg-green-50 dark:bg-green-900/50">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-green-600 dark:text-green-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

        <x-filament::section class="border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.total_items_sold') }}</p>
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $summary->itemsCount }}</h3>
                </div>
                <div class="flex items-center justify-center p-3 rounded-full bg-orange-50 dark:bg-orange-900/50">
                    <x-heroicon-o-cube class="w-6 h-6 text-orange-600 dark:text-orange-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Products --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-star class="w-5 h-5 text-yellow-500" style="width: 20px; height: 20px;" />
                    <span>{{ __('lang.top_products') }}</span>
                </div>
            </x-slot>

            <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                <table class="w-full text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase font-medium text-gray-700 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start w-12">#</th>
                            <th scope="col" class="px-6 py-3 text-start">{{ __('lang.product') }}</th>
                            <th scope="col" class="px-6 py-3 text-center w-24">{{ __('lang.quantity') }}</th>
                            <th scope="col" class="px-6 py-3 text-end w-32">{{ __('lang.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['top_products'] as $index => $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-start text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-start font-medium text-gray-900 dark:text-white truncate max-w-[200px]" title="{{ $product->productName }}">
                                {{ $product->productName }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $product->quantitySold }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-end font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                {{ $product->totalRevenue }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50 dark:bg-gray-800/50 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <x-heroicon-o-inbox class="w-8 h-8 text-gray-400" style="width: 32px; height: 32px;" />
                                    <span>{{ __('lang.no_data_available') }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Top Vendors --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-blue-500" style="width: 20px; height: 20px;" />
                    <span>{{ __('lang.top_vendors') }}</span>
                </div>
            </x-slot>

            <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                <table class="w-full text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase font-medium text-gray-700 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start">{{ __('lang.vendor') }}</th>
                            <th scope="col" class="px-6 py-3 text-center w-24">{{ __('lang.orders') }}</th>
                            <th scope="col" class="px-6 py-3 text-end w-32">{{ __('lang.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['top_vendors'] as $vendor)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-start font-medium text-gray-900 dark:text-white">
                                {{ $vendor->vendorName }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                    {{ $vendor->ordersCount }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-end font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                {{ $vendor->totalRevenue }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50 dark:bg-gray-800/50 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <x-heroicon-o-inbox class="w-8 h-8 text-gray-400" style="width: 32px; height: 32px;" />
                                    <span>{{ __('lang.no_data_available') }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>

</x-filament-panels::page>