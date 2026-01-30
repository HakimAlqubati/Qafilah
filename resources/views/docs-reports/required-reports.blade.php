@extends('layouts.docs-layout')

@section('title', $data['title'])
@section('page-title', $data['title'])
@section('page-subtitle', $data['date'])

@push('styles')
<style>
    .tab-btn.active {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .priority-high {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .priority-medium {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .priority-low {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .detail-card {
        transition: all 0.3s ease;
    }

    .detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-100 dark:border-gray-700 overflow-hidden flex flex-col md:flex-row">
    <!-- Sidebar Tabs -->
    <div class="md:w-56 bg-gray-50 dark:bg-gray-700/50 md:border-l border-b md:border-b-0 dark:border-gray-600 flex-shrink-0">
        <div class="flex md:flex-col overflow-x-auto md:overflow-x-visible">
            @foreach ($data['sections'] as $index => $section)
            <button onclick="showTab({{ $index }})" id="tab-btn-{{ $index }}"
                class="tab-btn flex-shrink-0 md:w-full py-4 px-4 text-right font-semibold text-gray-600 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-gray-600 transition border-l md:border-l-0 md:border-b dark:border-gray-600 {{ $index === 0 ? 'active' : '' }}">
                <div class="flex items-center gap-2 justify-end">
                    <span>{{ $section['title'] }}</span>
                    @if (isset($section['priority']))
                    <span class="w-2 h-2 rounded-full priority-{{ $section['priority'] }}"></span>
                    @endif
                </div>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Content Area -->
    <div class="flex-1 min-h-[500px]">
        @foreach ($data['sections'] as $sIndex => $section)
        <div id="tab-content-{{ $sIndex }}" class="tab-content p-6 md:p-8 {{ $sIndex === 0 ? 'active' : '' }}">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $section['title'] }}</h2>
                        @if (isset($section['priority']))
                        <span class="px-3 py-1 text-xs font-bold text-white rounded-full priority-{{ $section['priority'] }}">
                            @if ($section['priority'] === 'high') أولوية عالية
                            @elseif ($section['priority'] === 'medium') أولوية متوسطة
                            @else أولوية منخفضة
                            @endif
                        </span>
                        @endif
                    </div>
                    <p class="text-orange-600 dark:text-orange-400 font-medium text-lg">{{ $section['question'] }}</p>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-gray-700 dark:to-gray-600 rounded-xl p-4 mb-6">
                <p class="text-gray-700 dark:text-gray-200 font-medium">{{ $section['description'] }}</p>
            </div>

            <!-- Details Section -->
            @if (!empty($section['details']))
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    التفاصيل الأساسية
                </h3>
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach ($section['details'] as $key => $value)
                    <div class="detail-card bg-white dark:bg-gray-700 rounded-xl p-4 border border-gray-100 dark:border-gray-600">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ $key }}</p>
                        <p class="font-semibold text-gray-800 dark:text-white text-sm">{{ $value }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Filters Section -->
            @if (!empty($section['filters']))
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    الفلاتر المتاحة
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($section['filters'] as $filter)
                    <span class="px-3 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-sm font-medium">
                        {{ $filter }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Outputs Section -->
            @if (!empty($section['outputs']))
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    المخرجات
                </h3>
                <div class="grid md:grid-cols-2 gap-3">
                    @foreach ($section['outputs'] as $output)
                    <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700 dark:text-gray-200 text-sm">{{ $output }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Implementation Steps -->
            @if (!empty($section['implementationSteps']))
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                    خطوات التنفيذ
                </h3>
                <div class="space-y-4">
                    @foreach ($section['implementationSteps'] as $step)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-r-4 border-purple-500">
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $loop->iteration }}</span>
                            <div class="flex-1">
                                <p class="font-bold text-gray-800 dark:text-white">{{ $step['action'] }}</p>
                                <p class="text-orange-600 dark:text-orange-400 text-sm font-mono mt-1">{{ $step['file'] }}</p>
                                <p class="text-gray-600 dark:text-gray-300 text-sm mt-2">{{ $step['details'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Additional Reports (for the last tab) -->
            @if (!empty($section['additionalReports']))
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    قائمة التقارير الإضافية
                </h3>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach ($section['additionalReports'] as $report)
                    <div class="detail-card bg-white dark:bg-gray-700 rounded-xl p-4 border border-gray-100 dark:border-gray-600">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-bold text-gray-800 dark:text-white">{{ $report['name'] }}</h4>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if ($report['priority'] === 'عالي') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @elseif ($report['priority'] === 'متوسط') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                @else bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                @endif">
                                {{ $report['priority'] }}
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">{{ $report['description'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showTab(index) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-content-' + index).classList.add('active');
        document.getElementById('tab-btn-' + index).classList.add('active');
    }
</script>
@endpush