@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 font-sans">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="bg-white shadow-md rounded-2xl p-6 mb-8 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Perjalanan Dinas (SPPD)</h1>
                    <p class="text-gray-600 mt-1">Manajemen dan monitoring semua pengajuan SPPD.</p>
                </div>
                <div class="hidden md:flex items-center">
                    <i class="fas fa-file-alt text-indigo-400 text-4xl"></i>
                </div>
            </div>
        </div>

        <!-- Filtering UI -->
        <div class="bg-white shadow-md rounded-2xl p-6 mb-8">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label for="search-input" class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <div class="relative">
                        <input type="text" id="search-input" name="search" placeholder="Cari pemohon, tujuan..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 transition">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status-filter" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 transition">
                        <option value="">Semua Status</option>
                        <option value="in_review">Review</option>
                        <option value="revision">Revisi</option>
                        <option value="rejected">Ditolak</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label for="year-filter" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select id="year-filter" name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 transition">
                        <option value="">Semua Tahun</option>
                        @for ($year = 2025; $year <= 2030; $year++)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>

        <!-- SPPD Table Container -->
        <div id="sppd-table-container" class="bg-white shadow-md rounded-2xl overflow-hidden">
            @include('travel_requests.partials.sppd_table', ['travelRequests' => $travelRequests])
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableContainer = document.getElementById('sppd-table-container');
    const filterForm = document.getElementById('filter-form');
    const searchInput = document.getElementById('search-input');
    let searchTimeout;

    function fetchSppd(url) {
        // Show loading indicator
        tableContainer.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            window.history.pushState({}, '', url);
        })
        .catch(error => console.error('Error fetching SPPD data:', error))
        .finally(() => {
            // Hide loading indicator
            tableContainer.style.opacity = '1';
        });
    }

    // Debounced search
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.dispatchEvent(new Event('change'));
        }, 500); // 500ms delay
    });

    // Handle form changes for all filters
    filterForm.addEventListener('change', () => {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const url = `{{ route('travel-requests.indexAll') }}?${params.toString()}`;
        fetchSppd(url);
    });

    // Handle pagination clicks
    document.addEventListener('click', function(event) {
        if (event.target.closest('.pagination a')) {
            event.preventDefault();
            const url = event.target.closest('.pagination a').href;
            fetchSppd(url);
        }
    });
});
</script>
@endpush
