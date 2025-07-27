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
                        <div class="w-10 h-10 bg-[#8B0000] rounded-lg shadow-sm flex items-center justify-center mr-3 hover:shadow-md transition-all duration-300 transform hover:scale-105">
                           <img src="{{ asset('images/logo.png') }}" alt="KPU Logo" class="w-6 h-6 object-contain">
                        </div>
                        <!-- Logo text - tampil di semua ukuran layar -->
                        <div class="block">
                            <h1 class="text-sm font-bold text-gray-900 tracking-wide">KPU Kabupaten Cirebon</h1>
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
                    <a href="{{ route('dashboard') }}" class="text-gray-900 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                        <span>{{ __('Dashboard') }}</span>
                    </a>

                    <!-- Approval (tanpa dropdown, hanya untuk sekretaris & ppk) -->
                    @if(in_array($role, ['sekretaris', 'ppk']))
                    <a href="{{ route('approval.pimpinan.index') }}" class="text-gray-900 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                        <span>Approval</span>
                    </a>
                    @endif

                    <!-- SPPD Group -->
                    <div class="relative" x-data="{ sppdDropdown: false }" @mouseenter="sppdDropdown = true" @mouseleave="sppdDropdown = false">
                        <button 
                            @click="sppdDropdown = !sppdDropdown"
                            class="text-gray-900 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                            <span>SPPD</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="sppdDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div 
                            x-show="sppdDropdown" 
                            @click.away="sppdDropdown = false"
                            x-transition:enter="transition ease-out duration-200" 
                            x-transition:enter-start="opacity-0 scale-95" 
                            x-transition:enter-end="opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-75" 
                            x-transition:leave-start="opacity-100 scale-100" 
                            x-transition:leave-end="opacity-0 scale-95" 
                            class="absolute left-0 mt-2 min-w-[180px] max-w-xs bg-white rounded-lg shadow-xl z-50 border border-gray-200 py-1">
                            @if($role === 'kasubbag')
                            <a href="{{ route('travel-requests.create') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">Buat SPPD</a>
                            @endif
                            <a href="{{ route('my-travel-requests.index') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">SPPD Saya</a>
                            @if(in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                                <a href="{{ route('travel-requests.index') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">Daftar SPPD</a>
                            @endif
                        </div>
                    </div>

                    <!-- Analytics & Laporan Group -->
                    @if(in_array($role, ['kasubbag', 'sekretaris', 'ppk']))
                    <div class="relative" x-data="{ analyticsDropdown: false }" @mouseenter="analyticsDropdown = true" @mouseleave="analyticsDropdown = false">
                        <button 
                            @click="analyticsDropdown = !analyticsDropdown"
                            class="text-gray-900 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                            <span>Analytics & Laporan</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="analyticsDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div 
                            x-show="analyticsDropdown" 
                            @click.away="analyticsDropdown = false"
                            x-transition:enter="transition ease-out duration-200" 
                            x-transition:enter-start="opacity-0 scale-95" 
                            x-transition:enter-end="opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-75" 
                            x-transition:leave-start="opacity-100 scale-100" 
                            x-transition:leave-end="opacity-0 scale-95" 
                            class="absolute left-0 mt-2 min-w-[180px] max-w-xs bg-white rounded-lg shadow-xl z-50 border border-gray-200 py-1">
                            <a href="{{ route('analytics.index') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">Analytics</a>
                            <a href="{{ route('laporan.index') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">Laporan</a>
                        </div>
                    </div>
                    @endif

                    <!-- Dokumen -->
                    <div class="relative" x-data="{ dokumenDropdown: false }" @mouseenter="dokumenDropdown = true" @mouseleave="dokumenDropdown = false">
                        <button 
                            @click="dokumenDropdown = !dokumenDropdown"
                            class="text-gray-900 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                            <span>Dokumen</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="dokumenDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div 
                            x-show="dokumenDropdown" 
                            @click.away="dokumenDropdown = false"
                            x-transition:enter="transition ease-out duration-200" 
                            x-transition:enter-start="opacity-0 scale-95" 
                            x-transition:enter-end="opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-75" 
                            x-transition:leave-start="opacity-100 scale-100" 
                            x-transition:leave-end="opacity-0 scale-95" 
                            class="absolute left-0 mt-2 min-w-[180px] max-w-xs bg-white rounded-lg shadow-xl z-50 border border-gray-200 py-1">
                            <a href="{{ route('documents.index') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">Dokumen Saya</a>
                            @if(in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                                <a href="{{ route('templates.index') }}" class="block px-5 py-2 text-gray-800 text-base font-medium hover:bg-gray-100 rounded-md transition-colors duration-200">Manajemen Template</a>
                            @endif
                        </div>
                    </div>

                    <!-- Kelola User -->
                    @if(in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                    <a href="{{ route('users.index') }}" class="text-gray-900 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-colors duration-200">
                        <span>Kelola User</span>
                    </a>
                    @endif
                </div>

                <!-- Notifications & Professional User Profile -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div x-data="{ notificationDropdown: false }" class="relative">
                        <button @click="notificationDropdown = !notificationDropdown" id="notification-bell" class="relative p-2 text-gray-900 hover:text-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors duration-200">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 01-3.46 0" />
                            </svg>
                            <!-- Notification Badge -->
                            <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold" style="display:none;">0</span>
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
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200">
                            <div id="notification-dropdown-content">
                                <div class="px-4 py-6 text-center text-gray-400 text-sm">Memuat notifikasi...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional User Profile Dropdown -->
                    <div x-data="{ profileDropdown: false }" class="relative">
                        <button @click="profileDropdown = !profileDropdown" class="group flex items-center space-x-2 px-2 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400 text-gray-900 hover:text-gray-600 transition-colors duration-200">
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
    let icon = '';
    
    if (n.type === 'info') {
        icon = `<div class='flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center'><svg class='w-5 h-5 text-blue-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01' /></svg></div>`;
    } else if (n.type === 'success') {
        icon = `<div class='flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center'><svg class='w-5 h-5 text-green-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' /></svg></div>`;
    } else if (n.type === 'warning') {
        icon = `<div class='flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center'><svg class='w-5 h-5 text-yellow-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01' /></svg></div>`;
    } else if (n.type === 'error') {
        icon = `<div class='flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center'><svg class='w-5 h-5 text-red-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg></div>`;
    } else {
        icon = `<div class='flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center'><svg class='w-5 h-5 text-gray-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'><circle cx='12' cy='12' r='10' /></svg></div>`;
    }
    
    let time = new Date(n.created_at).toLocaleString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit', 
        day: '2-digit', 
        month: 'short' 
    });
    
    return `<div class='px-4 py-3 hover:bg-gray-50 border-b border-gray-50'>
        <div class='flex items-start space-x-3'>
            ${icon}
            <div class='flex-1 min-w-0'>
                <p class='text-sm font-medium text-gray-900'>${n.title || '-'}</p>
                <p class='text-xs text-gray-500'>${n.message || ''}</p>
                <p class='text-xs text-gray-400 mt-1'>${time}</p>
            </div>
        </div>
    </div>`;
}

async function fetchNotifications() {
    const badge = document.getElementById('notification-badge');
    const dropdown = document.getElementById('notification-dropdown-content');
    
    if (!badge || !dropdown) return;
    
    dropdown.innerHTML = `<div class='px-4 py-6 text-center text-gray-400 text-sm'>Memuat notifikasi...</div>`;
    
    try {
        const res = await fetch('/api/notifications', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const data = await res.json();
        
        if (data.notifications && Array.isArray(data.notifications) && data.notifications.length > 0) {
            dropdown.innerHTML = `<div class='px-4 py-3 border-b border-gray-100'><h3 class='text-sm font-semibold text-gray-900'>Notifikasi</h3><p class='text-xs text-gray-500'>Anda memiliki ${data.unread_count || 0} notifikasi baru</p></div>`;
            dropdown.innerHTML += `<div class='max-h-64 overflow-y-auto'>` + data.notifications.map(renderNotificationItem).join('') + `</div>`;
            dropdown.innerHTML += `<div class='px-4 py-3 border-t border-gray-100 bg-gray-50'><a href='/notifications' class='text-xs text-gray-600 hover:text-gray-800 font-medium'>Lihat semua notifikasi</a></div>`;
        } else {
            dropdown.innerHTML = `<div class='px-4 py-6 text-center text-gray-400 text-sm'>Tidak ada notifikasi baru</div>`;
        }
        
        if (data.unread_count && data.unread_count > 0) {
            badge.style.display = '';
            badge.textContent = data.unread_count;
        } else {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
        dropdown.innerHTML = `<div class='px-4 py-6 text-center text-red-400 text-sm'>Gagal memuat notifikasi</div>`;
        badge.style.display = 'none';
    }
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
        }).catch(err => console.error('Failed to log error:', err));
    }
}

// DOM Ready handler
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notifications
    fetchNotifications();
    
    // Set up notification polling
    setInterval(fetchNotifications, 30000);
    
    // Set up notification bell click handler
    const bell = document.getElementById('notification-bell');
    if (bell) {
        bell.addEventListener('click', function() {
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
                }).then(() => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) badge.style.display = 'none';
                }).catch(err => console.error('Failed to mark notifications as read:', err));
            }
        });
    }
    
    // Set up avatar error handlers
    const avatarImages = document.querySelectorAll('img[id*="profile-photo"], img[alt*="Profile"]');
    avatarImages.forEach(img => {
        img.onerror = function() {
            handleAvatarError(this);
            return true;
        };
    });
});

// Make handleAvatarError globally available for inline onerror handlers
window.handleAvatarError = handleAvatarError;
</script>