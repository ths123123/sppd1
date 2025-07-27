@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <!-- HAPUS JUDUL DAN ICON DOKUMEN SPPD SAYA -->
            <span></span>
            <a href="{{ route('documents.index') }}"
               class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>

        <!-- Filter/Search Bar -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-64">
                    <input type="text" placeholder="Cari berdasarkan nama file atau kode SPPD..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2"></i>Daftar Dokumen
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode SPPD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($documents as $doc)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-900">{{ $doc->original_filename }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-blue-600 font-semibold">
                                    @if($doc->travelRequest && $doc->travelRequest->status === 'completed')
                                        {{ $doc->travelRequest->kode_sppd }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $doc->travelRequest->destination ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($doc->travelRequest)
                                    @php
                                        $status = $doc->travelRequest->status;
                                        $statusConfig = [
                                            'in_review' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fas fa-clock', 'text' => 'Dalam Review'],
                                            'revision' => ['class' => 'bg-orange-100 text-orange-800', 'icon' => 'fas fa-redo', 'text' => 'Revisi'],
                                            'rejected' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fas fa-times-circle', 'text' => 'Ditolak'],
                                            'completed' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fas fa-check-circle', 'text' => 'Disetujui'],
                                        ];
                                        $config = $statusConfig[$status] ?? $statusConfig['in_review'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                        <i class="{{ $config['icon'] }} mr-1"></i>
                                        {{ $config['text'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-question mr-1"></i>
                                        Tidak Diketahui
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('documents.download', $doc->id) }}"
                                       target="_blank"
                                       class="inline-flex items-center bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition-colors duration-200">
                                        <i class="fas fa-eye mr-1"></i>
                                        Lihat
                                    </a>
                                    <a href="{{ route('documents.download', $doc->id) }}"
                                       class="inline-flex items-center bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition-colors duration-200">
                                        <i class="fas fa-download mr-1"></i>
                                        Download
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-500 mb-2">Belum Ada Dokumen</h3>
                                    <p class="text-gray-400">Anda belum memiliki dokumen SPPD yang terupload.</p>
                                    <p class="mt-4 text-sm text-gray-500">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Gunakan menu "Buat SPPD" di navbar untuk membuat permohonan baru
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($documents->hasPages())
            <div class="bg-white px-6 py-3 border-t border-gray-200">
                {{ $documents->links() }}
            </div>
            @endif
        </div>

        <!-- Summary Card -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div class="text-blue-700">
                    <h4 class="font-semibold mb-1">Informasi Dokumen SPPD</h4>
                    <p class="text-sm">
                        Total dokumen Anda: <strong>{{ $documents->total() }} dokumen</strong>.
                        Pastikan semua dokumen pendukung SPPD telah terupload untuk kelancaran proses administrasi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
