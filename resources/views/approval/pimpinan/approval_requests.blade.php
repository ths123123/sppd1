@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Bar Informasi Approval -->
        <div class="bg-white shadow-md rounded-2xl p-6 mb-8 border-l-4 border-red-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-info-circle text-red-700 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">Informasi Menu Persetujuan SPPD</h2>
                    <p class="text-gray-600 text-sm mt-0.5 leading-relaxed">
                        Halaman ini menampilkan daftar permohonan SPPD yang perlu persetujuan sesuai peran Anda. Gunakan tombol aksi untuk menyetujui, menolak, atau meminta revisi pengajuan SPPD.
                    </p>
                </div>
            </div>
        </div>

        <!-- Session Messages -->
        @if (session('error'))
            <div class="mb-4 glass-card rounded-md p-3 border-l-4 border-red-500 fade-in">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500 text-base"></i>
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if (isset($errors) && $errors->has('rejection_reason'))
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    showRejectModal('{{ old('reject_id') ?? '' }}', '{{ old('reject_kode') ?? '' }}');
                });
            </script>
        @endif

        <!-- Enhanced Stats Grid -->
        {{-- Statistik ringkasan dihapus sesuai permintaan user --}}

        <!-- Recent Activities -->
        @if(isset($recentActivities) && $recentActivities->count() > 0)
        <div class="mb-6">
            <div class="glass-card p-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Aktivitas Terakhir Saya</h3>
                <div class="space-y-2">
                    @foreach($recentActivities as $activity)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 rounded-full
                                @if($activity->status === 'completed') bg-green-500
                                @elseif($activity->status === 'rejected') bg-red-500
                                @else bg-yellow-500 @endif">
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $activity->travelRequest->kode_sppd }} - {{ $activity->travelRequest->user->name }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ ucfirst($activity->status) }} • {{ $activity->created_at->format('d M H:i') }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                            @if($activity->status === 'completed') bg-green-100 text-green-800
                            @elseif($activity->status === 'rejected') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($activity->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- SPPD Requests Table -->
        <div x-data="approvalTable" x-init="initTable()" class="bg-white shadow-md rounded-2xl overflow-hidden">
            <template x-if="notification">
                <div :class="notificationType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="rounded-lg px-4 py-3 mb-4 font-semibold">
                    <span x-text="notification"></span>
                </div>
            </template>
            <form id="approvalFilterForm" @submit.prevent="filterTable" class="mb-4 flex flex-col sm:flex-row gap-3 items-start sm:items-center px-6 pt-6">
                <input type="text" id="approval-search-input" name="search" placeholder="Cari tujuan, keperluan, kode SPPD..." class="form-input px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 w-full sm:w-64" />
                <select id="approval-urgency-filter" name="urgency" class="form-select px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 w-full sm:w-44">
                    <option value="">Semua Urgensi</option>
                    <option value="urgent">Mendesak</option>
                    <option value="normal">Normal</option>
                </select>
                <input type="hidden" id="approval-current-page" name="page" value="1" />
            </form>
            <div id="approval-table-ajax">
                @include('approval.pimpinan.partials.approval_requests_table', ['requests' => $requests])
            </div>
            <!-- Move the universal confirmation modal INSIDE Alpine.js scope -->
            <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display: none;">
                <div class="bg-white rounded-xl shadow-xl p-8 max-w-md w-full text-center relative transform transition-all" @click.away="showConfirmModal = false">
                    <div class="mb-6">
                        <template x-if="confirmType === 'approve'">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="confirmType === 'reject'">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="confirmType === 'revisi'">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4">
                                <svg class="h-10 w-10 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                        </template>
                        <h2 class="text-xl font-bold mb-3" x-text="confirmTitle"></h2>
                        <p class="text-gray-600" x-text="confirmMessage"></p>
                    </div>
                    <div class="flex justify-center gap-4 mt-6">
                        <button @click="showConfirmModal=false" :disabled="isLoading" 
                                class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            Batal
                        </button>
                        <button @click="console.log('[approvalTable] confirmAction called'); confirmAction()" :disabled="isLoading" 
                                :class="confirmType==='approve' ? 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500' : confirmType==='reject' ? 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500' : 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500'" 
                                class="px-5 py-2.5 rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-sm">
                            <span x-text="confirmButtonText"></span>
                        </button>
                    </div>
                    <template x-if="isLoading">
                        <div class="mt-5 flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Memproses...</span>
                        </div>
                    </template>
                </div>
            </div>
            <!-- Modernized Modal for Rejection (Tolak) -->
            <div id="rejectModal" 
                x-data="{ show: false, isLoading: false }" 
                x-show="show" 
                @approval:showreject.window="show = true" 
                @approval:hidereject.window="show = false" 
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
                style="display: none;"
                x-transition:enter="transition ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="transition ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0">
                <div class="relative top-20 mx-auto p-8 max-w-md w-full bg-white rounded-xl shadow-xl transform transition-all" 
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 transform scale-95" 
                    x-transition:enter-end="opacity-100 transform scale-100" 
                    x-transition:leave="transition ease-in duration-200" 
                    x-transition:leave-start="opacity-100 transform scale-100" 
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <button @click="show = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <form id="rejectForm" onsubmit="event.preventDefault(); window.dispatchEvent(new CustomEvent('approval:submitReject'))">
                        @csrf
                        <!-- Tambahkan kembali input hidden untuk id dan kode -->
                        <input type="hidden" name="reject_id" value="{{ old('reject_id') }}">
                        <input type="hidden" name="reject_kode" value="{{ old('reject_kode') }}">
                        <div class="mb-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Tolak SPPD</h3>
                            <p class="text-gray-600 mb-4">SPPD: <span id="rejectSppdCode" class="font-medium"></span></p>
                            <div class="mt-6">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2 text-left">Alasan Penolakan</label>
                                <textarea id="rejection_reason" name="rejection_reason"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @if(isset($errors) && $errors->has('rejection_reason')) border-red-500 @endif"
                                        rows="3"
                                        placeholder="Jelaskan alasan penolakan SPPD ini..."
                                        required>{{ old('rejection_reason') }}</textarea>
                                @if(isset($errors) && $errors->has('rejection_reason'))
                                    <div class="text-red-600 text-xs mt-1">{{ $errors->first('rejection_reason') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-center gap-4 mt-6">
                            <button type="button" @click="show = false"
                                    class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm">
                                <span class="flex items-center">
                                    <template x-if="isLoading">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    Tolak SPPD
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modernized Modal for Revision (Revisi) -->
            <div id="revisionModal" 
                x-data="{ show: false, isLoading: false }" 
                x-show="show" 
                @approval:showrevision.window="show = true" 
                @approval:hiderevision.window="show = false" 
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
                style="display: none;"
                x-transition:enter="transition ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="transition ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0">
                <div class="relative top-20 mx-auto p-8 max-w-md w-full bg-white rounded-xl shadow-xl transform transition-all" 
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 transform scale-95" 
                    x-transition:enter-end="opacity-100 transform scale-100" 
                    x-transition:leave="transition ease-in duration-200" 
                    x-transition:leave-start="opacity-100 transform scale-100" 
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <button @click="show = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <form id="revisionForm" onsubmit="event.preventDefault(); window.dispatchEvent(new CustomEvent('approval:submitRevision'))">
                        @csrf
                        <div class="mb-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4">
                                <svg class="h-10 w-10 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Minta Revisi SPPD</h3>
                            <p class="text-gray-600 mb-4">SPPD: <span id="revisionSppdCode" class="font-medium"></span></p>
                            <div class="mt-6">
                                <label for="revision_reason" class="block text-sm font-medium text-gray-700 mb-2 text-left">Alasan Revisi</label>
                                <textarea id="revision_reason" name="revision_reason"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                                        rows="3"
                                        placeholder="Jelaskan perbaikan yang diperlukan..."
                                        required></textarea>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Target Revisi</label>
                                <div class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                                    Kembali ke Kasubbag
                                </div>
                                <input type="hidden" name="target" value="kasubbag">
                            </div>
                        </div>
                        <div class="flex justify-center gap-4 mt-6">
                            <button type="button" @click="show = false"
                                    class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-sm">
                                <span class="flex items-center">
                                    <template x-if="isLoading">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    Minta Revisi
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Update Timestamp -->
    <div class="w-full flex flex-col items-center justify-center pb-8 pt-8 text-center">
        <div class="text-gray-500 text-sm">
            Update terakhir<br><span class="font-semibold">{{ now()->format('d M Y H:i') }} WIB</span>
        </div>
    </div>
</div>

<script>
function showRejectModal(id, code) {
    console.log('[showRejectModal] Called with id:', id, 'code:', code);
    
    // Set nilai pada form
    document.getElementById('rejectSppdCode').textContent = code || '';
    document.querySelector('#rejectForm input[name="reject_id"]').value = id || '';
    document.querySelector('#rejectForm input[name="reject_kode"]').value = code || '';
    
    // Set action form
    document.getElementById('rejectForm').action = `/approval/pimpinan/${id}/reject`;
    
    // Reset loading state
    const modal = document.getElementById('rejectModal');
    if (modal && modal._x_dataStack && modal._x_dataStack[0]) {
        modal._x_dataStack[0].isLoading = false;
    }
    
    // Tampilkan modal dengan Alpine.js events
    console.log('[showRejectModal] Dispatching approval:showreject event');
    window.dispatchEvent(new CustomEvent('approval:showreject'));
    
    // Fallback jika event tidak bekerja
    setTimeout(() => {
        if (modal && modal.classList.contains('hidden')) {
            console.log('[showRejectModal] Fallback: removing hidden class');
            modal.classList.remove('hidden');
            // Juga set show = true di Alpine data jika tersedia
            if (modal._x_dataStack && modal._x_dataStack[0]) {
                modal._x_dataStack[0].show = true;
            }
        }
    }, 100);
}

function closeRejectModal() {
    console.log('[closeRejectModal] Called');
    
    // Sembunyikan modal dengan Alpine.js events
    console.log('[closeRejectModal] Dispatching approval:hidereject event');
    window.dispatchEvent(new CustomEvent('approval:hidereject'));
    
    // Fallback jika event tidak bekerja
    setTimeout(() => {
        const modal = document.getElementById('rejectModal');
        if (modal && !modal.classList.contains('hidden')) {
            console.log('[closeRejectModal] Fallback: adding hidden class');
            modal.classList.add('hidden');
            // Juga set show = false di Alpine data jika tersedia
            if (modal._x_dataStack && modal._x_dataStack[0]) {
                modal._x_dataStack[0].show = false;
            }
        }
    }, 100);
}

function showRevisionModal(id, code) {
    console.log('[showRevisionModal] Called with id:', id, 'code:', code);
    
    // Set nilai pada form
    if (document.getElementById('revisionSppdCode')) {
        document.getElementById('revisionSppdCode').textContent = code || '';
    }
    
    // Set action form
    document.getElementById('revisionForm').action = `/approval/pimpinan/${id}/revision`;
    
    // Set default value untuk target
    const targetSelect = document.getElementById('revision_target');
    if (targetSelect && targetSelect.options.length > 0) {
        targetSelect.value = 'kasubbag';
    }
    
    // Reset loading state
    const modal = document.getElementById('revisionModal');
    if (modal && modal._x_dataStack && modal._x_dataStack[0]) {
        modal._x_dataStack[0].isLoading = false;
    }
    
    // Tampilkan modal dengan Alpine.js events
    console.log('[showRevisionModal] Dispatching approval:showrevision event');
    window.dispatchEvent(new CustomEvent('approval:showrevision'));
    
    // Fallback jika event tidak bekerja
    setTimeout(() => {
        if (modal && modal.classList.contains('hidden')) {
            console.log('[showRevisionModal] Fallback: removing hidden class');
            modal.classList.remove('hidden');
            // Juga set show = true di Alpine data jika tersedia
            if (modal._x_dataStack && modal._x_dataStack[0]) {
                modal._x_dataStack[0].show = true;
            }
        }
    }, 100);
}

function closeRevisionModal() {
    console.log('[closeRevisionModal] Called');
    
    // Sembunyikan modal dengan Alpine.js events
    console.log('[closeRevisionModal] Dispatching approval:hiderevision event');
    window.dispatchEvent(new CustomEvent('approval:hiderevision'));
    
    // Fallback jika event tidak bekerja
    setTimeout(() => {
        const modal = document.getElementById('revisionModal');
        if (modal && !modal.classList.contains('hidden')) {
            console.log('[closeRevisionModal] Fallback: adding hidden class');
            modal.classList.add('hidden');
            // Juga set show = false di Alpine data jika tersedia
            if (modal._x_dataStack && modal._x_dataStack[0]) {
                modal._x_dataStack[0].show = false;
            }
        }
    }, 100);
}

// Tambahkan fungsi resetFilterForm
function resetFilterForm() {
    const form = document.getElementById('approvalFilterForm');
    if (form) {
        form.reset();
        document.getElementById('approval-search-input').value = '';
        document.getElementById('approval-urgency-filter').value = '';
        document.getElementById('approval-current-page').value = 1;
    }
}

// Tambahkan fungsi polling reload tabel approval
function pollReloadApprovalTable(attempt = 1, maxAttempts = 2) {
    const self = Alpine.store && Alpine.store('approvalTable') ? Alpine.store('approvalTable') : null;
    if (!self) return;
    self.filterTable(1);
    setTimeout(() => {
        const rowCount = document.querySelectorAll('#approval-table-ajax tbody tr').length;
        console.log(`[approvalTable] Polling attempt ${attempt}, rowCount: ${rowCount}`);
        if (rowCount === 0 && attempt < maxAttempts) {
            setTimeout(() => pollReloadApprovalTable(attempt + 1, maxAttempts), 500);
        }
    }, 400);
}

// Fungsi untuk mengatur loading state pada modal
function setModalLoading(modalId, isLoading) {
    const modal = document.getElementById(modalId);
    if (modal && modal._x_dataStack && modal._x_dataStack[0]) {
        console.log(`[setModalLoading] Setting ${modalId} loading state to:`, isLoading);
        modal._x_dataStack[0].isLoading = isLoading;
    }
}
</script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('approvalTable', () => ({
        notification: '',
        notificationType: '',
        isLoading: false,
        showConfirmModal: false,
        confirmType: '',
        confirmTitle: '',
        confirmMessage: '',
        confirmButtonText: '',
        confirmAction: () => {},
        initTable() {
            this.rebindEvents();
            window.addEventListener('approval:submitReject', () => this.submitRejectForm());
            window.addEventListener('approval:submitRevision', () => this.submitRevisionForm());
        },
        showNotification(msg, type = 'success') {
            this.notification = msg;
            this.notificationType = type;
            setTimeout(() => { this.notification = ''; }, 3000);
        },
        filterTable(page = null) {
            // FIX: Ignore event object
            if (page && typeof page === 'object' && page.preventDefault) {
                console.log('[approvalTable] Page parameter is an event object, setting to null');
                page = null;
            }
            
            const form = document.getElementById('approvalFilterForm');
            if (!form) {
                console.error('[approvalTable] Form not found!');
                return;
            }
            
            if (page) {
                document.getElementById('approval-current-page').value = page;
            }
            
            const currentPage = parseInt(document.getElementById('approval-current-page').value) || 1;
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            
            console.log('[approvalTable] filterTable params:', params);
            console.log('[approvalTable] Current page:', currentPage);
            console.log('[approvalTable] User role:', '{{ Auth::user()->role }}');
            
            // Debug info sebelum fetch
            console.log('[approvalTable] Fetching from URL:', '{{ route("approval.pimpinan.ajax") }}?' + params);
            
            fetch('{{ route("approval.pimpinan.ajax") }}?' + params, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => {
                console.log('[approvalTable] AJAX status:', res.status);
                if (!res.ok) {
                    throw new Error('Network response was not ok: ' + res.status);
                }
                return res.text();
            })
            .then(html => {
                if (!html || html.trim() === '') {
                    console.warn('[approvalTable] Empty response received');
                    return;
                }
                
                console.log('[approvalTable] AJAX response length:', html.length);
                console.log('[approvalTable] AJAX response preview:', html.substring(0, 100) + '...');
                
                try {
                    document.getElementById('approval-table-ajax').innerHTML = html;
                    this.rebindEvents();
                    
                    // Re-attach pagination click events
                    document.querySelectorAll('#approval-table-ajax .pagination a').forEach(link => {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            const url = new URL(link.href);
                            const page = url.searchParams.get('page') || 1;
                            this.filterTable(page);
                        });
                    });
                    
                    // Jika tabel kosong dan page > 1, fallback ke page sebelumnya
                    const rowCount = document.querySelectorAll('#approval-table-ajax tbody tr').length;
                    console.log('[approvalTable] Row count after update:', rowCount);
                    
                    if (rowCount === 0 && currentPage > 1) {
                        console.log('[approvalTable] No rows found on page ' + currentPage + ', falling back to previous page');
                        this.filterTable(currentPage - 1);
                    }
                    
                    // Debug: tampilkan warning jika data kosong
                    if (rowCount === 0) {
                        console.warn('[approvalTable] Tidak ada data SPPD yang tampil setelah reload.');
                    }
                } catch (err) {
                    console.error('[approvalTable] Error updating table HTML:', err);
                    this.showNotification('Terjadi kesalahan saat memperbarui tabel.', 'error');
                }
            })
            .catch((err) => {
                console.error('[approvalTable] AJAX error:', err);
                this.showNotification('Gagal memuat data tabel approval.', 'error');
            });
        },
        rebindEvents() {
            console.log('[approvalTable] rebindEvents called');
            // Approve
            document.querySelectorAll('[data-approve-btn]').forEach(btn => {
                btn.onclick = () => {
                    const id = btn.getAttribute('data-id');
                    const url = btn.getAttribute('data-url');
                    console.log(`[approvalTable] Approve button clicked: id=${id}, url=${url}`);
                    this.confirmApprove(id, url);
                };
            });
            // Reject
            document.querySelectorAll('[data-reject-btn]').forEach(btn => {
                btn.onclick = () => {
                    const id = btn.getAttribute('data-id');
                    const url = btn.getAttribute('data-url');
                    console.log(`[approvalTable] Reject button clicked: id=${id}, url=${url}`);
                    this.confirmReject(id, url);
                };
            });
            // Revision
            document.querySelectorAll('[data-revision-btn]').forEach(btn => {
                btn.onclick = () => {
                    const id = btn.getAttribute('data-id');
                    const url = btn.getAttribute('data-url');
                    console.log(`[approvalTable] Revision button clicked: id=${id}, url=${url}`);
                    this.confirmRevision(id, url);
                };
            });
        },
        confirmApprove(id, url) {
            this.confirmType = 'approve';
            this.confirmTitle = 'Konfirmasi Persetujuan';
            this.confirmMessage = 'Apakah Anda yakin ingin menyetujui SPPD ini?';
            this.confirmButtonText = 'Setujui';
            this.confirmAction = () => {
                this.isLoading = true;
                console.log(`[approvalTable] Sending APPROVE AJAX: url=${url}, id=${id}`);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    this.isLoading = false;
                    this.showConfirmModal = false;
                    this.filterTable(1); // Langsung reload tabel approval
                    const msg = data.message || (data.success ? 'SPPD berhasil disetujui.' : 'Gagal menyetujui SPPD.');
                    this.showNotification(msg, data.success ? 'success' : 'error');
                    console.log('[approvalTable] APPROVE AJAX response:', data);
                })
                .catch((err) => {
                    this.isLoading = false;
                    this.showConfirmModal = false;
                    this.showNotification('Terjadi kesalahan jaringan.', 'error');
                    console.error('[approvalTable] APPROVE AJAX error:', err);
                });
            };
            this.showConfirmModal = true;
        },
        confirmReject(id, url) {
            // Ambil kode SPPD dari kolom tujuan (td kedua)
            let code = '';
            const row = document.querySelector(`[data-row-id='${id}']`);
            if (row) {
                code = row.querySelector('td:nth-child(2) .text-sm.text-gray-900')?.textContent?.trim() || '';
            }
            showRejectModal(id, code);
        },
        _doRejectAjax() {
            // Set loading state pada modal
            setModalLoading('rejectModal', true);
            
            const form = document.getElementById('rejectForm');
            const id = form.querySelector('input[name="reject_id"]').value;
            const url = `/approval/pimpinan/${id}/reject`;
            const reason = form.querySelector('textarea[name="rejection_reason"]').value;
            console.log(`[approvalTable] Sending REJECT AJAX: url=${url}, id=${id}, reason=${reason}`);
            
            // Create FormData for proper form submission
            const formData = new FormData();
            formData.append('rejection_reason', reason);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(async res => {
                let data = {};
                let isJson = false;
                try {
                    data = await res.clone().json();
                    isJson = true;
                } catch (e) {
                    // Not JSON
                }
                
                // Reset loading state
                setModalLoading('rejectModal', false);
                
                if (res.ok && isJson && data.success) {
                    resetFilterForm();
                    this.filterTable(1); // Langsung reload tabel approval
                    const msg = data.message || 'SPPD berhasil ditolak.';
                    this.showNotification(msg, 'success');
                    closeRejectModal();
                    console.log('[approvalTable] REJECT AJAX response:', data);
                } else if (res.status === 422 && isJson && data.errors) {
                    const msg = data.errors.rejection_reason ? data.errors.rejection_reason.join(' ') : 'Validasi gagal.';
                    this.showNotification(msg, 'error');
                } else if (!isJson) {
                    this.showNotification('Terjadi kesalahan server atau jaringan. Coba lagi.', 'error');
                } else {
                    const msg = data.message || 'Gagal menolak SPPD.';
                    this.showNotification(msg, 'error');
                }
            })
            .catch((err) => {
                // Reset loading state
                setModalLoading('rejectModal', false);
                
                this.showNotification('Terjadi kesalahan jaringan.', 'error');
                closeRejectModal();
                console.error('[approvalTable] REJECT AJAX error:', err);
            });
        },
        submitRejectForm() {
            // Untuk Tolak, langsung proses AJAX tanpa konfirmasi universal
            this._doRejectAjax();
        },
        confirmRevision(id, url) {
            let code = '';
            const row = document.querySelector(`[data-row-id='${id}']`);
            if (row) {
                code = row.querySelector('td:nth-child(2) .text-sm.text-gray-900')?.textContent?.trim() || '';
            }
            showRevisionModal(id, code);
        },
        _doRevisionAjax() {
            // Set loading state pada modal
            setModalLoading('revisionModal', true);
            
            const form = document.getElementById('revisionForm');
            const id = form.action.split('/').slice(-2, -1)[0];
            const url = `/approval/pimpinan/${id}/revision`;
            const reason = form.querySelector('textarea[name="revision_reason"]').value;
            const target = form.querySelector('input[name="target"]').value;
            console.log(`[approvalTable] Sending REVISION AJAX: url=${url}, id=${id}, reason=${reason}, target=${target}`);
            
            // Create FormData for proper form submission
            const formData = new FormData();
            formData.append('revision_reason', reason);
            formData.append('target', target);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(async res => {
                let data = {};
                let isJson = false;
                try {
                    data = await res.clone().json();
                    isJson = true;
                } catch (e) {
                    // Not JSON
                }
                
                // Reset loading state
                setModalLoading('revisionModal', false);
                
                if (res.ok && isJson && data.success) {
                    resetFilterForm();
                    this.filterTable(1); // Langsung reload tabel approval
                    const msg = data.message || 'SPPD berhasil direvisi.';
                    this.showNotification(msg, 'success');
                    closeRevisionModal();
                    console.log('[approvalTable] REVISION AJAX response:', data);
                } else if (res.status === 422 && isJson && data.errors) {
                    const msg = data.errors.revision_reason ? data.errors.revision_reason.join(' ') : 'Validasi gagal.';
                    this.showNotification(msg, 'error');
                } else if (!isJson) {
                    this.showNotification('Terjadi kesalahan server atau jaringan. Coba lagi.', 'error');
                } else {
                    const msg = data.message || 'Gagal merevisi SPPD.';
                    this.showNotification(msg, 'error');
                }
            })
            .catch((err) => {
                // Reset loading state
                setModalLoading('revisionModal', false);
                
                this.showNotification('Terjadi kesalahan jaringan.', 'error');
                closeRevisionModal();
                console.error('[approvalTable] REVISION AJAX error:', err);
            });
        },
        submitRevisionForm() {
            // Untuk Revisi, langsung proses AJAX tanpa konfirmasi universal
            this._doRevisionAjax();
        },
    }));
});
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('approval-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(window._approvalFilterTimeout);
            window._approvalFilterTimeout = setTimeout(() => {
                document.getElementById('approvalFilterForm').dispatchEvent(new Event('submit'));
            }, 400);
        });
    }
    var urgencySelect = document.getElementById('approval-urgency-filter');
    if (urgencySelect) {
        urgencySelect.addEventListener('change', function() {
            document.getElementById('approvalFilterForm').dispatchEvent(new Event('submit'));
        });
    }
    // On page load, rebind pagination events
    document.querySelectorAll('#approval-table-ajax .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page') || 1;
            if (window.Alpine && Alpine.store && Alpine.store('approvalTable')) {
                Alpine.store('approvalTable').filterTable(page);
            }
        });
    });
});
</script>
@endsection

