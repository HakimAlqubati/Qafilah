<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('lang.welcome') }} - Qafilah</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {
                    {
                    app()->getLocale()==='ar' ? "'Cairo', sans-serif": "'Inter', sans-serif"
                }
            }

            ;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff8c00, #ff6b00);
            animation: float 15s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 400px;
            height: 400px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 300px;
            height: 300px;
            bottom: -50px;
            right: -50px;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 40px 20px;
            max-width: 900px;
            width: 100%;
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo {
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(255, 140, 0, 0.3);
            animation: pulse 3s infinite ease-in-out;
            padding: 15px;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 20px 40px rgba(255, 140, 0, 0.3);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 25px 50px rgba(255, 140, 0, 0.4);
            }
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .title {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 50px;
        }

        .cards-container {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
            perspective: 1000px;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 40px 35px;
            width: 320px;
            text-decoration: none;
            color: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 140, 0, 0.3) 0%, rgba(255, 107, 0, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-icon {
            position: relative;
            z-index: 1;
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            transition: all 0.4s ease;
        }

        .card-admin .card-icon {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
        }

        .card-merchant .card-icon {
            background: linear-gradient(135deg, #ffb347 0%, #ff8c00 100%);
        }

        .card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .card-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .card-title {
            position: relative;
            z-index: 1;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .card-description {
            position: relative;
            z-index: 1;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card-arrow {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .card:hover .card-arrow {
            color: white;
            gap: 12px;
        }

        .card-arrow svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
            transition: transform 0.3s ease;
        }

        [dir="rtl"] .card-arrow svg {
            transform: rotate(180deg);
        }

        .card:hover .card-arrow svg {
            transform: translateX(5px);
        }

        [dir="rtl"] .card:hover .card-arrow svg {
            transform: translateX(-5px) rotate(180deg);
        }

        /* Language Switcher */
        .lang-switcher {
            position: absolute;
            top: 30px;
            right: 30px;
        }

        [dir="rtl"] .lang-switcher {
            right: auto;
            left: 30px;
        }

        .lang-btn {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }

        @media (max-width: 768px) {
            .title {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .cards-container {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 100%;
                max-width: 320px;
            }

            .lang-switcher {
                top: 20px;
                right: 20px;
            }

            [dir="rtl"] .lang-switcher {
                left: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Animated background -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Language Switcher -->
    <form action="{{ route('locale.switch') }}" method="POST" class="lang-switcher">
        @csrf
        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
        <button type="submit" class="lang-btn">
            {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
        </button>
    </form>

    <div class="container">
        <!-- Logo -->
        <div class="logo-container">
            <div class="logo">
                <img src="/imgs/logo.png" alt="Qafilah Logo">
            </div>
        </div>

        <!-- Title -->
        <h1 class="title">{{ __('lang.welcome_to_qafilah') }}</h1>
        <p class="subtitle">{{ __('lang.choose_panel') }}</p>

        <!-- Cards -->
        <div class="cards-container">
            <!-- Admin Panel Card -->
            <a href="/admin" class="card card-admin">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z" />
                    </svg>
                </div>
                <h2 class="card-title">{{ __('lang.admin_panel') }}</h2>
                <p class="card-description">{{ __('lang.admin_panel_desc') }}</p>
                <span class="card-arrow">
                    {{ __('lang.go_to_panel') }}
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z" />
                    </svg>
                </span>
            </a>

            <!-- Merchant Panel Card -->
            <a href="/merchant/login" class="card card-merchant">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 4H4v2h16V4zm1 10v-2l-1-5H4l-1 5v2h1v6h10v-6h4v6h2v-6h1zm-9 4H6v-4h6v4z" />
                    </svg>
                </div>
                <h2 class="card-title">{{ __('lang.merchant_panel') }}</h2>
                <p class="card-description">{{ __('lang.merchant_panel_desc') }}</p>
                <span class="card-arrow">
                    {{ __('lang.go_to_panel') }}
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z" />
                    </svg>
                </span>
            </a>
        </div>
    </div>
</body>

</html>