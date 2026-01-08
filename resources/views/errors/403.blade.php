<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('403 - Access Denied') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .pulse-icon {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        {{-- Glass Card --}}
        <div class="glass-card rounded-2xl p-8 md:p-12 max-w-lg mx-auto shadow-2xl">
            {{-- Lock Icon --}}
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-500/20 pulse-icon">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-7xl md:text-8xl font-bold text-white mb-2">403</h1>

            {{-- Title --}}
            <h2 class="text-xl md:text-2xl font-semibold text-white/90 mb-4">
                {{ __('Access Denied') }}
            </h2>

            {{-- Message --}}
            <p class="text-white/70 mb-8 text-sm md:text-base">
                {{ $exception->getMessage() ?: __('You do not have permission to access this page.') }}
            </p>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">


                {{-- Home Page --}}
                <a href="/"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-medium rounded-lg transition-all duration-200 hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    {{ __('Home Page') }}
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="mt-8 text-white/40 text-sm">
            &copy; {{ date('Y') }} {{ config('app.name', 'Qafilah') }}
        </p>
    </div>
</body>

</html>