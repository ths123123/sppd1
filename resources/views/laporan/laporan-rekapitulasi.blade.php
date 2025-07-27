@extends('layouts.app')

@section('content')
<style>
.chart-container {
    position: relative;
    height: 250px;
    width: 100%;
}
.chart-container canvas {
    max-height: 250px !important;
}
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Bar Informasi Paling Atas --}}
        <div class="glass-card rounded-xl p-5 mb-6 border-l-4 border-blue-600 bg-blue-50">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-file-alt text-blue-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Laporan Rekapitulasi SPPD</h2>
                    <p class="text-gray-700 text-base">
                        Laporan ini menampilkan rekapitulasi perjalanan dinas, status pengajuan, dan anggaran secara ringkas dan visual. Data disajikan sesuai standar pelaporan pemerintah Indonesia.
                    </p>
                </div>
            </div>
        </div>

        {{-- Hapus bar informasi paling atas, hanya tampilkan filter periode, rekap, dan update terakhir di bawah --}}
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mt-4 gap-4">
                <form id="laporanFilterForm" method="GET" class="flex flex-wrap items-center gap-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200" onsubmit="return false;">
                    <label class="text-sm font-medium text-gray-700 mr-2">Periode:</label>
                    <select name="periode" class="rounded-lg border-gray-300 text-sm mr-2">
                        <option value="1bulan" {{ request('periode') == '1bulan' ? 'selected' : '' }}>1 Bulan</option>
                        <option value="3bulan" {{ request('periode') == '3bulan' ? 'selected' : '' }}>3 Bulan</option>
                        <option value="6bulan" {{ request('periode') == '6bulan' ? 'selected' : '' }}>6 Bulan</option>
                        <option value="1tahun" {{ request('periode') == '1tahun' ? 'selected' : '' }}>1 Tahun</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Hapus form dan section Catatan Audit / Summary, Penanggung Jawab, Jabatan, Tanggal Laporan, dan tombol Terapkan Catatan --}}
        {{-- Hanya include partial rekap, tidak ada referensi $userStats, $unitStats, atau tabel status lama --}}
        <div id="laporan-rekap-ajax">
            @include('laporan.partials.rekap', get_defined_vars())
        </div>

        <!-- Financial Statistics -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                Statistik Keuangan
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Rata-rata Biaya SPPD:</p>
                    <p class="font-bold text-gray-900">Rp {{ number_format($avgBiaya) }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Biaya SPPD Tertinggi:</p>
                    <p class="font-bold text-gray-900">Rp {{ number_format($maxBiaya) }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Biaya SPPD Terendah (di atas 0):</p>
                    <p class="font-bold text-gray-900">Rp {{ number_format($minBiaya) }}</p>
                </div>
            </div>
        </div>

        <!-- Top Destinations -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>
                Top 5 Destinasi SPPD
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Destinasi</th>
                            <th class="px-4 py-2 text-right">Jumlah Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topDestinations as $destination)
                        <tr>
                            <td class="px-4 py-2">{{ $destination->tujuan }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($destination->total) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-gray-500 py-4">Tidak ada data destinasi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Trends Table -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                Tren Bulanan SPPD
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Bulan</th>
                            <th class="px-4 py-2 text-right">SPPD Dalam Review</th>
                            <th class="px-4 py-2 text-right">SPPD Disetujui</th>
                            <th class="px-4 py-2 text-right">Anggaran Disetujui (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($months as $index => $month)
                        <tr>
                            <td class="px-4 py-2">{{ $month }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($monthlyInReview[$index] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($monthlyApproved[$index] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right">Rp {{ number_format($monthlyBudget[$index] ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-500 py-4">Tidak ada data tren bulanan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quarterly Analysis Table -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-calendar-alt text-orange-600 mr-2"></i>
                Analisis Kuartalan
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Kuartal</th>
                            <th class="px-4 py-2 text-right">Total SPPD</th>
                            <th class="px-4 py-2 text-right">SPPD Disetujui</th>
                            <th class="px-4 py-2 text-right">Anggaran Disetujui (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($quarterlyData as $quarter)
                        <tr>
                            <td class="px-4 py-2">{{ $quarter['quarter'] }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($quarter['total']) }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($quarter['approved']) }}</td>
                            <td class="px-4 py-2 text-right">Rp {{ number_format($quarter['budget']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-500 py-4">Tidak ada data kuartalan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($catatan || $penanggung_jawab || $jabatan || $tanggal_laporan)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow-sm mb-6 mt-4">
                @if($catatan)
                    <div class="mb-2">
                        <span class="font-semibold text-gray-800">Catatan Audit / Summary:</span>
                        <div class="text-gray-700 whitespace-pre-line">{{ $catatan }}</div>
                    </div>
                @endif
                <div class="flex flex-col md:flex-row md:items-center md:gap-8 text-sm text-gray-700 mt-2">
                    <div><span class="font-semibold">Penanggung Jawab:</span> {{ $penanggung_jawab }}</div>
                    <div><span class="font-semibold">Jabatan:</span> {{ $jabatan }}</div>
                    <div><span class="font-semibold">Tanggal Laporan:</span> {{ \Carbon\Carbon::parse($tanggal_laporan)->format('d F Y') }}</div>
                </div>
            </div>
        @endif

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">
                    <i class="fas fa-file-check text-blue-600 mr-2 text-base"></i>
                    Statistik Dokumen
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Dokumen:</span>
                        <span class="font-bold text-blue-600">{{ number_format($totalDocuments) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Dokumen Terverifikasi:</span>
                        <span class="font-bold text-green-600">{{ number_format($totalVerifiedDocuments) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Tingkat Verifikasi:</span>
                        <span class="font-bold">
                            {{ $totalDocuments > 0 ? number_format(($totalVerifiedDocuments/$totalDocuments)*100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">
                    <i class="fas fa-users-cog text-purple-600 mr-2 text-base"></i>
                    Statistik Pengguna
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Pengguna:</span>
                        <span class="font-bold text-purple-600">{{ number_format($totalUsers) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pengguna Aktif:</span>
                        <span class="font-bold text-green-600">{{ number_format($activeUsers) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Tingkat Aktivitas:</span>
                        <span class="font-bold">
                            {{ $totalUsers > 0 ? number_format(($activeUsers/$totalUsers)*100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="w-full text-center mt-8 mb-4">
            <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }} WIB</p>
        </div>
    </div>
</div>

<script>
function fetchLaporanRekap(params) {
    const url = new URL("{{ route('laporan.ajax') }}", window.location.origin);
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            document.getElementById('laporan-rekap-ajax').innerHTML = html;
            document.getElementById('laporan-update-time').textContent = new Date().toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
        });
}

function laporanAnalitik() {
    return {
        init() {
            this.initCharts();
        },

        exportPDF() {
            window.open('{{ route("laporan.export.pdf") }}', '_blank');
        },

        exportExcel() {
            window.open('{{ route("laporan.export.excel") }}', '_blank');
        },

        initCharts() {
            // Status Distribution Pie Chart
            const statusCtx = document.getElementById('statusPieChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Disetujui', 'Menunggu', 'Dalam Review', 'Ditolak'],
                    datasets: [{
                        data: [
                            {{ $statusDistribution['completed'] ?? 0 }},
                            {{ $statusDistribution['in_review'] ?? 0 }},
                            {{ $statusDistribution['rejected'] ?? 0 }}
                        ],
                        backgroundColor: [
                            '#10B981', // Green
                            '#F59E0B', // Yellow
                            '#EF4444'  // Red
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('laporanFilterForm');
    function getAllParams() {
        const params = {};
        if (filterForm) {
            const formData = new FormData(filterForm);
            Object.assign(params, Object.fromEntries(formData.entries()));
        }
        return params;
    }
    if (filterForm) {
        const periodeSelect = filterForm.querySelector('select[name="periode"]');
        if (periodeSelect) {
            periodeSelect.addEventListener('change', function() {
                fetchLaporanRekap(getAllParams());
            });
        }
    }
});
</script>
@endsection
