<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? 'User' }}">

    <title>{{ config('app.name', 'SPPD KPU Kabupaten Cirebon') }}</title>
    <!-- Favicon KPU -->
    <link rel="icon" type="image/png" href="/images/logo.png" sizes="32x32">
    <link rel="shortcut icon" href="/images/logo.png" type="image/png">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/navbar-profile-update.js'])

    <!-- Alpine.js for interactivity -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body {
            background: #f8fafc;
            overflow-x: hidden;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .modern-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        .modern-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .modern-button {
            background: linear-gradient(135deg, #ffffff, #e5e5e5);
            color: #000000;
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .modern-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .modern-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        .modern-button:hover::before {
            left: 100%;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            animation: pulse-dot 2s infinite;
        }
        .chart-container {
            position: relative;
            height: 320px;
        }
        .logo-professional {
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .logo-professional:hover {
            background: rgba(0, 0, 0, 0.1);
            transform: scale(1.05);
        }
        .modern-title {
            background: linear-gradient(135deg, #000000, #1a1a1a, #2a2a2a);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .modern-text {
            color: rgba(0, 0, 0, 0.8);
        }
        .modern-muted {
            color: rgba(0, 0, 0, 0.6);
        }
        .notification-button:hover {
            background: #3B82F6;
            color: #ffffff;
            transform: scale(1.1);
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased min-h-screen overflow-x-hidden">
    {{-- Navbar utama --}}
    <div class="navbar-fixed">
        @include('components.navbar')
    </div>
    <x-toast />

    <!-- Page Heading -->
    @isset($header)
        <header class="bg-white dark:bg-white-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main class="main-content">
        @yield('content')
    </main>    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Navbar Profile Updater -->
    <script src="{{ asset('js/navbar-profile-updater.js') }}"></script>

    <!-- Professional Navbar Manager -->
    @vite(['resources/js/app.js'])
    
    <script>
        // Initialize Professional Navbar Manager
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ProfessionalNavbarManager !== 'undefined') {
                new ProfessionalNavbarManager();
            }
        });
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @stack('scripts')
</body>
</html>
