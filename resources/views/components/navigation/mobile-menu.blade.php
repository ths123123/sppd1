@php
    $user = Auth::user();
    $role = $user?->role;
@endphp

<style>
.mobile-offcanvas {
  position: fixed;
  top: 0; right: 0; bottom: 0; left: 0;
  z-index: 9999;
  background: rgba(0,0,0,0.25);
  transition: background 0.2s;
}
.mobile-offcanvas-panel {
  position: absolute;
  top: 0; right: 0; height: 100%;
  width: 90vw; max-width: 370px;
  background: #fff;
  box-shadow: -4px 0 32px rgba(0,0,0,0.12);
  border-radius: 1.25rem 0 0 1.25rem;
  display: flex; flex-direction: column;
  transition: transform 0.3s cubic-bezier(.4,2,.6,1), box-shadow 0.2s;
}
.mobile-offcanvas[x-show="open"] .mobile-offcanvas-panel {
  transform: translateX(0);
}
.mobile-menu-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.25rem 1.5rem 0.5rem 1.5rem;
  border-bottom: 1px solid #f3f4f6;
}
.mobile-menu-title {
  font-size: 1.1rem; font-weight: bold; color: #8B0000;
  letter-spacing: 0.01em;
}
.mobile-menu-list {
  flex: 1 1 auto;
  overflow-y: auto;
  padding: 1rem 0.5rem 1rem 0.5rem;
}
.mobile-menu-section {
  font-size: 0.8rem; color: #8B0000; font-weight: 600;
  margin: 1.2rem 0 0.5rem 1.2rem;
  letter-spacing: 0.04em;
}
.mobile-menu-item {
  display: flex; align-items: center;
  gap: 1rem;
  padding: 0.9rem 1.2rem;
  border-radius: 0.75rem;
  font-size: 1.05rem;
  color: #222;
  font-weight: 500;
  background: none;
  transition: background 0.15s, color 0.15s;
}
.mobile-menu-item:hover, .mobile-menu-item.active {
  background: #f3f4f6;
  color: #8B0000;
}
.mobile-menu-icon {
  width: 1.5rem; height: 1.5rem;
  color: #8B0000;
  flex-shrink: 0;
}
.mobile-menu-expand {
  margin-left: auto;
  transition: transform 0.2s;
}
.mobile-menu-expand[aria-expanded="true"] {
  transform: rotate(90deg);
}
</style>

<div x-data="{ open: false, sppdOpen: false, analyticsOpen: false, dokumenOpen: false, closeAll() { this.sppdOpen = this.analyticsOpen = this.dokumenOpen = false; } }" class="sm:hidden">
    <!-- Hamburger Button -->
    <button @click="open = true" class="inline-flex items-center justify-center p-2 rounded-md bg-white text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out" aria-label="Main menu" aria-expanded="false">
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Offcanvas Mobile Menu -->
    <div x-show="open" x-transition.opacity class="mobile-offcanvas" @click.self="open = false; closeAll();" style="display: none;">
        <nav class="mobile-offcanvas-panel" x-transition:enter="transition transform duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="mobile-menu-header flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-12 object-contain rounded-lg shadow-sm mr-2">
                <span class="mobile-menu-title text-base font-bold text-[#8B0000] whitespace-nowrap">KPU Kabupaten Cirebon</span>
                <button @click="open = false; closeAll();" class="ml-auto text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="mobile-menu-list">
                <div class="mobile-menu-section">MENU UTAMA</div>
                <a href="{{ route('dashboard') }}" class="mobile-menu-item text-black {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" /></svg>
                    Dashboard
                </a>

                <!-- Approval - Tampilkan untuk semua user -->
                <a href="{{ route('approval.pimpinan.index') }}"
                   data-requires-role="approver"
                   class="mobile-menu-item text-black {{ request()->routeIs('approval.pimpinan.*') ? 'active' : '' }} {{ !in_array($role, ['sekretaris', 'ppk']) ? 'opacity-50 cursor-not-allowed' : '' }}"
                   @if(!in_array($role, ['sekretaris', 'ppk']))
                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                   @endif>
                    <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 9a2 2 0 012-2h2a2 2 0 012 2m-6 0a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    Approval
                </a>

                <!-- SPPD Group - Tampilkan untuk semua user -->
                <button @click="sppdOpen = !sppdOpen; analyticsOpen = dokumenOpen = false;" class="mobile-menu-item w-full justify-between text-black" :aria-expanded="sppdOpen">
                    <span class="flex items-center gap-2">
                        <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.5 6.5l7 7m0 0l-7 7m7-7H3" /></svg>
                        SPPD
                    </span>
                    <svg class="w-5 h-5 text-gray-400 ml-auto transition-transform duration-200" :class="sppdOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                </button>
                <div x-show="sppdOpen" x-transition class="pl-10 space-y-1">
                    <!-- Buat SPPD - Tampilkan untuk semua user -->
                    <a href="{{ route('travel-requests.create') }}"
                       data-requires-role="kasubbag"
                       class="mobile-menu-item text-black"
                       @if($role !== 'kasubbag')
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        Buat SPPD
                    </a>

                    <!-- SPPD Saya - Tampilkan untuk semua user -->
                    <a href="{{ route('my-travel-requests.index') }}" class="mobile-menu-item text-black">
                        SPPD Saya
                    </a>

                    <!-- Daftar SPPD - Tampilkan untuk semua user -->
                    <a href="{{ route('travel-requests.index') }}"
                       data-requires-role="view_all_sppd"
                       class="mobile-menu-item text-black"
                       @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        Daftar SPPD
                    </a>
                </div>

                <!-- Analytics & Laporan Group - Tampilkan untuk semua user -->
                <button @click="analyticsOpen = !analyticsOpen; sppdOpen = dokumenOpen = false;" class="mobile-menu-item w-full justify-between text-black" :aria-expanded="analyticsOpen">
                    <span class="flex items-center gap-2">
                        <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" /></svg>
                        Analytics & Laporan
                    </span>
                    <svg class="w-5 h-5 text-gray-400 ml-auto transition-transform duration-200" :class="analyticsOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                </button>
                <div x-show="analyticsOpen" x-transition class="pl-10 space-y-1">
                    <a href="{{ route('analytics.index') }}"
                       data-requires-role="analytics"
                       class="mobile-menu-item text-black"
                       @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk']))
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        Analytics
                    </a>
                    <a href="{{ route('laporan.daftar') }}"
                       data-requires-role="analytics"
                       class="mobile-menu-item text-black"
                       @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk']))
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        Laporan
                    </a>
                </div>

                <!-- Dokumen Group - Tampilkan untuk semua user -->
                <button @click="dokumenOpen = !dokumenOpen; sppdOpen = analyticsOpen = false;" class="mobile-menu-item w-full justify-between text-black" :aria-expanded="dokumenOpen">
                    <span class="flex items-center gap-2">
                        <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 19V5a2 2 0 012-2h6a2 2 0 012 2v14a2 2 0 01-2 2H8a2 2 0 01-2-2z" /></svg>
                        Dokumen
                    </span>
                    <svg class="w-5 h-5 text-gray-400 ml-auto transition-transform duration-200" :class="dokumenOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                </button>
                <div x-show="dokumenOpen" x-transition class="pl-10 space-y-1">
                    <a href="{{ route('documents.index') }}" class="mobile-menu-item text-black">
                        Dokumen Saya
                    </a>
                    <a href="{{ route('templates.index') }}"
                       data-requires-role="document_management"
                       class="mobile-menu-item text-black"
                       @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                       onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                       @endif>
                        Manajemen Template
                    </a>
                </div>

                <!-- Kelola User - Tampilkan untuk semua user -->
                <div class="mobile-menu-section">MENU MANAJEMEN</div>
                <a href="{{ route('users.index') }}"
                   data-requires-role="user_management"
                   class="mobile-menu-item text-black {{ request()->routeIs('users.*') ? 'active' : '' }}"
                   @if(!in_array($role, ['kasubbag', 'sekretaris', 'ppk', 'admin']))
                   onclick="event.preventDefault(); showAccessWarning('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');"
                   @endif>
                    <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Kelola User
                </a>

                <div class="mobile-menu-section">AKUN</div>
                <a href="/notifications" class="mobile-menu-item text-black">
                    <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    Notifikasi
                    <span id="mobile-notification-badge" class="ml-2 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold shadow-md" style="display:none;">0</span>
                </a>
                <a href="{{ route('profile.show') }}" class="mobile-menu-item text-black">
                    <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Profil Saya
                </a>
                <a href="{{ route('settings.index') }}" class="mobile-menu-item text-black">
                    <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Setting
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-menu-item w-full text-left text-black">
                        <svg class="mobile-menu-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>
</div>
