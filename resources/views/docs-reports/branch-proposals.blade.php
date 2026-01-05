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
</style>
@endpush

@section('content')
<div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-100 dark:border-gray-700 overflow-hidden flex flex-col md:flex-row">
    <!-- Sidebar Tabs -->
    <div class="md:w-48 bg-gray-50 dark:bg-gray-700/50 md:border-l border-b md:border-b-0 dark:border-gray-600 flex-shrink-0">
        <div class="flex md:flex-col overflow-x-auto md:overflow-x-visible">
            @foreach ($data['sections'] as $index => $section)
            <button onclick="showTab({{ $index }})" id="tab-btn-{{ $index }}"
                class="tab-btn flex-shrink-0 md:w-full py-4 px-4 text-right font-semibold text-gray-600 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-gray-600 transition border-l md:border-l-0 md:border-b dark:border-gray-600 {{ $index === 0 ? 'active' : '' }}">
                {{ $section['title'] }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Content Area -->
    <div class="flex-1 min-h-[400px]">
        @foreach ($data['sections'] as $sIndex => $section)
        <div id="tab-content-{{ $sIndex }}" class="tab-content p-6 md:p-8 {{ $sIndex === 0 ? 'active' : '' }}">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">{{ $section['title'] }}</h2>
            <p class="text-orange-600 dark:text-orange-400 font-medium text-lg mb-6">{{ $section['question'] }}</p>

            <!-- Options -->
            <div class="space-y-4">
                @foreach ($section['options'] as $oIndex => $option)
                <div class="border border-orange-200 dark:border-gray-600 rounded-xl overflow-hidden">
                    <button onclick="toggleAccordion('accordion-{{ $sIndex }}-{{ $oIndex }}')"
                        class="w-full flex items-center justify-between p-4 bg-orange-50 dark:bg-gray-700 hover:bg-orange-100 dark:hover:bg-gray-600 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                            <span class="text-gray-800 dark:text-white font-semibold text-right">{{ $option['title'] }}</span>
                        </div>
                        <svg id="icon-accordion-{{ $sIndex }}-{{ $oIndex }}" class="w-5 h-5 text-orange-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="accordion-{{ $sIndex }}-{{ $oIndex }}" class="accordion-content">
                        <div class="p-4 bg-white dark:bg-gray-800 border-t border-orange-100 dark:border-gray-600">
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 font-semibold">الإجراءات المطلوبة:</p>
                            <div class="space-y-4">
                                @foreach ($option['steps'] as $step)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <span class="w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $loop->iteration }}</span>
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
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Smart Suggestion -->
            @if (isset($section['smartSuggestion']))
            <div class="bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 border border-emerald-500/30 rounded-xl overflow-hidden mt-6">
                <button onclick="toggleAccordion('suggestion-{{ $sIndex }}')" class="w-full flex items-center justify-between p-4 hover:bg-emerald-500/10 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ $section['smartSuggestion']['title'] }}</span>
                    </div>
                    <svg id="icon-suggestion-{{ $sIndex }}" class="w-5 h-5 text-emerald-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="suggestion-{{ $sIndex }}" class="accordion-content">
                    <div class="p-4 border-t border-emerald-500/20">
                        <p class="text-gray-700 dark:text-gray-300 mb-3">{{ $section['smartSuggestion']['description'] }}</p>
                        <ul class="space-y-2">
                            @foreach ($section['smartSuggestion']['details'] as $detail)
                            <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $detail }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
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