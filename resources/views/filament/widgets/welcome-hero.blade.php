<x-filament::section class="!p-0 overflow-hidden relative rounded-2xl shadow-lg">
    <div class="relative isolate overflow-hidden bg-gradient-to-r from-teal-800 via-cyan-700 to-emerald-700">
        {{-- ✨ خلفية مضيئة متحركة --}}
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_rgba(255,255,255,0.08),_transparent_70%)] animate-pulse">
        </div>

        {{-- ✨ عنصر تأثير ضوء متحرك على الأطراف --}}
        <div
            class="absolute -inset-x-1 -top-1 h-[2px] bg-gradient-to-r from-transparent via-white/50 to-transparent animate-[shine_4s_linear_infinite]">
        </div>

        <div class="relative px-8 py-16 text-center text-white">
            <h1 class="text-4xl font-bold tracking-tight drop-shadow-md mb-4 animate-fadeIn">
                Welcome to <span class="text-cyan-200">Qafilah</span> Dashboard
            </h1>

            <p class="text-lg text-white/80 max-w-2xl mx-auto leading-relaxed animate-fadeIn delay-200">
                Manage your business with clarity, confidence, and creativity.
            </p>


        </div>
    </div>

    {{-- ⚙️ Keyframes داخل الصفحة --}}
    <style>
        @keyframes shine {
            0% {
                transform: translateX(-100%);
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 1s ease-out forwards;
        }

        .animate-fadeIn.delay-200 {
            animation-delay: 0.2s;
        }

        .animate-fadeIn.delay-300 {
            animation-delay: 0.3s;
        }
    </style>
</x-filament::section>
