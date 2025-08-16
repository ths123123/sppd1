<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SPPD KPU Kabupaten Cirebon</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>

        * { font-family: 'Segoe UI', 'Inter', Arial, sans-serif; }

        .gradient-bg {
            background-color: #8B0000;
        }

        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }


        .btn-primary {
            background-color: #8B0000;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 0, 0, 0.4);
        }

        .step-card {
            background: rgba(139, 0, 0, 0.05);
            border: 1px solid rgba(139, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .step-card:hover {
            background: rgba(139, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .typing-cursor::after {
            content: '|';
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .slide-in {
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="bg-white min-h-screen">
    <!-- Desktop Layout -->
    <div class="hidden lg:flex min-h-screen">
        <!-- Left Panel - Gradient (Info) -->
        <div class="lg:w-1/2 gradient-bg flex items-center justify-center p-4 max-h-screen overflow-auto">
            <div class="max-w-sm w-full text-white slide-in">
                <div class="text-center mb-4">
                    <div class="w-16 h-16 mx-auto mb-4 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="KPU Logo" class="w-12 h-12 object-contain">
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Sistem SPPD</h1>
                    <p class="text-base text-white/80">KPU Kabupaten Cirebon</p>
                </div>
                <div class="space-y-4">
                    <div style="height:76px; display:flex; align-items:flex-start; justify-content:center; margin-bottom:1.5rem;">
                        <div class="typing-cursor text-center text-base" id="typing-text" style="min-width:320px; max-width:100%; width:auto; white-space:normal; text-align:center; vertical-align:top; position:relative; top:-18px; font-family:'Segoe UI', Arial, sans-serif;"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="step-card rounded-xl p-3 bg-white/10 backdrop-blur-sm text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center mb-1">
                                    <span class="font-bold text-xs">1</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Pengajuan SPPD</div>
                                    <div class="text-xs text-white/70">Buat pengajuan perjalanan dinas</div>
                                </div>
                            </div>
                        </div>
                        <div class="step-card rounded-xl p-3 bg-white/10 backdrop-blur-sm text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center mb-1">
                                    <span class="font-bold text-xs">2</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Proses Review</div>
                                    <div class="text-xs text-white/70">Alur persetujuan berjenjang</div>
                                </div>
                            </div>
                        </div>
                        <div class="step-card rounded-xl p-3 bg-white/10 backdrop-blur-sm text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center mb-1">
                                    <span class="font-bold text-xs">3</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Dokumentasi</div>
                                    <div class="text-xs text-white/70">Upload bukti dan laporan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="lg:w-1/2 flex items-center justify-center p-4 max-h-screen overflow-auto">
            <div class="w-full max-w-sm slide-in">
                <div class="text-center mb-4">
                    <div class="w-12 h-12 mx-auto mb-3 gradient-bg rounded-2xl flex items-center justify-center">
                        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="KPU Logo" class="w-8 h-8 object-contain">
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Masuk ke Sistem</h2>
                    <p class="text-gray-600 text-sm">Silakan masuk dengan akun Anda</p>
                </div>
                <?php if($errors->any()): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan:</h3>
                            <div class="mt-1 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card-shadow rounded-2xl p-4 bg-white border border-gray-100">
                    <form class="space-y-4" method="POST" action="<?php echo e(route('login')); ?>">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Alamat Email</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg input-focus outline-none transition-all text-sm" placeholder="Masukkan email Anda" required autofocus value="<?php echo e(old('email')); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Kata Sandi</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg input-focus outline-none transition-all text-sm" placeholder="Masukkan kata sandi" required>
                                <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                                <span class="ml-2 text-xs text-gray-600">Ingat saya</span>
                            </label>
                            <?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="text-xs text-purple-600 hover:text-purple-500">Lupa kata sandi?</a>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="w-full py-2 px-3 btn-primary text-white font-medium rounded-lg text-sm">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Masuk ke Sistem
                        </button>
                    </form>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-center text-xs text-gray-600 mb-2">Butuh bantuan? Hubungi administrator</p>
                        <div class="flex justify-center space-x-4 text-xs text-gray-500">
                            <div class="flex items-center">
                                <i class="fas fa-phone mr-2"></i>
                                (0231) 123456
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope mr-2"></i>
                                admin@kpucirebon.go.id
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Layout -->
    <div class="lg:hidden min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="gradient-bg p-6 text-center text-white">
            <div class="w-16 h-16 mx-auto mb-4 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="KPU Logo" class="w-12 h-12 object-contain">
            </div>
            <h1 class="text-2xl font-bold mb-2">Sistem SPPD</h1>
            <p class="text-white/80">KPU Kabupaten Cirebon</p>
        </div>

        <!-- Login Form -->
        <div class="p-6 -mt-8 relative z-10">
            <div class="card-shadow rounded-2xl p-6 bg-white">
                <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Masuk ke Sistem</h2>
                
                <?php if($errors->any()): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan:</h3>
                            <div class="mt-1 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form class="space-y-4" method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Email</label>
                        <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus outline-none" placeholder="Masukkan email Anda" required autofocus value="<?php echo e(old('email')); ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi</label>
                        <div class="relative">
                            <input type="password" id="passwordMobile" name="password" class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg input-focus outline-none" placeholder="Masukkan kata sandi" required>
                            <button type="button" onclick="togglePasswordMobile()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-eye" id="toggleIconMobile"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-purple-600 border-gray-300 rounded" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                            <span class="ml-2 text-gray-600">Ingat saya</span>
                        </label>
                        <?php if(Route::has('password.request')): ?>
                        <a href="<?php echo e(route('password.request')); ?>" class="text-purple-600">Lupa kata sandi?</a>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 btn-primary text-white font-medium rounded-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Masuk ke Sistem
                    </button>
                </form>
            </div>
        </div>

        <!-- Steps -->
        <div class="p-6 space-y-3">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Cara Kerja Sistem</h3>

            <div class="step-card rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center mr-4 text-white">
                        <span class="font-bold text-sm">1</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Pengajuan SPPD</div>
                        <div class="text-sm text-gray-600">Buat pengajuan perjalanan dinas</div>
                    </div>
                </div>
            </div>

            <div class="step-card rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center mr-4 text-white">
                        <span class="font-bold text-sm">2</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Proses Review</div>
                        <div class="text-sm text-gray-600">Alur persetujuan berjenjang</div>
                    </div>
                </div>
            </div>

            <div class="step-card rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center mr-4 text-white">
                        <span class="font-bold text-sm">3</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Dokumentasi</div>
                        <div class="text-sm text-gray-600">Upload bukti dan laporan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="p-6 border-t border-gray-200 bg-white">
            <p class="text-center text-sm text-gray-600 mb-3">Butuh bantuan? Hubungi administrator</p>
            <div class="flex justify-center space-x-4 text-sm text-gray-500">
                <div class="flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    (0231) 123456
                </div>
                <div class="flex items-center">
                    <i class="fas fa-envelope mr-2"></i>
                    admin@kpucirebon.go.id
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function togglePasswordMobile() {
            const passwordInput = document.getElementById('passwordMobile');
            const toggleIcon = document.getElementById('toggleIconMobile');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Typing effect
        const text = "Platform digital terintegrasi untuk pengelolaan perjalanan dinas yang efisien, transparan, dan aman di lingkungan KPU Kabupaten Cirebon.";
        let index = 0;

        function typeWriter() {
            const element = document.getElementById('typing-text');
            if (element && index < text.length) {
                element.innerHTML = text.substring(0, index + 1);
                index++;
                setTimeout(typeWriter, 50);
            } else {
                setTimeout(() => {
                    index = 0;
                    typeWriter();
                }, 3000);
            }
        }

        // Initialize typing effect
        window.addEventListener('load', () => {
            setTimeout(typeWriter, 1000);
        });
    </script>
</body>
</html>
<?php /**PATH D:\pkl\SPPD-KP1\SPPD-KPUKP1\resources\views/auth/login.blade.php ENDPATH**/ ?>