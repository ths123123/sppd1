@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-4">
    <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-lg font-bold mb-6 text-gray-800 flex items-center">
            <i class="fas fa-eye mr-2 text-base"></i>
            Daftar SPPD Dalam Review
        </h2>
        <!-- Bar Informasi Review -->
        <div class="glass-card rounded-xl p-6 mb-6 fade-in border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 mb-2">Informasi Menu Review SPPD</h2>
                    <p class="text-gray-700 text-base">
                        Halaman ini menampilkan daftar SPPD yang sedang dalam proses review. Gunakan menu ini untuk memantau dan menindaklanjuti pengajuan yang perlu direview.
                    </p>
                </div>
            </div>
        </div>
        <!-- Filter Form -->
        <form method="GET" id="reviewFilterForm" autocomplete="off" onsubmit="return false;" class="mb-4 flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <input type="text" name="search" placeholder="Cari tujuan, keperluan, kode SPPD..." class="form-input px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 w-full sm:w-64" />
            <select name="status" class="form-select px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 w-full sm:w-44">
                <option value="">Semua Status</option>
                <option value="in_review">Review</option>
                <option value="revision_minor">Revisi</option>
                <option value="rejected">Ditolak</option>
                <option value="completed">Selesai</option>
            </select>
        </form>
        <div class="bg-white rounded-xl shadow p-6">
            <div id="review-table-ajax">
                @include('review.partials.review_table', ['travelRequests' => $travelRequests])
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection

<script>
function fetchReviewTable(params) {
    const url = new URL("{{ route('review.ajax') }}", window.location.origin);
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            document.getElementById('review-table-ajax').innerHTML = html;
            attachReviewAjaxPagination();
        });
}
function attachReviewAjaxPagination() {
    const container = document.getElementById('review-table-ajax');
    if (!container) return;
    const links = container.querySelectorAll('.pagination a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const params = Object.fromEntries(url.searchParams.entries());
            fetchReviewTable(params);
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('reviewFilterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const status = filterForm.querySelector('select[name="status"]').value;
            const search = filterForm.querySelector('input[name="search"]').value;
            fetchReviewTable({ status, search });
            return false;
        });
    }
    const statusSelect = document.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            filterForm.requestSubmit();
        });
    }
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterForm.requestSubmit();
            }
        });
    }
    attachReviewAjaxPagination();
});
</script>
