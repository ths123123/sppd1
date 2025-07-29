@extends('layouts.app')

@section('content')
<style>
    .timeframe-btn.active {
        background-color: rgb(79, 70, 229);
        color: white;
    }
    
    .timeframe-btn:not(.active) {
        background-color: rgb(243, 244, 246);
        color: rgb(55, 65, 81);
    }
    
    .timeframe-btn:hover:not(.active) {
        background-color: rgb(229, 231, 235);
    }
    
    .chart-container {
        position: relative;
        width: 100%;
    }
    
    .summary-card {
        transition: all 0.3s ease;
    }
    
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .insight-section {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-left: 4px solid #3b82f6;
    }
</style>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="glass-card rounded-xl p-6 mb-6 fade-in border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                <i class="fas fa-chart-line text-blue-600 text-3xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-900 mb-2">Laporan Analitik SPPD</h2>
                <p class="text-gray-700 text-base">
                    Dashboard analitik komprehensif untuk monitoring dan evaluasi SPPD
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Charts Row 1: Monthly Trends Chart (Full Width) -->
        <div class="mb-8">
            <!-- Monthly Trends Chart -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-shadow p-6 relative border border-slate-100 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Tren Bulanan SPPD</h3>
                            <p class="text-sm text-gray-600">Data real-time dari database dengan breakdown status lengkap</p>
                        </div>
                    </div>
                </div>

                <!-- Chart Type Selector -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Chart Type:</span>
                        <select id="chartTypeSelector" class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="sppd_count">Jumlah SPPD</option>
                            <option value="budget">Anggaran</option>
                            <option value="approval_rate">Tingkat Approval</option>
                        </select>
                    </div>
                    
                    <!-- Timeframe Selector -->
                    <div class="flex items-center space-x-1">
                        <button class="timeframe-btn bg-gray-100 text-gray-700 px-3 py-1 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors" data-period="1">1M</button>
                        <button class="timeframe-btn bg-gray-100 text-gray-700 px-3 py-1 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors" data-period="3">3M</button>
                        <button class="timeframe-btn bg-indigo-600 text-white px-3 py-1 text-xs font-medium rounded-md hover:bg-indigo-700 transition-colors" data-period="12">1Y</button>
                        <button class="timeframe-btn bg-gray-100 text-gray-700 px-3 py-1 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors" data-period="all">All</button>
                    </div>
                </div>
                
                <!-- Chart Container with Enhanced Layout -->
                <div class="chart-container">
                    <!-- Main Chart -->
                    <div class="h-80 relative">
                        <canvas id="monthlyTrendsChart" class="cursor-pointer"></canvas>
                    </div>
                </div>
                
                <!-- Real-time Data Display -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 mt-4">
                    <div class="summary-card bg-blue-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-blue-600" id="total-sppd">-</div>
                        <div class="text-xs text-blue-700">Total SPPD</div>
                        <div class="text-xs text-gray-500" id="total-change">-</div>
                    </div>
                    <div class="summary-card bg-green-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-green-600" id="approved-sppd">-</div>
                        <div class="text-xs text-green-700">Disetujui</div>
                        <div class="text-xs text-gray-500" id="approved-change">-</div>
                    </div>
                    <div class="summary-card bg-red-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-red-600" id="rejected-sppd">-</div>
                        <div class="text-xs text-red-700">Ditolak</div>
                        <div class="text-xs text-gray-500" id="rejected-change">-</div>
                    </div>
                    <div class="summary-card bg-yellow-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="review-sppd">-</div>
                        <div class="text-xs text-yellow-700">Dalam Review</div>
                        <div class="text-xs text-gray-500" id="review-change">-</div>
                    </div>
                </div>
                
                <!-- Enhanced Insight Section -->
                <div class="insight-section rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lightbulb text-indigo-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-indigo-900 mb-1">Analisis Tren Bulanan</div>
                            <div class="text-sm text-indigo-800" id="insight-monthly">
                                (Analisis tren bulanan akan muncul di sini)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Budget Trends Chart (Full Width) -->
        <div class="mb-8">
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

        <!-- Charts Row 3: Distribusi Status & Analisis Departemen -->
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