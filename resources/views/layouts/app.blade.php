<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store')</title>

    {{-- TailwindCSS CDN (for quick styling) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Optional: custom fonts or icons --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    {{-- AlpineJS (for tabs) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">

    {{-- Simple Navbar --}}
    <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-3 flex justify-between items-center">
            <a href="/" class="text-lg font-semibold text-indigo-600">MyStore</a>
            <nav class="space-x-4 text-sm">
                <a href="/" class="text-gray-600 hover:text-indigo-600">Home</a>
                <a href="/products" class="text-gray-600 hover:text-indigo-600">Products</a>
                <a href="#" class="text-gray-600 hover:text-indigo-600">About</a>
            </nav>
        </div>
    </header>

    {{-- Main content from pages --}}
    <main class="py-10">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-10 border-t border-gray-100 py-6 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} MyStore — All rights reserved.
    </footer>

</body>
</html>
