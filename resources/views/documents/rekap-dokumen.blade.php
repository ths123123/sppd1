@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-folder-open mr-2 text-emerald-600 text-base"></i> Rekap Seluruh Dokumen
            </h2>
            <a href="{{ route('documents.index') }}"
               class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Dokumen</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $documents->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">SPPD Disetujui</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $documents->where('travelRequest.status', 'completed')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Menunggu Persetujuan</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $documents->where('travelRequest.status', 'in_review')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">SPPD Ditolak</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $documents->where('travelRequest.status', 'rejected')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter/Search Bar -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Dokumen</label>
                    <input type="text" placeholder="Nama file atau kode SPPD..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pengaju</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Semua Pengaju</option>
                        <!-- Dynamic options berdasarkan data pengaju -->
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="in_review">Diajukan</option>
                        <option value="completed">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="revision">Revisi</option>
                    </select>
                </div>
                <div>
                    <button class="w-full bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">
                        <i class="fas fa-list mr-2 text-base"></i>Daftar Seluruh Dokumen
                    </h3>
                    <div class="flex space-x-2">
                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-file-csv mr-2"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode SPPD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengaju</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload By</th>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ substr($doc->travelRequest->user->name ?? 'N', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $doc->travelRequest->user->name ?? 'Tidak Diketahui' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $doc->travelRequest->user->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $doc->travelRequest->destination ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $doc->uploader->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($doc->travelRequest)
                                    @php
                                        $status = $doc->travelRequest->status;
                                        $statusConfig = [
                                            'in_review' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fas fa-clock', 'text' => 'Diajukan'],
                                            'completed' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fas fa-check-circle', 'text' => 'Disetujui'],
                                            'rejected' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fas fa-times-circle', 'text' => 'Ditolak'],
                                            'revision' => ['class' => 'bg-orange-100 text-orange-800', 'icon' => 'fas fa-redo', 'text' => 'Revisi']
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
                                    @if($doc->travelRequest)
                                    <a href="{{ route('travel-requests.show', $doc->travelRequest->id) }}"
                                       class="inline-flex items-center bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 transition-colors duration-200">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Detail
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-500 mb-2">Belum Ada Dokumen</h3>
                                    <p class="text-gray-400">Belum ada dokumen SPPD yang terupload di sistem.</p>
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

        <!-- Summary Information -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-emerald-500 mt-1 mr-3"></i>
                    <div class="text-emerald-700">
                        <h4 class="font-semibold text-base mb-1">Informasi Rekap</h4>
                        <p class="text-sm">
                            Halaman ini menampilkan seluruh dokumen SPPD dari semua staff untuk keperluan monitoring dan administrasi.
                            Hanya Sekretaris, Kasubbag, dan PPK yang dapat mengakses halaman ini.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-chart-bar text-blue-500 mt-1 mr-3"></i>
                    <div class="text-blue-700">
                        <h4 class="font-semibold text-base mb-1">Total Dokumen</h4>
                        <p class="text-sm">
                            Saat ini terdapat <strong>{{ $documents->total() }} dokumen</strong> yang tersimpan dalam sistem.
                            Gunakan fitur filter untuk mempermudah pencarian dokumen tertentu.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection
