@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white pt-20 sm:pt-24">
    

    <!-- Bar Informasi User Management -->
    <div class="max-w-7xl w-full mx-auto px-3 sm:px-6 mt-4">
        <div class="glass-card rounded-xl p-6 mb-6 fade-in border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 mb-2">Informasi Menu User Management</h2>
                    <p class="text-gray-700 text-base">
                        Halaman ini digunakan untuk mengelola data pengguna sistem SPPD. Hanya admin yang dapat menambah, mengubah, atau menghapus user.
                    </p>
                </div>
            </div>
        </div>
        <!-- Statistics Cards with Modern Design -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 sm:mb-8">
            <!-- Total Users Card -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">All registered users</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Active Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active'] }}</p>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                            <p class="text-xs text-gray-400">Currently active</p>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Leadership Card -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Leadership</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pimpinan'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">Management roles</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-crown text-purple-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Users Card -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Inactive</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['inactive'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">Deactivated accounts</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-red-50 to-red-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-user-times text-red-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters Section -->
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 mb-6 border border-gray-100 w-full max-w-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter text-gray-400 mr-2 text-xs"></i>
                    Filters
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input id="searchInput"
                               type="text"
                               placeholder="Name, email, or NIP..."
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="roleFilter"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <option value="">All Roles</option>
                        <option value="staff">staff</option>
                        <option value="kasubbag">kasubbag</option>
                        <option value="sekretaris">sekretaris</option>
                        <option value="ppk">ppk</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- Users Table with Modern Design -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 w-full max-w-full">
            <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-users text-blue-600 text-xs mr-2"></i>
                    User Directory
                </h3>
                <button onclick="openAddUserModal()" class="bg-blue-500 hover:bg-blue-600 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="overflow-x-auto w-full max-w-full">
                <table class="min-w-full w-full max-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                User Info
                            </th>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Identification
                            </th>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Department
                            </th>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Last Activity
                            </th>
                            <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="user-table-body">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="relative">
                                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br {{ $user->is_active ? 'from-blue-400 to-blue-600' : 'from-gray-400 to-gray-600' }} flex items-center justify-center text-white font-semibold shadow-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        @if($user->is_active)
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                            <i class="fas fa-envelope mr-1 text-gray-400"></i>
                                            {{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">{{ $user->nip ?? '-' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $user->jabatan ?? '-' }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                @php
                                    $roleStyles = [
                                        'staff' => 'bg-gray-100 text-gray-700 border-gray-200',
                                        'kasubbag' => 'bg-purple-100 text-purple-700 border-purple-200',
                                        'sekretaris' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'ppk' => 'bg-green-100 text-green-700 border-green-200',
                                        'admin' => 'bg-yellow-100 text-yellow-700 border-yellow-200'
                                    ];
                                    $style = $roleStyles[$user->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium {{ $style }} border">
                                    {{ $user->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $user->role }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->unit_kerja ?? '-' }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                        <span class="text-sm font-medium text-green-700">Active</span>
                                    </div>
                                @else
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                                        <span class="text-sm font-medium text-gray-500">Inactive</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                @if($user->last_login_at)
                                    <div class="text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($user->last_login_at)->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($user->last_login_at)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Never logged in</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <button class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($user->is_active)
                                    <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->name }}', false)"
                                            class="p-2 bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 rounded-lg transition-all duration-200 flex items-center justify-center"
                                            title="Nonaktif">
                                        <span class="text-xs font-medium">Nonaktif</span>
                                    </button>
                                    @else
                                    <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->name }}', true)"
                                            class="p-2 bg-green-100 text-green-600 hover:bg-green-200 hover:text-green-700 rounded-lg transition-all duration-200 flex items-center justify-center"
                                            title="Aktif">
                                        <span class="text-xs font-medium">Aktif</span>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-3xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">No Users Found</h3>
                                    <p class="text-gray-500 text-sm">Start by adding your first user to the system.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="px-3 sm:px-6 py-3 sm:py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
            @endif
        </div>

        <!-- Footer Info -->
        <div class="mt-4 sm:mt-6 bg-white rounded-2xl p-4 sm:p-6 text-gray-800 border border-gray-200 w-full max-w-full">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-info-circle text-gray-500"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base">System Information</h4>
                        {{-- <p class="text-sm text-gray-500 mt-0.5">Real-time data • Last updated: {{ now()->format('d M Y, H:i') }}</p> --}}
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                    <p class="text-sm text-gray-500">Total Users</p>
                </div>
            </div>
        </div>
        <div class="w-full text-center mt-6 sm:mt-8 mb-4" id="last-updated-container">
            <p class="text-sm text-gray-500" id="last-updated">Last updated: {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>
</div>

<!-- Modern Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-3 sm:p-5 w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-full">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-red-700 to-red-600 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="text-base font-semibold">Tambah Pengguna Baru</h3>
                    </div>
                    <button type="button"
                            class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-gray-100 transition-colors"
                            onclick="closeAddUserModal()">
                        <svg class="w-8 h-8" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                            <line x1="6" y1="6" x2="18" y2="18" />
                            <line x1="6" y1="18" x2="18" y2="6" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="addUserForm" onsubmit="submitAddUser(event)" class="p-3 sm:p-6 w-full max-w-full">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-5">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="nama@email.com">
                    </div>

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            NIP (Nomor Induk Pegawai) <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="nip"
                               required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Masukkan NIP">
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role"
                                required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="">Pilih Role</option>
                            <option value="staff">Staff</option>
                            <option value="kasubbag">Kasubbag</option>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="ppk">PPK</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <!-- Position -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Jabatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="jabatan"
                               required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Masukkan jabatan">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nomor HP
                        </label>
                        <input type="text"
                               name="phone"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="08xxxxxxxxxx">
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Alamat
                        </label>
                        <textarea name="address"
                                  rows="2"
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                  placeholder="Masukkan alamat lengkap"></textarea>
                    </div>

                    <!-- Rank -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Pangkat
                        </label>
                        <input type="text"
                               name="pangkat"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Masukkan pangkat">
                    </div>

                    <!-- Grade -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Golongan
                        </label>
                        <input type="text"
                               name="golongan"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Masukkan golongan">
                    </div>

                    <!-- Work Unit -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Unit Kerja
                        </label>
                        <input type="text"
                               name="unit_kerja"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Masukkan unit kerja">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="••••••••">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Konfirmasi Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               name="password_confirmation"
                               required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="••••••••">
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end mt-6 sm:mt-8 space-x-3 pt-4 sm:pt-6 border-t border-gray-100">
                    <button type="button"
                            onclick="closeAddUserModal()"
                            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all duration-200">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-all duration-200 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const userTableBody = document.querySelector('#user-table-body');

    function fetchUsers() {
        const search = searchInput.value;
        const role = roleFilter.value;
        const status = statusFilter.value;
        const params = new URLSearchParams({search, role, status});
        fetch(`/users/list-json?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            userTableBody.innerHTML = data.html;
        });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(window._userFilterTimeout);
        window._userFilterTimeout = setTimeout(fetchUsers, 400);
    });
    roleFilter.addEventListener('change', fetchUsers);
    statusFilter.addEventListener('change', fetchUsers);
});

// Modal functions
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.getElementById('addUserForm').reset();
    document.body.style.overflow = 'auto';
}

// Submit add user form
async function submitAddUser(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');

    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

    try {
        const response = await fetch('{{ route("users.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        if (result.success) {
            // Show success notification
            showNotification('success', result.message || 'User created successfully!');
            closeAddUserModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            // Tampilkan error detail jika ada
            if (result.errors) {
                let messages = [];
                Object.keys(result.errors).forEach(function(key) {
                    messages = messages.concat(result.errors[key]);
                });
                messages.forEach(msg => showNotification('error', msg));
            } else {
                showNotification('error', result.message || 'Failed to create user');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', 'An error occurred while saving the user');
    } finally {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
    }
}

// Toggle user status
async function toggleUserStatus(userId, userName, activateUser) {
    const action = activateUser ? 'mengaktifkan' : 'menonaktifkan';

    if (!confirm(`Apakah kamu yakin untuk ${action} ${userName}?`)) {
        return;
    }

    try {
        const response = await fetch(`{{ url('/users') }}/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PATCH'
            }
        });

        const result = await response.json();

        if (result.success) {
            showNotification('success', result.message);
            // Reload halaman untuk menampilkan perubahan status
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', result.message || `Gagal ${action} ${userName}!`);
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', `Terjadi kesalahan saat ${action} ${userName}`);
    }
}

// Show notification
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>