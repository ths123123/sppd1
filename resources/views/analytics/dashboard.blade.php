@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 pt-4">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-md md:sticky md:top-16 z-20">
        <div class="max-w-7xl mx-auto px-3 py-2 md:px-6 md:py-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-2 md:gap-0">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-8 h-8 md:w-12 md:h-12 rounded-full bg-indigo-100">
                    <i class="fas fa-chart-line text-indigo-600 text-lg md:text-2xl"></i>
                </span>
                <div>
                    <h1 class="text-lg md:text-2xl font-bold text-gray-900">Laporan Analitik SPPD</h1>
                    <p class="text-xs md:text-base text-gray-600 mt-1">Dashboard analitik komprehensif untuk monitoring dan evaluasi SPPD</p>
                </div>
                </div>
            <div class="flex items-center space-x-2 md:space-x-4 w-full md:w-auto mt-2 md:mt-0">
                <!-- Period Filter -->
                <form method="GET" class="flex items-center space-x-2 w-full md:w-auto">
                    <select name="period" class="rounded-lg border-gray-300 text-xs md:text-sm shadow focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition w-full md:w-auto">
                        <option value="1" {{ $period == '1' ? 'selected' : '' }}>1 Bulan Terakhir</option>
                        <option value="3" {{ $period == '3' ? 'selected' : '' }}>3 Bulan Terakhir</option>
                        <option value="6" {{ $period == '6' ? 'selected' : '' }}>6 Bulan Terakhir</option>
                        <option value="12" {{ $period == '12' ? 'selected' : '' }}>12 Bulan Terakhir</option>
                        <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Sepanjang Waktu</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Trends Chart -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-indigo-400"></i> Tren Bulanan SPPD
                </h3>
                <div class="h-80 relative">
                    <canvas id="monthlyTrendsChart" class="cursor-pointer"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600 bg-indigo-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-monthly">(Analisis tren bulanan akan muncul di sini)</span>
                </div>
            </div>
            <!-- Budget Trends Chart -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-coins text-green-400"></i> Tren Anggaran Bulanan
                </h3>
                <div class="h-80 relative">
                    <canvas id="budgetTrendsChart" class="cursor-pointer"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600 bg-green-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-budget">(Analisis tren anggaran akan muncul di sini)</span>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Distribusi Status & Analisis Departemen -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Status Distribution -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-pink-400"></i> Distribusi Status SPPD
                </h3>
                <div class="h-80 relative">
                    <canvas id="statusChart" class="cursor-pointer"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600 bg-pink-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-status">(Analisis distribusi status akan muncul di sini)</span>
                </div>
            </div>
            <!-- Department Analysis -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-building text-blue-400"></i> Analisis per Departemen
                </h3>
                <div class="h-80 relative">
                    <canvas id="departmentAnalysisChart" class="cursor-pointer"></canvas>
                        </div>
                <div class="mt-4 text-sm text-gray-600 bg-blue-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-department">(Analisis departemen akan muncul di sini)</span>
                </div>
            </div>
        </div>

        <!-- Additional Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Approval Performance -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-user-check text-yellow-400"></i> Performa Approval
                </h3>
                <div class="h-80 relative">
                    <canvas id="approvalPerformanceChart" class="cursor-pointer"></canvas>
                        </div>
                <div class="mt-4 text-sm text-gray-600 bg-yellow-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-approval">(Analisis approval akan muncul di sini)</span>
                </div>
            </div>
            <!-- Top Destinations -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-purple-400"></i> Destinasi Populer
                </h3>
                <div class="h-80 relative">
                    <canvas id="topDestinationsChart" class="cursor-pointer"></canvas>
                    </div>
                <div class="mt-4 text-sm text-gray-600 bg-purple-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-destination">(Analisis destinasi akan muncul di sini)</span>
                </div>
            </div>
            <!-- Budget Utilization -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-wallet text-green-400"></i> Utilisasi Anggaran
                </h3>
                <div class="h-80 relative">
                    <canvas id="budgetUtilizationChart" class="cursor-pointer"></canvas>
                    </div>
                <div class="mt-4 text-sm text-gray-600 bg-green-50 rounded-lg px-4 py-2">
                    <span class="font-semibold">Insight:</span> <span id="insight-utilization">(Analisis utilisasi anggaran akan muncul di sini)</span>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: <span id="last-updated">-</span></p>
    </div>

<!-- Modal Placeholder for Detail (akan diisi JS) -->
<div id="analytics-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 relative">
        <button id="close-analytics-modal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <div id="analytics-modal-content">
            <!-- Konten detail akan diisi via JS -->
        </div>
    </div>
    </div>
</div>

<!-- Chart.js & Analytics JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/pages/analytics.js')

@endsection