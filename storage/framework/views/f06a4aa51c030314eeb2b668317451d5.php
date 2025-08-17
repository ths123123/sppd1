<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="user-role" content="<?php echo e(Auth::user()->role ?? 'staff'); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>
    <!-- Favicon KPU -->
    <link rel="icon" type="image/png" href="/images/logo.png" sizes="32x32">
    <link rel="shortcut icon" href="/images/logo.png" type="image/png">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Vite Assets -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/navbar-profile-update.js']); ?>

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

        /* Notifikasi Styling */
        .notification-item {
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            transform: translateX(5px);
        }
        .notification-badge {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .notification-icon {
            transition: all 0.3s ease;
        }
        .notification-icon:hover {
            transform: rotate(15deg);
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="font-sans antialiased min-h-screen overflow-x-hidden" data-user-role="<?php echo e(Auth::user()->role ?? 'staff'); ?>">
    
    <div class="navbar-fixed">
        <?php echo $__env->make('components.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>

    <!-- Page Heading -->
    <?php if(isset($header)): ?>
        <header class="bg-white dark:bg-white-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <?php echo e($header); ?>

            </div>
        </header>
    <?php endif; ?>

    <!-- Page Content -->
    <main class="main-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Navbar Profile Updater -->
    <script src="<?php echo e(asset('js/navbar-profile-updater.js')); ?>"></script>

    <!-- Navbar Access Control -->
    <script src="<?php echo e(asset('js/navbar-access-control.js')); ?>"></script>

    <!-- Professional Navbar Manager -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>

    <script>
        // Initialize Professional Navbar Manager
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ProfessionalNavbarManager !== 'undefined') {
                new ProfessionalNavbarManager();
            }
        });
    </script>

    <style>
        /* Animasi untuk notifikasi real-time */
        @keyframes pulseOnce {
            0% { opacity: 0.5; transform: scale(0.98); }
            50% { opacity: 1; transform: scale(1.02); }
            100% { opacity: 1; transform: scale(1); }
        }

        .animate-pulse-once {
            animation: pulseOnce 1s ease-in-out;
        }

        /* Animasi badge notifikasi */
        @keyframes badgePulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .notification-badge {
            animation: badgePulse 2s infinite;
        }

        /* Efek hover untuk item notifikasi */
        .notification-item:hover {
            transform: translateX(3px);
        }

        /* Professional notification banner styling */
        #access-warning-banner {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link href="<?php echo e(asset('css/components/navbar-access-control.css')); ?>" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\pkl\SPPD-KP1\SPPD-KPUKP1\resources\views/layouts/app.blade.php ENDPATH**/ ?>