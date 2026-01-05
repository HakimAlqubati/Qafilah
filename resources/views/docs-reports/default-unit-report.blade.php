@extends('layouts.docs-layout')

@section('title', $data['title'])
@section('page-title', $data['title'])
@section('page-subtitle', $data['date'] . ' • ' . $data['summary'])

@section('content')
<div class="space-y-6">
    @foreach ($data['sections'] as $index => $section)
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-100 dark:border-gray-700 overflow-hidden">
        <!-- Section Header -->
        <button onclick="toggleAccordion('section-{{ $index }}')"
            class="w-full flex items-center justify-between p-6 hover:bg-orange-50/50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center gap-4">
                @php
                $iconColors = [
                'lightbulb' => 'from-amber-400 to-amber-600',
                'code' => 'from-blue-400 to-blue-600',
                'flow' => 'from-purple-400 to-purple-600',
                'target' => 'from-emerald-400 to-emerald-600',
                ];
                $iconPaths = [
                'lightbulb' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                'code' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                'flow' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'target' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                ];
                @endphp
                <div class="w-12 h-12 bg-gradient-to-br {{ $iconColors[$section['icon']] ?? 'from-orange-400 to-orange-600' }} rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$section['icon']] ?? '' }}"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $section['title'] }}</h2>
            </div>
            <svg id="icon-section-{{ $index }}" class="w-5 h-5 text-orange-500 transform transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Section Content -->
        <div id="section-{{ $index }}" class="accordion-content open">
            <div class="px-6 pb-6 space-y-4">
                {{-- Content Text --}}
                @if (!empty($section['content']))
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed bg-orange-50/50 dark:bg-gray-700/50 rounded-xl p-4">
                    {{ $section['content'] }}
                </p>
                @endif

                {{-- Files List --}}
                @if (!empty($section['files']))
                @foreach ($section['files'] as $category)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                    <h4 class="font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                        {{ $category['category'] }}
                    </h4>
                    <div class="space-y-3">
                        @foreach ($category['items'] as $item)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-600">
                            <div class="text-xs text-orange-600 dark:text-orange-400 font-mono bg-orange-50 dark:bg-gray-700 rounded px-2 py-1 inline-block mb-2">
                                {{ $item['file'] }}
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">{{ $item['change'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif

                {{-- Steps List --}}
                @if (!empty($section['steps']))
                <div class="space-y-3">
                    @foreach ($section['steps'] as $stepIndex => $step)
                    <div class="flex items-start gap-4 bg-purple-50/50 dark:bg-purple-900/20 rounded-xl p-4">
                        <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ $stepIndex + 1 }}
                        </span>
                        <p class="text-gray-700 dark:text-gray-300 pt-1">{{ $step }}</p>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Points List --}}
                @if (!empty($section['points']))
                <ul class="space-y-3">
                    @foreach ($section['points'] as $point)
                    <li class="flex items-start gap-3 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-xl p-4">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">{{ $point }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection