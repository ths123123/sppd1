@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <!-- Bagian header dihapus sesuai permintaan user -->

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                    <div class="text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Informasi Menu SPPD Saya -->
        <div class="glass-card rounded-xl p-6 mb-8 fade-in border-l-4 border-blue-500">
                <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">Informasi Menu SPPD Saya</h2>
                    <p class="text-gray-700 text-base">
                        Halaman ini menampilkan daftar permohonan SPPD Anda. Hanya kasubbag yang dapat membuat permohonan SPPD baru. Gunakan fitur pencarian dan filter untuk memantau status atau mengelola permohonan SPPD Anda.
                    </p>
                    </div>
                </div>
            </div>

        <!-- Filter & Pencarian Modern -->
        <div class="glass-card rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center gap-3 border border-blue-100 shadow-sm">
            <form method="GET" id="filterForm" autocomplete="off" onsubmit="return false;">
                <div class="flex flex-col sm:flex-row gap-2 w-full items-center">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-gray-500"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tujuan/keperluan..." class="form-input px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 w-full sm:w-64" style="min-width:180px;">
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-gray-500"><i class="fas fa-filter"></i></span>
                        <select name="status" class="form-select px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 w-full sm:w-44">
                            <option value="">Semua Status</option>
                            <option value="in_review" {{ request('status')=='in_review' ? 'selected' : '' }}>Menunggu</option>
                            <option value="revision" {{ request('status')=='revision' ? 'selected' : '' }}>Revisi</option>
                            <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Disetujui</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- SPPD List -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Daftar SPPD Saya</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $travelRequests->total() }} permohonan total</p>
                    </div>
                </div>
            </div>

            <div id="sppd-table-ajax">
                @include('travel_requests.partials.my_requests_table', ['travelRequests' => $travelRequests])
                    </div>
            </div>

            <!-- Pagination -->
            @if($travelRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if($travelRequests->onFirstPage())
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default">
                                Previous
                            </span>
                        @else
                            <a href="{{ $travelRequests->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        @endif

                        @if($travelRequests->hasMorePages())
                            <a href="{{ $travelRequests->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        @else
                            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default">
                                Next
                            </span>
                        @endif
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ $travelRequests->firstItem() }}</span>
                                to
                                <span class="font-medium">{{ $travelRequests->lastItem() }}</span>
                                of
                                <span class="font-medium">{{ $travelRequests->total() }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            {{ $travelRequests->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush

@push('scripts')
<script>
function fetchSppdTable(params) {
    const url = new URL("{{ route('my-travel-requests.ajax') }}", window.location.origin);
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            document.getElementById('sppd-table-ajax').innerHTML = html;
            attachAjaxPagination();
        });
}

function attachAjaxPagination() {
    document.querySelectorAll('#sppd-table-ajax .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const params = Object.fromEntries(url.searchParams.entries());
            fetchSppdTable(params);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('AJAX filter submit prevented');
            const status = filterForm.querySelector('select[name="status"]').value;
            const search = filterForm.querySelector('input[name="search"]').value;
            fetchSppdTable({ status, search });
            return false;
        });
    }
    // Status filter: trigger submit form
    const statusSelect = document.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            filterForm.requestSubmit();
        });
    }
    // Search input: trigger submit form on Enter
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterForm.requestSubmit();
            }
        });
    }
    attachAjaxPagination();
});
</script>
@endpush
