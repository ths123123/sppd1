{{-- Professional Modern Logo Component --}}
<div class="shrink-0 flex items-center">
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
        <div class="relative">
            <div class="w-12 h-12 bg-white rounded-lg shadow-lg flex items-center justify-center group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-105">
                <img src="{{ asset('images/logo.png') }}"
                     alt="KPU Kabupaten Cirebon"
                     class="h-8 w-8 object-contain">
            </div>
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full animate-pulse"></div>
        </div>
        <!-- Teks hanya muncul di desktop dengan ukuran lebih kecil -->
        <div class="hidden xl:block">
            <div class="text-white font-semibold text-sm tracking-wide group-hover:text-blue-200 transition-colors duration-200">
                KPU Kabupaten Cirebon
            </div>
        </div>
    </a>
</div>
