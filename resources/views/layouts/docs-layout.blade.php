<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'التوثيق') - قافلة</title>
    <link rel="icon" type="image/png" href="{{ asset('imgs/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
        if (localStorage.theme !== 'light') document.documentElement.classList.add('dark');
    </script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-collapsed .sidebar {
            width: 4rem;
        }

        .sidebar-collapsed .main-content {
            margin-right: 4rem;
        }

        .sidebar {
            width: 16rem;
            transition: width 0.3s ease;
        }

        .main-content {
            margin-right: 16rem;
            transition: margin-right 0.3s ease;
        }

        .nav-item.active {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        .nav-item:not(.active):hover {
            background: rgba(249, 115, 22, 0.1);
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .accordion-content.open {
            max-height: 2000px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                right: -16rem;
                z-index: 50;
            }

            .sidebar.mobile-open {
                right: 0;
            }

            .main-content {
                margin-right: 0 !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <!-- Mobile Menu Button -->
    <button onclick="toggleMobileSidebar()" class="md:hidden fixed top-4 right-4 z-50 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed top-0 right-0 h-full bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl border-l border-orange-100 dark:border-gray-700 shadow-2xl z-40">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="p-6 border-b border-orange-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('imgs/logo.png') }}" alt="Logo" class="h-10 w-10">
                    <span class="sidebar-text font-bold text-xl text-orange-600 dark:text-orange-400">قافلة</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <p class="sidebar-text text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4 px-3">التقارير</p>

                <a href="{{ route('docs.default-unit-report') }}"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 transition-all {{ request()->routeIs('docs.default-unit-report') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="sidebar-text font-medium">الوحدة الافتراضية</span>
                </a>

                <a href="{{ route('docs.branch-proposals') }}"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 transition-all {{ request()->routeIs('docs.branch-proposals') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="sidebar-text font-medium">مقترحات الفروع</span>
                </a>
            </nav>

            <!-- Footer Actions -->
            <div class="p-4 border-t border-orange-100 dark:border-gray-700 space-y-2">
                <button onclick="toggleSidebar()" class="hidden md:flex w-full items-center justify-center gap-2 px-4 py-2 rounded-xl text-gray-500 hover:bg-orange-50 dark:hover:bg-gray-700">
                    <svg id="collapse-icon" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span class="sidebar-text text-sm">طي القائمة</span>
                </button>
                <button onclick="toggleTheme()" class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-gray-500 hover:bg-orange-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <span class="sidebar-text text-sm">تبديل الوضع</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" onclick="toggleMobileSidebar()" class="hidden md:hidden fixed inset-0 bg-black/50 z-30"></div>

    <!-- Main Content -->
    <main class="main-content min-h-screen p-6 md:p-8">
        <div class="max-w-5xl mx-auto">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">@yield('page-title')</h1>
                @hasSection('page-subtitle')
                <p class="text-gray-500 dark:text-gray-400">@yield('page-subtitle')</p>
                @endif
            </header>

            @yield('content')

            <footer class="mt-12 text-center opacity-60">
                <img src="{{ asset('imgs/logo.png') }}" alt="Logo" class="h-8 mx-auto mb-2 grayscale">
                <p class="text-gray-400 text-sm">Qafilah Documentation</p>
            </footer>
        </div>
    </main>

    <script>
        function toggleTheme() {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        }

        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            document.getElementById('collapse-icon').classList.toggle('rotate-180');
        }

        function toggleMobileSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
            document.getElementById('mobile-overlay').classList.toggle('hidden');
        }

        function toggleAccordion(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById('icon-' + id);
            content.classList.toggle('open');
            if (icon) icon.classList.toggle('rotate-180');
        }
    </script>
    @stack('scripts')
</body>

</html>