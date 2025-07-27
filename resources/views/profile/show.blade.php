@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div class="py-4 sm:py-12 bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen mt-4 sm:mt-16">
    <div class="max-w-4xl w-full mx-auto sm:px-6 lg:px-8">
        <!-- Profile Card with Creative Border -->
        <div class="relative">
            <!-- Decorative Background Elements -->
            <div class="absolute -top-4 -left-4 w-24 h-24 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full opacity-20 animate-pulse"></div>
            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-gradient-to-br from-pink-400 to-red-500 rounded-full opacity-20 animate-pulse delay-1000"></div>

            <!-- Main Profile Card -->
            <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 w-full max-w-full px-2 sm:px-6 lg:px-8">
                <!-- Header Section with Gradient -->
                <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-4 sm:px-8 py-6 sm:py-12 w-full max-w-full">
                    <!-- Decorative Pattern Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-10"></div>
                    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <circle cx="10" cy="10" r="1" fill="white" fill-opacity="0.1"/>
                            </pattern>
                        </defs>
                        <rect width="100" height="100" fill="url(#pattern)"/>
                    </svg>

                    <div class="relative z-10 flex flex-col items-center text-white">                        <!-- Profile Photo with Creative Frame -->
                        <div class="relative mb-6">
                            <!-- Outer Decorative Ring -->
                            <div class="absolute -inset-4 bg-gradient-to-r from-yellow-400 via-pink-500 to-purple-600 rounded-full animate-spin-slow opacity-75"></div>

                            <!-- Middle Ring -->
                            <div class="relative w-40 h-40 bg-white rounded-full p-2 shadow-2xl">
                                <!-- Inner Ring -->
                                <div class="w-full h-full bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 rounded-full p-1">
                                    <!-- Photo Container -->
                                    <div class="w-full h-full bg-white rounded-full p-1">
                                        <div class="w-full h-full rounded-full overflow-hidden">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover object-center">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Indicator -->
                            <div class="absolute bottom-2 right-2 w-6 h-6 bg-green-500 border-4 border-white rounded-full animate-pulse"></div>
                        </div>

                        <!-- Name and Title -->
                        <h1 class="text-lg font-bold mb-2">{{ $user->name ?? '-' }}</h1>
                        @if($user->role === 'ppk')
                            <p class="text-xs opacity-60 mt-1">Role: Pejabat Pembuat Komitmen</p>
                        @else
                            <p class="text-xs opacity-60 mt-1">Role: {{ $user->getRoleDisplayName() }}</p>
                        @endif

                        <!-- Quick Actions -->
                        <div class="flex space-x-4 mt-6">
                            <a href="{{ route('profile.edit') }}"
                               class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-2 rounded-full transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30">
                                <i class="fas fa-edit mr-2"></i>Edit Profile
                            </a>
                            <button class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-2 rounded-full transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30">
                                <i class="fas fa-share mr-2"></i>Share
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Profile Information Section -->
                <div class="px-4 sm:px-8 py-6 sm:py-8 w-full max-w-full">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Personal Information -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-user text-blue-500 mr-3"></i>
                                    Personal Information
                                </h3>
                            </div>

                            <div class="space-y-4">
                                @if($user->email)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-envelope text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($user->phone)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-phone text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Phone</p>
                                        <p class="text-gray-900 font-medium">{{ $user->phone }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($user->birth_date)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-birthday-cake text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Birth Date</p>
                                        <p class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($user->birth_date)->format('d F Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($user->birth_date)->age }} years old</p>
                                    </div>
                                </div>
                                @endif

                                @if($user->gender)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-{{ $user->gender == 'male' ? 'user' : 'user' }} text-pink-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Gender</p>
                                        <p class="text-gray-900 font-medium">{{ ucfirst($user->gender) }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($user->address)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-map-marker-alt text-red-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Address</p>
                                        <p class="text-gray-900 font-medium">{{ $user->address }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-briefcase text-purple-500 mr-3"></i>
                                    Professional Information
                                </h3>
                            </div>

                            <div class="space-y-4">
                                @if($user->employee_id)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-id-badge text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Employee ID</p>
                                        <p class="text-gray-900 font-medium">{{ $user->employee_id }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($user->nip)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-fingerprint text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">NIP</p>
                                        <p class="text-gray-900 font-medium">{{ $user->nip }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($user->pangkat)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-medal text-teal-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Pangkat</p>
                                        <p class="text-gray-900 font-medium">{{ $user->pangkat }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-medal text-gray-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Pangkat</p>
                                        <p class="text-gray-900 font-medium">{{ $user->pangkat ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-layer-group text-gray-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Golongan</p>
                                        <p class="text-gray-900 font-medium">{{ $user->golongan ?? '-' }}</p>
                                    </div>
                                </div>

                                @if($user->unit_kerja)
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building text-cyan-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Unit Kerja</p>
                                        <p class="text-gray-900 font-medium">{{ $user->unit_kerja }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-tag text-gray-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Role/Jabatan</p>
                                        <p class="text-gray-900 font-medium">{{ $user->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $user->getRoleDisplayName() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    @if($user->bio)
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-quote-left text-gray-500 mr-3"></i>
                            About Me
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <p class="text-gray-700 leading-relaxed italic">{{ $user->bio }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Account Status -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 mb-2">Account Status</h3>
                                <div class="flex items-center space-x-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <span class="w-2 h-2 mr-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($user->last_login_at)
                                        <span class="text-sm text-gray-500">
                                            Last login: {{ $user->last_login_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Member Since -->
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Member since</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $user->created_at->format('M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 8s linear infinite;
}
</style>
@endsection
