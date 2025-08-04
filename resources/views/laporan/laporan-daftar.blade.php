@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Card (sama persis approval) -->
        <div class="bg-white shadow-md rounded-2xl p-6 mb-8 border-l-4 border-blue-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-file-alt text-blue-700 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">Daftar Laporan SPPD</h2>
                    <p class="text-gray-600 text-sm mt-0.5 leading-relaxed">
                        Pilih jenis laporan SPPD yang ingin diunduh. Semua laporan tersedia dalam format Excel.
                    </p>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Laporan Rekapitulasi SPPD</div>
                    <div class="text-gray-500 text-sm">Rekap per periode, status, peserta, tujuan</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export.excel', ['jenis' => 'rekapitulasi']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Download Excel
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Laporan Dokumen SPPD</div>
                    <div class="text-gray-500 text-sm">Status verifikasi, jumlah dokumen</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export.excel', ['jenis' => 'dokumen']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Download Excel
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Laporan Anggaran SPPD</div>
                    <div class="text-gray-500 text-sm">Total, per bulan, per kegiatan</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export.excel', ['jenis' => 'anggaran']) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Download Excel
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Laporan Detail SPPD</div>
                    <div class="text-gray-500 text-sm">Daftar lengkap, filter, cetak</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export.excel', ['jenis' => 'detail']) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Download Excel
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Laporan Pengguna/Peserta</div>
                    <div class="text-gray-500 text-sm">Aktivitas, jumlah SPPD per user</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export.excel', ['jenis' => 'pengguna']) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Download Excel
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection