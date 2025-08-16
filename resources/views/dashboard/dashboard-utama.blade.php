{{--
    ====================================================================
    DASHBOARD UTAMA - SISTEM SPPD KPU KABUPATEN CIREBON
    ====================================================================

    🎯 PROFESSIONAL DASHBOARD - ENHANCED VERSION

    📁 MODULAR COMPONENTS:
    ├── partials/header.blade.php       → Dashboard header & title
    ├── partials/statistics.blade.php   → Statistics cards
    ├── partials/charts.blade.php       → Chart visualizations
    └── assets/charts.js                → JavaScript module

    ✅ BENEFITS:
    - Modern & professional UI
    - Responsive design
    - Interactive elements
    - Improved user experience
    - Optimized performance

    ====================================================================
--}}

@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Notification Bar for Unauthorized Access -->
        <div id="unauthorized-notification" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md mb-4" role="alert">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">Anda tidak memiliki akses ke menu ini. Silakan hubungi Kasubbag.</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" onclick="document.getElementById('unauthorized-notification').classList.add('hidden')" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Welcome Banner --}}
        <div class="relative overflow-hidden bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl mb-8">
            <div class="absolute right-0 top-0 h-full w-1/2 overflow-hidden">
                <svg class="h-full w-full text-red-700 opacity-20" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <polygon points="50,0 100,0 50,100 0,100" />
                </svg>
                <div class="absolute right-0 bottom-0 opacity-10">
                    <svg width="180" height="180" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM16.59 7.58L10 14.17L7.41 11.59L6 13L10 17L18 9L16.59 7.58Z" fill="white"/>
                    </svg>
                </div>
            </div>
            <div class="relative px-8 py-10 sm:px-12 sm:py-12">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-2/3">
                        <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl mb-2">
                            Selamat Datang, {{ Auth::user()->name }}!
                        </h1>
                        <p class="text-red-100 text-lg mb-6 max-w-3xl">
                            Kelola dan pantau perjalanan dinas dengan sistem terintegrasi KPU Kabupaten Cirebon
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('travel-requests.create') }}" data-requires-role="kasubbag" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 {{ Auth::user()->role !== 'kasubbag' ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Buat SPPD Baru
                            </a>
                            <a href="{{ route('travel-requests.index') }}" data-requires-role="view_all_sppd" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-800 bg-opacity-60 hover:bg-opacity-70 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 {{ !in_array(Auth::user()->role, ['admin', 'kasubbag', 'sekretaris', 'ppk']) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Lihat Semua SPPD
                            </a>
                        </div>
                    </div>
                    <div class="md:w-1/3 mt-8 md:mt-0 flex justify-center">
                        <div class="bg-white bg-opacity-20 p-4 rounded-lg backdrop-filter backdrop-blur-sm border border-white border-opacity-20 shadow-lg">
                            <div class="text-center">
                                <div class="text-white text-opacity-90 text-sm font-medium mb-1">Terakhir diperbarui</div>
                                <div class="text-white text-lg font-bold">{{ now('Asia/Jakarta')->format('d M Y') }}</div>
                                <div class="text-white text-opacity-80 text-sm">{{ now('Asia/Jakarta')->format('H:i:s') }} WIB</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Approved Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="px-6 py-5 sm:px-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-green-400 to-green-600 p-3 rounded-lg shadow-md">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Disetujui</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-900" id="approved-count">{{ $approvedCount ?? 0 }}</p>
                                <p class="ml-2 text-sm text-green-600 font-medium">SPPD</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 px-6 py-3">
                    <div class="text-sm text-green-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Perjalanan dinas disetujui</span>
                    </div>
                </div>
            </div>

            <!-- Rejected Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="px-6 py-5 sm:px-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-red-400 to-red-600 p-3 rounded-lg shadow-md" style="box-shadow: 0 0 15px rgba(239, 68, 68, 0.5);">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-6">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ditolak</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-900" id="rejected-count">{{ $rejectedCount ?? 0 }}</p>
                                <p class="ml-2 text-sm text-red-600 font-medium">SPPD</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 px-6 py-3">
                    <div class="text-sm text-red-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Perjalanan dinas ditolak</span>
                    </div>
                </div>
            </div>

            <!-- Review Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="px-6 py-5 sm:px-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-yellow-400 to-yellow-400 p-3 rounded-lg shadow-md">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Dalam Review</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-900" id="review-count">{{ $reviewCount ?? 0 }}</p>
                                <p class="ml-2 text-sm text-yellow-600 font-medium">SPPD</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 px-6 py-3">
                    <div class="text-sm text-yellow-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Menunggu persetujuan</span>
                    </div>
                </div>
            </div>

            <!-- Diajukan Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="px-6 py-5 sm:px-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-blue-400 to-blue-600 p-3 rounded-lg shadow-md">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Dokumen</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-900" id="document-count">{{ $documentCount ?? 0 }}</p>
                                <p class="ml-2 text-sm text-blue-600 font-medium">SPPD</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 px-6 py-3">
                    <div class="text-sm text-blue-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"></path>
                            <path d="M10 5a1 1 0 011 1v3.586l2.707 2.707a1 1 0 01-1.414 1.414l-3-3A1 1 0 019 10V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Jumlah Dokumen di Sistem</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
         <div class="grid grid-cols-1 gap-8 mb-8">
            <!-- Monthly Trend Chart (Full Width) -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Tren Bulanan SPPD</h3>
                        <div class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">12 Bulan Terakhir</div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="chart-container h-80 relative w-full" style="min-height: 320px;">
                        <canvas id="monthlyChart" style="width: 100% !important; height: 100% !important; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; image-rendering: pixelated;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity & Quick Links --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden lg:col-span-2">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
                </div>
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto scrollbar-thin" id="recent-activities-container">
                    {{-- Konten aktivitas akan diisi oleh JavaScript --}}
                    <div class="px-6 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-900">Memuat aktivitas terbaru...</p>
                        <p class="mt-1 text-sm text-gray-500">Harap tunggu sebentar.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Akses Cepat</h3>
                </div>
                <div class="p-6 space-y-4">
                    <a href="{{ route('travel-requests.create') }}" data-requires-role="kasubbag" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200 {{ Auth::user()->role !== 'kasubbag' ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <div class="flex-shrink-0 bg-blue-500 p-2 rounded-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Buat SPPD Baru</p>
                            <p class="text-xs text-gray-500">Buat perjalanan dinas baru</p>
                        </div>
                    </a>
                    <a href="{{ route('my-travel-requests.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200">
                        <div class="flex-shrink-0 bg-green-500 p-2 rounded-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">SPPD Saya</p>
                            <p class="text-xs text-gray-500">Lihat perjalanan dinas Anda</p>
                        </div>
                    </a>
                    <a href="{{ route('approvals.index') }}" data-requires-role="approver" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors duration-200 {{ !in_array(Auth::user()->role, ['kasubbag', 'sekretaris', 'ppk']) ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <div class="flex-shrink-0 bg-purple-500 p-2 rounded-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Persetujuan</p>
                            <p class="text-xs text-gray-500">Kelola persetujuan SPPD</p>
                        </div>
                    </a>
                    <a href="{{ route('documents.index') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors duration-200">
                        <div class="flex-shrink-0 bg-yellow-500 p-2 rounded-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Dokumen</p>
                            <p class="text-xs text-gray-500">Akses dokumen perjalanan</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/dashboard/charts.js'])
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Prepare data from backend
        const dashboardData = {
            months: @json($months ?? []),
            monthlyApproved: @json($monthlyApproved ?? []),
            monthlyInReview: @json($monthlyInReview ?? []),
            monthlyRejected: @json($monthlyRejected ?? []),
            monthlySubmitted: @json($monthlySubmitted ?? []),
            statusDistribution: {
                approved: {{ $approvedCount ?? 0 }},
                submitted: {{ $submittedCount ?? 0 }},
                in_review: {{ $reviewCount ?? 0 }},
                rejected: {{ $rejectedCount ?? 0 }},
                completed: {{ $approvedCount ?? 0 }} // Tambahkan completed untuk kompatibilitas
            }
        };

        // Initialize dashboard
        if (window.DashboardManager) {
            window.DashboardManager.init(dashboardData);
            // Panggil fetchRealtimeDashboard untuk memperbarui data secara real-time
            if (typeof fetchRealtimeDashboard === 'function') {
                fetchRealtimeDashboard();
            }
        } else {
            console.error('DashboardManager not found! Make sure charts.js is loaded.');
        }

        // Tidak perlu inisialisasi Status Chart di sini karena sudah diinisialisasi di charts.js

        // Handle unauthorized access notification
        const userRole = "{{ Auth::user()->role }}";

        // Add event listeners to restricted links
        document.querySelectorAll('a[data-requires-role]').forEach(link => {
            const requiredRole = link.getAttribute('data-requires-role');

            // Sembunyikan menu jika pengguna tidak memiliki role yang diperlukan
            if (requiredRole === 'kasubbag' && userRole !== 'kasubbag') {
                link.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (requiredRole === 'approver' && !['kasubbag', 'sekretaris', 'ppk'].includes(userRole)) {
                link.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (requiredRole === 'view_all_sppd' && !['admin', 'kasubbag', 'sekretaris', 'ppk'].includes(userRole)) {
                link.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (requiredRole === 'analytics' && !['kasubbag', 'sekretaris', 'ppk'].includes(userRole)) {
                link.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (requiredRole === 'document_management' && !['admin', 'kasubbag', 'sekretaris', 'ppk'].includes(userRole)) {
                link.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (requiredRole === 'user_management' && !['admin', 'kasubbag', 'sekretaris', 'ppk'].includes(userRole)) {
                link.classList.add('opacity-50', 'cursor-not-allowed');
            }

            link.addEventListener('click', function(e) {
                if ((requiredRole === 'kasubbag' && userRole !== 'kasubbag') ||
                    (requiredRole === 'approver' && !['kasubbag', 'sekretaris', 'ppk'].includes(userRole)) ||
                    (requiredRole === 'view_all_sppd' && !['admin', 'kasubbag', 'sekretaris', 'ppk'].includes(userRole)) ||
                    (requiredRole === 'analytics' && !['kasubbag', 'sekretaris', 'ppk'].includes(userRole)) ||
                    (requiredRole === 'document_management' && !['admin', 'kasubbag', 'sekretaris', 'ppk'].includes(userRole)) ||
                    (requiredRole === 'user_management' && !['admin', 'kasubbag', 'sekretaris', 'ppk'].includes(userRole))) {
                    e.preventDefault();
                    document.getElementById('unauthorized-notification').classList.remove('hidden');

                    // Auto-hide notification after 5 seconds
                    setTimeout(() => {
                        document.getElementById('unauthorized-notification').classList.add('hidden');
                    }, 5000);
                }
            });
        });

        console.log('🎯 Dashboard SPPD KPU Kabupaten Cirebon - Enhanced Version Active');
    });
</script>
@endpush

@push('styles')
<style>
/* Custom scrollbar */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Card hover effects */
.hover-lift {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Smooth animations */
.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .responsive-padding {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>
@endpush
