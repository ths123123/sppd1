@php
    use Illuminate\Support\Facades\Storage;
    $user = Auth::user();
    $role = $user?->role ?? null;
@endphp

<nav x-data="{ desktopDropdownOpen: false }" class="w-full h-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex items-center justify-between h-full">
            <!-- Logo Section -->
            <div class="flex items-center space-x-2">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-12 h-12 bg-white rounded-lg shadow-sm flex items-center justify-center mr-3 hover:shadow-md transition-all duration-300 transform hover:scale-105 border-2 border-red-300">
                           <img src="{{ asset('images/logo.png') }}" alt="KPU Logo" class="w-8 h-8 object-contain">
                        </div>
                        <!-- Logo text - tampil di semua ukuran layar -->
                        <div class="block">
                            <h1 class="text-sm font-bold text-white tracking-wide">KPU Kabupaten Cirebon</h1>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div class="sm:hidden relative">
                @include('components.navigation.mobile-menu')
            </div>

            <!-- Desktop: Professional Navigation & Profile -->
            <div class="hidden sm:flex sm:items-center sm:space-x-6">
                <!-- Primary Navigation Links (Grouped Dropdown) -->
                <div class="flex items-center space-x-1 lg:space-x-3">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                        <span>{{ __('Dashboard') }}</span>
                    </a>

                    <!-- Approval - Tampilkan untuk semua user -->
                    <a href="{{ route('approval.pimpinan.index') }}"
                       data-requires-role="approver"
                       class="text-white px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200 {{ in_array($role, ['sekretaris', 'ppk']) ? 'hover:text-gray-200' : 'opacity-50 cursor-not-allowed' }}"
                       @if(!in_array($role, ['sekretaris', 'ppk']))
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        <span>Approval</span>
                    </a>

                    <!-- SPPD Group -->
                    <div class="relative" x-data="{ sppdDropdown: false }" @mouseenter="sppdDropdown = true" @mouseleave="sppdDropdown = false">
                        <button class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                            <span>SPPD</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': sppdDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- SPPD Dropdown Menu -->
                        <div x-show="sppdDropdown" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <!-- Buat SPPD - Tampilkan untuk semua user -->
                                <a href="{{ route('travel-requests.create') }}"
                                   data-requires-role="kasubbag"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   @if($role !== 'kasubbag')
                                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                                   @endif>
                                    Buat SPPD Baru
                                </a>

                                <!-- SPPD Saya - Tampilkan untuk semua user -->
                                <a href="{{ route('my-travel-requests.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    SPPD Saya
                                </a>

                                <!-- Daftar SPPD - Tampilkan untuk semua user -->
                                <a href="{{ route('travel-requests.index') }}"
                                   data-requires-role="view_all_sppd"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                                   @endif>
                                    Daftar Semua SPPD
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics & Laporan Group -->
                    <div class="relative" x-data="{ analyticsDropdown: false }" @mouseenter="analyticsDropdown = true" @mouseleave="analyticsDropdown = false">
                        <button class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                            <span>Analytics & Laporan</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': analyticsDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Analytics Dropdown Menu -->
                        <div x-show="analyticsDropdown" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="{{ route('analytics.index') }}"
                                   data-requires-role="analytics"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk']))
                                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                                   @endif>
                                    Analytics
                                </a>
                                <a href="{{ route('laporan.daftar') }}"
                                   data-requires-role="analytics"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk']))
                                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                                   @endif>
                                    Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen Group -->
                    <div class="relative" x-data="{ dokumenDropdown: false }" @mouseenter="dokumenDropdown = true" @mouseleave="dokumenDropdown = false">
                        <button class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                            <span>Dokumen</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': dokumenDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dokumen Dropdown Menu -->
                        <div x-show="dokumenDropdown" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="{{ route('documents.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Dokumen Saya
                                </a>
                                <a href="{{ route('templates.index') }}"
                                   data-requires-role="document_management"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                                   @endif>
                                    Manajemen Template
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Kelola User - Tampilkan untuk semua user -->
                    <a href="{{ route('users.index') }}"
                       data-requires-role="user_management"
                       class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200"
                       @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        <span>Kelola User</span>
                    </a>
                </div>

                <!-- Notifications & Professional User Profile -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div x-data="{ notificationDropdown: false }" class="relative">
                        <button @click="notificationDropdown = !notificationDropdown" id="notification-bell" class="relative p-2 text-white hover:text-red-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-300 transition-all duration-200 hover:scale-110 notification-icon">
                            <i class="fas fa-bell w-6 h-6 text-xl"></i>
                            <!-- Notification Badge -->
                            <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold shadow-md notification-badge" style="display:none;">0</span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="notificationDropdown"
                             @click.away="notificationDropdown = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl z-50 border border-gray-200 overflow-hidden">
                            <div class="bg-white px-4 py-3 text-gray-800 border-b">
                                <h3 class="text-base font-bold">Notifikasi</h3>
                                <p class="text-xs text-gray-600 mt-1">Pemberitahuan sistem SPPD KPU Kabupaten Cirebon</p>
                            </div>
                            <div id="notification-dropdown-content" class="max-h-[400px] overflow-y-auto">
                                <div class="px-4 py-6 text-center text-gray-400 text-sm">
    <div class="flex justify-center items-center space-x-2">
        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Memuat notifikasi...</span>
    </div>
</div>
                            </div>
                            <!-- Footer dengan tombol Lihat Semua -->
                            <div id="notification-footer" class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                                <div class="flex items-center">
                                    <span id="notification-unread-count" class="bg-gray-400 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold mr-2">0</span>
                                    <span class="text-xs text-gray-500">Belum dibaca</span>
                                </div>
                                <a href="/notifications" class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center transition-all duration-200 hover:translate-x-1">
                                    <span>Lihat semua</span>
                                    <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Professional User Profile Dropdown -->
                    <div x-data="{ profileDropdown: false }" class="relative">
                        <button @click="profileDropdown = !profileDropdown" class="group flex items-center space-x-2 px-2 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-300 text-white hover:text-red-200 transition-colors duration-200">
                            <!-- Profile Photo dengan Auto-Update -->
                            <div class="relative w-10 h-10 rounded-full overflow-hidden flex items-center justify-center">
                                @if($user && $user->avatar_url && !str_contains($user->avatar_url, 'ui-avatars.com'))
                                    <img id="navbar-profile-photo"
                                         src="{{ $user->avatar_url }}"
                                         alt="{{ $user->name ?? 'User' }}"
                                         class="w-full h-full object-cover rounded-full shadow-md"
                                         onerror="handleAvatarError(this)">
                                @else
                                    <div id="navbar-profile-photo" class="w-full h-full rounded-full bg-gray-300 flex items-center justify-center text-white font-bold text-lg border-2 border-gray-400">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <!-- Online Status Indicator -->
                                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full animate-pulse transform translate-x-1/3 translate-y-1/3"></div>
                            </div>
                        </button>

                        <!-- Enhanced Profile Dropdown Menu -->
                        <div x-show="profileDropdown"
                             @click.away="profileDropdown = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50"
                             style="min-width: 18rem;">
                            <!-- Simple Profile Header -->
                            <div class="flex flex-col items-center justify-center px-6 py-6 sm:p-5 border-b border-gray-100 bg-white rounded-t-xl">
                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-300 shadow-md mb-2 flex items-center justify-center bg-gray-200">
                                    @if($user && $user->avatar_url && !str_contains($user->avatar_url, 'ui-avatars.com'))
                                        <img id="dropdown-profile-photo"
                                             src="{{ $user->avatar_url }}"
                                             alt="{{ $user->name ?? 'User' }}"
                                             class="w-full h-full object-cover"
                                             onerror="handleAvatarError(this)">
                                    @else
                                        <span class="text-2xl font-bold text-gray-600">{{ substr($user->name ?? 'U', 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="text-black font-semibold text-lg">{{ $user->name ?? 'User' }}</div>
                                <div class="text-gray-500 text-xs mb-1">{{ $user->email ?? 'email@domain.com' }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                    <span class="text-green-600 text-xs">Online</span>
                                </div>
                            </div>
                            <!-- Simple Menu Items -->
                            <div class="flex flex-col divide-y divide-gray-100 text-base sm:text-sm">
                                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-6 py-4 sm:px-5 sm:py-3 hover:bg-gray-100 transition-colors duration-200 text-black">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 py-4 sm:px-5 sm:py-3 hover:bg-gray-100 transition-colors duration-200 text-black">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" /></svg>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-6 py-4 sm:px-5 sm:py-3 hover:bg-gray-100 transition-colors duration-200 text-black">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>Setting</span>
                                </a>
                            </div>
                            <!-- Simple Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 px-6 py-4 sm:px-5 sm:py-3">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full text-left hover:bg-red-50 transition-colors duration-200 text-red-600 font-semibold rounded-b-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
// Notifikasi Navbar Polling
function renderNotificationItem(n) {
    // Validasi data notifikasi
    if (!n || typeof n !== 'object') {
        console.error('Invalid notification data:', n);
        return '';
    }

    var icon = '';
    var bgColor = 'bg-gray-50';
    var borderColor = 'border-l-gray-300';
    var statusBadge = '';

    if (n.type === 'info') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-info text-blue-600 text-lg"></i></div>';
        bgColor = 'bg-blue-50';
        borderColor = 'border-l-blue-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">Informasi</span>';
    } else if (n.type === 'success') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-check text-green-600 text-lg"></i></div>';
        bgColor = 'bg-green-50';
        borderColor = 'border-l-green-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">Sukses</span>';
    } else if (n.type === 'warning') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i></div>';
        bgColor = 'bg-yellow-50';
        borderColor = 'border-l-yellow-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">Perhatian</span>';
    } else if (n.type === 'error') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-times text-red-600 text-lg"></i></div>';
        bgColor = 'bg-red-50';
        borderColor = 'border-l-red-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-medium">Error</span>';
    } else if (n.type === 'reminder' || n.type === 'approval_reminder') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-clipboard-check text-purple-600 text-lg"></i></div>';
        bgColor = 'bg-purple-50';
        borderColor = 'border-l-purple-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 font-medium">Pengingat</span>';
    } else if (n.type === 'approval_request') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-clipboard-list text-indigo-600 text-lg"></i></div>';
        bgColor = 'bg-indigo-50';
        borderColor = 'border-l-indigo-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800 font-medium">Permintaan Persetujuan</span>';
    } else if (n.type === 'status_update') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-sync-alt text-blue-600 text-lg"></i></div>';
        bgColor = 'bg-blue-50';
        borderColor = 'border-l-blue-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">Update Status</span>';
    } else if (n.type === 'sppd_submitted' || n.type === 'sppd_submitted_confirmation') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-paper-plane text-green-600 text-lg"></i></div>';
        bgColor = 'bg-green-50';
        borderColor = 'border-l-green-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">SPPD Diajukan</span>';
    } else if (n.type === 'sppd_completed') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-paper-plane text-green-600 text-lg"></i></div>';
        bgColor = 'bg-green-50';
        borderColor = 'border-l-green-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">SPPD Selesai</span>';
    } else if (n.type === 'sppd_rejected') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-times-circle text-red-600 text-lg"></i></div>';
        bgColor = 'bg-red-50';
        borderColor = 'border-l-red-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-medium">SPPD Ditolak</span>';
    } else if (n.type === 'sppd_revision') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-edit text-yellow-600 text-lg"></i></div>';
        bgColor = 'bg-yellow-50';
        borderColor = 'border-l-yellow-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">Revisi SPPD</span>';
    } else if (n.type === 'sppd_approved') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-paper-plane text-green-600 text-lg"></i></div>';
        bgColor = 'bg-green-50';
        borderColor = 'border-l-green-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">SPPD Disetujui</span>';
    } else if (n.type === 'sppd_approved_by_sekretaris') {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-paper-plane text-green-600 text-lg"></i></div>';
        bgColor = 'bg-green-50';
        borderColor = 'border-l-green-400';
        statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">SPPD Disetujui</span>';
    } else {
        icon = '<div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center shadow-sm"><i class="fas fa-bell text-gray-600 text-lg"></i></div>';
    }

    // Pastikan created_at ada dan valid
    var time = '';
    try {
        if (n.created_at) {
            time = new Date(n.created_at).toLocaleString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        } else {
            console.warn('Notification missing created_at:', n);
            time = 'Waktu tidak tersedia';
        }
    } catch (e) {
        console.error('Error formatting date:', e);
        time = 'Waktu tidak valid';
    }

    // Format pesan yang lebih profesional
    var formattedMessage = n.message || 'Tidak ada pesan';
    if (n.travel_request_id) {
        // Tampilkan kode SPPD dengan format yang lebih profesional
        formattedMessage = formattedMessage.replace(/SPPD-\d+/g, '<span class="font-medium text-indigo-700">$&</span>');

        // Tambahkan informasi tambahan dari data jika tersedia
        if (n.data && typeof n.data === 'object') {
            if (n.data.travel_request_code) {
                formattedMessage = formattedMessage.replace(/SPPD dengan nomor/g, 'SPPD dengan kode <span class="font-medium text-indigo-700">' + n.data.travel_request_code + '</span>');
            }

            // Tambahkan informasi status jika tersedia
            if (n.data.status) {
                formattedMessage += '<div class="mt-1 text-sm">Status: <span class="font-medium">' + n.data.status + '</span></div>';
            }
        }
    }

    // Tambahkan action URL jika ada
    var actionButton = '';
    if (n.action_url && n.action_text) {
        actionButton = '<a href="' + n.action_url + '" class="mt-2 inline-block px-3 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 transition-colors duration-200">' + n.action_text + '</a>';
    }

    try {
        // Tentukan URL detail berdasarkan jenis notifikasi
        var detailUrl = '/notifications';
        if (n.travel_request_id) {
            detailUrl = '/travel-requests/' + n.travel_request_id;
        } else if (n.action_url) {
            detailUrl = n.action_url;
        }

        return '<div class="' + bgColor + ' hover:bg-opacity-80 border-b border-l-4 ' + borderColor + ' transition-all duration-200 cursor-pointer notification-item">' +
            '<div class="px-4 py-3">' +
                '<div class="flex items-start space-x-3">' +
                    icon +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex justify-between items-start">' +
                            '<p class="text-sm font-bold text-gray-900 mb-1">' + (n.title || 'Notifikasi') + '</p>' +
                            statusBadge +
                        '</div>' +
                        '<p class="text-sm text-gray-700 leading-relaxed">' + formattedMessage + '</p>' +
                        (actionButton || '<a href="' + detailUrl + '" class="mt-2 inline-block px-3 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 transition-colors duration-200">Lihat Detail</a>') +
                        '<div class="flex items-center mt-2">' +
                            '<i class="far fa-clock text-gray-400 mr-1 text-xs"></i>' +
                            '<p class="text-xs text-gray-500">' + time + '</p>' +
                            (n.is_read === false ? '<span class="ml-2 w-2 h-2 bg-red-600 rounded-full"></span>' : '') +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    } catch (e) {
        console.error('Error rendering notification item:', e, n);
        return '<div class="bg-red-50 border-b border-l-4 border-l-red-400 px-4 py-3"><p class="text-sm text-red-600">Error menampilkan notifikasi</p></div>';
    }
}

// Simpan data notifikasi terakhir untuk perbandingan
var lastNotificationData = null;

function fetchNotifications() {
    const badge = document.getElementById('notification-badge');
    const mobileBadge = document.getElementById('mobile-notification-badge');
    const dropdown = document.getElementById('notification-dropdown-content');
    const unreadCountElement = document.getElementById('notification-unread-count');
    const footer = document.getElementById('notification-footer');

    if (!badge || !dropdown) {
        console.error('Notification elements not found');
        return Promise.resolve();
    }

    // Hanya tampilkan loading jika dropdown terlihat dan belum ada data
    const isDropdownVisible = dropdown.offsetParent !== null;
    if (isDropdownVisible && !lastNotificationData) {
        dropdown.innerHTML = `<div class='px-4 py-6 text-center text-gray-400 text-sm'>
    <div class='flex justify-center items-center space-x-2'>
        <svg class='animate-spin h-5 w-5 text-gray-500' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'>
            <circle class='opacity-25' cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4'></circle>
            <path class='opacity-75' fill='currentColor' d='M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z'></path>
        </svg>
        <span>Memuat notifikasi...</span>
    </div>
</div>`;

        // Pastikan footer tetap terlihat
        if (footer) {
            footer.style.display = 'flex';
        }
    }

    // Log untuk debugging
    console.log('Fetching notifications...');

    console.log('Fetching notifications from API...');

    return fetch('/api/notifications', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
        },
        // Tambahkan cache: 'no-store' untuk memastikan selalu mendapatkan data terbaru
        cache: 'no-store'
    })
    .then(function(res) {
        console.log('API response status:', res.status);
        if (!res.ok) {
            throw new Error('HTTP error! status: ' + res.status);
        }
        return res.json();
    })
    .then(function(data) {
        console.log('Notification data received:', data);

        // Periksa apakah data valid
        if (!data || !data.success) {
            console.error('Invalid notification data received:', data);
            throw new Error('Invalid notification data');
        }

        // Periksa apakah ada perubahan data notifikasi
        const hasChanges = !lastNotificationData ||
                          JSON.stringify(data.notifications) !== JSON.stringify(lastNotificationData.notifications) ||
                          data.unread_count !== lastNotificationData.unread_count;

        // Simpan data terbaru untuk perbandingan berikutnya
        lastNotificationData = data;

        console.log('Notification data updated, unread count:', data.unread_count);

        // Update jumlah notifikasi yang belum dibaca di footer dan badge
        const unreadCountElement = document.getElementById('notification-unread-count');
        if (unreadCountElement) {
            unreadCountElement.textContent = data.unread_count || 0;
            unreadCountElement.className = data.unread_count > 0
                ? "bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold mr-2"
                : "bg-gray-400 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold mr-2";
        }

        // Pastikan badge notifikasi juga diperbarui secara konsisten (desktop dan mobile)
        const notificationBadge = document.getElementById('notification-badge');
        const mobileNotificationBadge = document.getElementById('mobile-notification-badge');

        if (data.unread_count && data.unread_count > 0) {
            // Update desktop badge
            if (notificationBadge) {
                notificationBadge.style.display = '';
                notificationBadge.textContent = data.unread_count;
                notificationBadge.className = "absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold shadow-md notification-badge";
            }

            // Update mobile badge
            if (mobileNotificationBadge) {
                mobileNotificationBadge.style.display = '';
                mobileNotificationBadge.textContent = data.unread_count;
            }
        } else {
            // Hide badges when no unread notifications
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
            if (mobileNotificationBadge) {
                mobileNotificationBadge.style.display = 'none';
            }
        }

        // Log notifikasi untuk debugging
        if (data.notifications && data.notifications.length > 0) {
            console.log('Notifikasi yang diterima:', data.notifications);
        } else {
            console.log('Tidak ada notifikasi yang diterima dari server');
        }

        // Hanya perbarui UI jika ada perubahan atau dropdown terlihat
        if (hasChanges || isDropdownVisible) {
            if (data.notifications && Array.isArray(data.notifications) && data.notifications.length > 0) {
                // Tidak perlu header karena sudah ada di template
                var notificationHtml = '';
                for (var i = 0; i < data.notifications.length; i++) {
                    notificationHtml += renderNotificationItem(data.notifications[i]);
                }
                dropdown.innerHTML = notificationHtml;

                // Tambahkan animasi untuk notifikasi baru jika ada perubahan
                if (hasChanges) {
                    const notificationItems = dropdown.querySelectorAll('.notification-item');
                    for (var i = 0; i < notificationItems.length; i++) {
                        (function(item, index) {
                            item.classList.add('animate-pulse-once');
                            setTimeout(function() {
                                item.classList.remove('animate-pulse-once');
                            }, 1000 + (index * 100));
                        })(notificationItems[i], i);
                    }
                }
            } else {
                console.log('No notifications found, showing empty state');
                dropdown.innerHTML = `
            <div class='p-8 text-center'>
                <div class='w-16 h-16 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4'>
                    <i class="fas fa-bell-slash text-gray-400 text-xl"></i>
                </div>
                <p class='text-gray-500 text-sm mb-2'>Tidak ada notifikasi baru</p>
                <p class='text-gray-400 text-xs'>Semua pemberitahuan akan muncul di sini</p>
            </div>`;

                // Pastikan footer tetap terlihat
                const footer = document.getElementById('notification-footer');
                if (footer) {
                    footer.style.display = 'flex';
                }
            }
        }

        if (data.unread_count && data.unread_count > 0) {
            badge.style.display = '';
            badge.textContent = data.unread_count;
        } else {
            badge.style.display = 'none';
        }

        return data;
    })
    .catch(function(error) {
        console.error('Error fetching notifications:', error);
        if (dropdown) {
            dropdown.innerHTML = `<div class='px-4 py-6 text-center text-red-400 text-sm'>
    <div class='flex justify-center items-center space-x-2'>
        <svg class='h-5 w-5 text-red-500' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z' />
        </svg>
        <span>Gagal memuat notifikasi</span>
    </div>
    <div class='mt-2'>
        <button onclick='fetchNotifications()' class='text-xs text-blue-500 hover:text-blue-700 underline'>Coba lagi</button>
    </div>
</div>`;

            // Pastikan footer tetap terlihat
            const footer = document.getElementById('notification-footer');
            if (footer) {
                footer.style.display = 'flex';
            }
        }
        if (badge) {
            badge.style.display = 'none';
        }

        // Coba lagi setelah 30 detik
        setTimeout(fetchNotifications, 30000);
    });
}

// Fungsi untuk menangani error loading avatar
function handleAvatarError(img) {
    if (!img) return;

    const userName = img.getAttribute('alt') || 'User';
    const initial = userName.charAt(0).toUpperCase();

    // Prevent infinite loop
    if (img.src.includes('ui-avatars.com')) return;

    console.error('Failed to load avatar image for ' + userName);

    // Ganti dengan default avatar dari UI Avatars
    img.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(initial)}&background=6366f1&color=ffffff&size=200`;

    // Log error ke server (optional)
    if (typeof fetch !== 'undefined' && document.querySelector('meta[name="csrf-token"]')) {
        fetch('/api/log-error', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                type: 'avatar_load_error',
                message: 'Failed to load avatar image',
                user: userName
            })
        }).catch(function(err) {
            console.error('Failed to log error:', err);
        });
    }
}

// Fungsi untuk memperbarui notifikasi secara real-time dengan teknik long polling
function setupRealTimeNotifications() {
    console.log('Setting up real-time notifications...');

    // Definisikan polling interval
    const pollingInterval = 15000; // 15 detik

    // Pertama, ambil notifikasi awal dengan sedikit delay untuk memastikan DOM sudah siap
    setTimeout(function() {
        console.log('Fetching initial notifications...');
        fetchNotifications()
            .then(function(data) {
                console.log('Initial notifications fetched successfully:', data);
                // Kemudian, mulai polling dengan interval yang lebih cepat
                setTimeout(startPolling, pollingInterval);
            })
            .catch(function(error) {
                console.error('Failed to fetch initial notifications:', error);
                // Tetap mulai polling meskipun terjadi error
                setTimeout(startPolling, pollingInterval * 2); // Tunggu lebih lama jika terjadi error
            });
    }, 1000);

    // Gunakan teknik polling yang lebih efisien dengan jitter untuk menghindari thundering herd
    function startPolling() {
        console.log('Polling for new notifications...');
        fetchNotifications()
            .then(function(data) {
                console.log('Notifications polled successfully:', data ? data.unread_count : 'no data');
                // Tambahkan jitter (±2000ms) untuk menghindari sinkronisasi polling dari banyak klien
                const jitter = Math.random() * 4000 - 2000;
                setTimeout(startPolling, pollingInterval + jitter);
            })
            .catch(function(error) {
                console.error('Error in notification polling:', error);
                // Tetap lanjutkan polling meskipun terjadi error, tapi dengan interval yang lebih lama
                const jitter = Math.random() * 2000;
                setTimeout(startPolling, pollingInterval * 2 + jitter);
            });
    }
}

// DOM Ready handler
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing notifications...');

    // Initialize notifications dengan sistem real-time
    setupRealTimeNotifications();

    // Set up notification bell click handler
    const bell = document.getElementById('notification-bell');
    if (bell) {
        console.log('Notification bell found, adding click listener');
        bell.addEventListener('click', function() {
            console.log('Notification bell clicked');
            fetchNotifications();

            // Mark all as read
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(function() {
                    console.log('Notifications marked as read');
                    const badge = document.getElementById('notification-badge');
                    if (badge) badge.style.display = 'none';
                }).catch(function(err) {
                    console.error('Failed to mark notifications as read:', err);
                });
            }
        });
    } else {
        console.warn('Notification bell not found in DOM');
    }

    // Set up avatar error handlers
    const avatarImages = document.querySelectorAll('img[id*="profile-photo"], img[alt*="Profile"]');
    for (var i = 0; i < avatarImages.length; i++) {
        avatarImages[i].onerror = function() {
            handleAvatarError(this);
            return true;
        };
    }
});

// Make handleAvatarError globally available for inline onerror handlers
window.handleAvatarError = handleAvatarError;

// Function to navigate to notifications page
function goToNotifications() {
    window.location.href = '/notifications';
}

// Make goToNotifications globally available
window.goToNotifications = goToNotifications;

// Function to show access warning for restricted menus
function showAccessWarning(message) {
    // Remove any existing access warning
    const existingWarning = document.getElementById('access-warning-banner');
    if (existingWarning) {
        existingWarning.remove();
    }

    // Create professional notification banner
    const warningBanner = document.createElement('div');
    warningBanner.id = 'access-warning-banner';
    warningBanner.className = 'fixed top-0 left-0 right-0 z-50 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-lg';
    warningBanner.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="font-medium">${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;

    // Insert banner at the top of the page
    document.body.insertBefore(warningBanner, document.body.firstChild);

    // Auto-remove after 8 seconds
    setTimeout(() => {
        if (warningBanner.parentNode) {
            warningBanner.remove();
        }
    }, 8000);
}

// Make showAccessWarning globally available
window.showAccessWarning = showAccessWarning;
</script>
