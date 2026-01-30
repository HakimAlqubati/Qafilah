<x-filament-panels::page>
    {{ $this->form }}

    @php
    $data = $this->reportData;
    $summary = $data['summary'];
    @endphp

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.total_revenue') }}</p>
                    <h3 class="text-2xl font-bold text-primary-600">{{ $summary->getFormattedRevenue() }}</h3>
                </div>
                <div class="p-3 bg-primary-50 rounded-full dark:bg-primary-900">
                    <x-heroicon-o-currency-dollar class="w-6 h-6 text-primary-600 dark:text-primary-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.total_orders') }}</p>
                    <h3 class="text-2xl font-bold">{{ $summary->ordersCount }}</h3>
                </div>
                <div class="p-3 bg-blue-50 rounded-full dark:bg-blue-900">
                    <x-heroicon-o-shopping-cart class="w-6 h-6 text-blue-600 dark:text-blue-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.average_order_value') }}</p>
                    <h3 class="text-2xl font-bold">{{ $summary->getFormattedAverageValue() }}</h3>
                </div>
                <div class="p-3 bg-green-50 rounded-full dark:bg-green-900">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-green-600 dark:text-green-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('lang.total_items_sold') }}</p>
                    <h3 class="text-2xl font-bold">{{ $summary->itemsCount }}</h3>
                </div>
                <div class="p-3 bg-orange-50 rounded-full dark:bg-orange-900">
                    <x-heroicon-o-cube class="w-6 h-6 text-orange-600 dark:text-orange-400" style="width: 24px; height: 24px;" />
                </div>
            </div>
        </x-filament::section>

    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Top Products --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('lang.top_products') }}
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">{{ __('lang.product') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('lang.quantity') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('lang.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_products'] as $index => $product)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                {{ $product->productName }}
                            </td>
                            <td class="px-4 py-2 text-center">{{ $product->quantitySold }}</td>
                            <td class="px-4 py-2 text-right">{{ $product->totalRevenue }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center">{{ __('lang.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Top Vendors --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('lang.top_vendors') }}
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2">{{ __('lang.vendor') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('lang.orders') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('lang.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_vendors'] as $vendor)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                {{ $vendor->vendorName }}
                            </td>
                            <td class="px-4 py-2 text-center">{{ $vendor->ordersCount }}</td>
                            <td class="px-4 py-2 text-right">{{ $vendor->totalRevenue }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center">{{ __('lang.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>