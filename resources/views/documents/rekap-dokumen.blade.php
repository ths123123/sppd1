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
            <form id="document-filter-form" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">Cari Dokumen</label>
                    <input type="text" id="search-input" name="search" placeholder="Nama pengaju, peserta, atau kode SPPD..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status-filter" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="in_review">Diajukan</option>
                        <option value="completed">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="revision">Revisi</option>
                    </select>
                </div>
                <div>
                    <label for="document-type-filter" class="block text-sm font-medium text-gray-700 mb-2">Jenis Dokumen</label>
                    <select id="document-type-filter" name="document_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Semua Jenis</option>
                        <option value="surat_tugas">Surat Tugas</option>
                        <option value="sppd">SPPD</option>
                        <option value="laporan">Laporan</option>
                        <option value="bukti_pengeluaran">Bukti Pengeluaran</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
            </form>
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

            <!-- Tabel Dokumen (Partial View) -->
            @include('documents.partials.documents_table', ['documents' => $documents])
            
            <!-- Script untuk toggle dokumen dan filter otomatis -->
            <script>
                function toggleDocuments(kodeSppd) {
                    const element = document.getElementById('documents-' + kodeSppd);
                    if (element.classList.contains('hidden')) {
                        element.classList.remove('hidden');
                    } else {
                        element.classList.add('hidden');
                    }
                }
                
                document.addEventListener('DOMContentLoaded', function() {
                    const filterForm = document.getElementById('document-filter-form');
                    const searchInput = document.getElementById('search-input');
                    const statusFilter = document.getElementById('status-filter');
                    const documentTypeFilter = document.getElementById('document-type-filter');
                    const tableContainer = document.querySelector('.overflow-x-auto');
                    
                    let searchTimeout;
                    
                    // Fungsi untuk menerapkan filter
                    function applyFilter() {
                        // Tampilkan indikator loading
                        tableContainer.style.opacity = '0.6';
                        
                        const formData = new FormData(filterForm);
                        const params = new URLSearchParams(formData);
                        const url = `{{ route('documents.all') }}?${params.toString()}`;
                        
                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Perbarui konten tabel
                            document.querySelector('.overflow-x-auto').outerHTML = html;
                            // Perbarui URL browser
                            window.history.pushState({}, '', url);
                            // Pasang kembali event listener untuk pagination
                            attachPaginationListeners();
                        })
                        .catch(error => console.error('Error fetching documents:', error))
                        .finally(() => {
                            // Sembunyikan indikator loading
                            tableContainer.style.opacity = '1';
                        });
                    }
                    
                    // Debounce untuk input pencarian
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(applyFilter, 500); // Delay 500ms
                    });
                    
                    // Event listener untuk filter dropdown
                    statusFilter.addEventListener('change', applyFilter);
                    documentTypeFilter.addEventListener('change', applyFilter);
                    
                    // Mencegah form submit normal
                    filterForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        applyFilter();
                    });
                    
                    // Fungsi untuk menangani pagination
                    function attachPaginationListeners() {
                        document.querySelectorAll('.pagination a').forEach(link => {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();
                                const url = this.href;
                                
                                // Tampilkan indikator loading
                                tableContainer.style.opacity = '0.6';
                                
                                fetch(url, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.text())
                                .then(html => {
                                    // Perbarui konten tabel
                                    document.querySelector('.overflow-x-auto').outerHTML = html;
                                    // Perbarui URL browser
                                    window.history.pushState({}, '', url);
                                    // Pasang kembali event listener
                                    attachPaginationListeners();
                                })
                                .catch(error => console.error('Error fetching pagination:', error))
                                .finally(() => {
                                    // Sembunyikan indikator loading
                                    tableContainer.style.opacity = '1';
                                });
                            });
                        });
                    }
                    
                    // Pasang event listener untuk pagination awal
                    attachPaginationListeners();
                });
            </script>
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
