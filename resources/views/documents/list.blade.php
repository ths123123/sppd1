@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Hapus h2 judul Dokumen SPPD -->

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Dokumen SPPD Saya -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                    <div class="flex items-center text-white">
                        <i class="fas fa-user-file text-2xl mr-4"></i>
                        <div>
                            <h3 class="text-xl font-bold">Dokumen SPPD Saya</h3>
                            <p class="text-blue-100">Lihat dokumen SPPD yang saya ajukan</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">
                        Akses dokumen-dokumen SPPD yang telah Anda ajukan, termasuk surat tugas, laporan perjalanan, dan dokumen pendukung lainnya.
                    </p>
                    <a href="{{ route('documents.my') }}"
                       class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-eye mr-2"></i>
                        Lihat Dokumen Saya
                    </a>
                </div>
            </div>

            <!-- Rekap Seluruh Dokumen (hanya untuk sekretaris dan kasubbag) -->
            @if($canViewAll)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 p-6">
                    <div class="flex items-center text-white">
                        <i class="fas fa-folder-open text-2xl mr-4"></i>
                        <div>
                            <h3 class="text-xl font-bold">Rekap Seluruh Dokumen</h3>
                            <p class="text-emerald-100">Lihat semua dokumen SPPD berdasarkan pengaju</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">
                        Akses rekap seluruh dokumen dari semua staff untuk keperluan monitoring dan administrasi.
                    </p>
                    <a href="{{ route('documents.all') }}"
                       class="inline-flex items-center bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition-colors duration-200">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Lihat Semua Dokumen
                    </a>
                </div>
            </div>
            @else
            <!-- Card untuk staff biasa yang tidak punya akses -->
            <div class="bg-gray-100 rounded-xl shadow overflow-hidden opacity-60">
                <div class="bg-gradient-to-r from-gray-400 to-gray-500 p-6">
                    <div class="flex items-center text-white">
                        <i class="fas fa-lock text-2xl mr-4"></i>
                        <div>
                            <h3 class="text-xl font-bold">Rekap Seluruh Dokumen</h3>
                            <p class="text-gray-200">Akses terbatas</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-500 mb-4">
                        Fitur ini hanya dapat diakses oleh Sekretaris, Kasubbag, dan PPK untuk keperluan monitoring dan administrasi.
                    </p>
                    <button disabled
                            class="inline-flex items-center bg-gray-400 text-white px-6 py-3 rounded-lg cursor-not-allowed">
                        <i class="fas fa-ban mr-2"></i>
                        Akses Ditolak
                    </button>
                </div>
            </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div class="text-blue-700">
                    <h3 class="text-base font-bold mb-1">Informasi</h3>
                    <p class="text-sm">
                        Dokumen SPPD meliputi surat tugas, laporan perjalanan dinas, bukti transportasi, penginapan, dan dokumen pendukung lainnya.
                        Pastikan semua dokumen telah terupload dengan lengkap untuk proses administrasi yang lancar.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection
