@extends('layouts.app')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 py-4 sm:py-6 md:py-8">
        <!-- Header -->
        <div class="mb-8 fade-in">
            <div class="flex items-center">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">Dashboard SPPD</h2>
                    <p class="text-gray-600">Kelola dan monitor semua permohonan perjalanan dinas</p>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 fade-in">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="glass-card rounded-xl p-6 hover-lift fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Menunggu Review</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['menunggu_review'] }}</p>
                        <p class="text-xs text-gray-500">Perlu persetujuan Anda</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-xl p-6 hover-lift fade-in" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Disetujui</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['disetujui'] }}</p>
                        <p class="text-xs text-gray-500">SPPD approved</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-xl p-6 hover-lift fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Semua Menunggu</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['semua_menunggu'] }}</p>
                        <p class="text-xs text-gray-500">Total dalam proses</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-xl p-6 hover-lift fade-in" style="animation-delay: 0.3s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total SPPD</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500">Semua permohonan</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-gray-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengajuan Menunggu Persetujuan -->
        @if($pendingApprovals->count() > 0)
        <div class="glass-card rounded-xl mb-8 hover-lift fade-in" style="animation-delay: 0.4s;">
            <div class="p-6 border-b border-gray-200/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Pengajuan Menunggu Persetujuan</h3>
                            <p class="text-sm text-gray-500">{{ $pendingApprovals->count() }} permohonan memerlukan review Anda</p>
                        </div>
                    </div>
                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-medium">
                        {{ $pendingApprovals->count() }} Pending
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    @foreach($pendingApprovals as $request)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono text-sm font-medium text-gray-900">
                                        @if($request->status === 'completed')
                                            {{ $request->kode_sppd }}
                                        @endif
                                    </span>
                                    <span class="status-badge status-submitted">
                                        <i class="fas fa-clock mr-1"></i>Menunggu Review
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Pemohon:</p>
                                        <p class="font-medium text-gray-900">{{ optional($request->user)->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->user->role }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Tujuan:</p>
                                        <p class="font-medium text-gray-900">{{ $request->tujuan }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Keperluan:</p>
                                        <p class="text-gray-900">{{ Str::limit($request->keperluan, 100) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Tanggal Keberangkatan:</p>
                                        <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d/m/Y') }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span><i class="fas fa-calendar mr-1"></i>Diajukan: {{ $request->created_at->format('d/m/Y H:i') }}</span>
                                    @if($request->total_biaya > 0)
                                        <span><i class="fas fa-money-bill mr-1"></i>Biaya: Rp {{ number_format($request->total_biaya, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 ml-4">
                                <a href="{{ route('travel-requests.show', $request->id) }}" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center w-full mb-2 md:w-auto md:mb-0">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                                <a href="{{ route('approval.pimpinan.index') }}" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-center w-full md:w-auto">
                                    <i class="fas fa-check mr-1"></i>Review
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($pendingApprovals->count() > 3)
                <div class="mt-4 text-center">
                    <a href="{{ route('approval.pimpinan.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                        Lihat semua pengajuan <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Daftar Semua SPPD -->
        <div class="glass-card rounded-xl hover-lift fade-in" style="animation-delay: 0.5s;">
            <div class="p-6 border-b border-gray-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Daftar Semua SPPD</h3>
                        <!-- Total permohonan disembunyikan dari menu Daftar SPPD -->
                    </div>
                    <!-- Tombol Export & Refresh disembunyikan dari menu Daftar SPPD -->
                </div>
            </div>

            <!-- Search and Filter disembunyikan dari menu Daftar SPPD -->
            <div class="hidden">
                ...existing code for search and filter...
            </div>

            <div class="table-responsive overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode SPPD</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Keperluan</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/50" id="sppd-table">
                        @forelse($travelRequests as $request)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($request->status === 'completed')
                                        {{ $request->kode_sppd }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $request->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ optional($request->user)->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $request->user->role }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $request->tujuan }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($request->keperluan, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClass = [
                                        'in_review' => 'status-in_review',
                                        'revision' => 'status-revision',
                                        'rejected' => 'status-rejected',
                                        'completed' => 'status-completed'
                                    ][$request->status] ?? 'status-in_review';
                                    $statusText = [
                                        'in_review' => 'Menunggu Review',
                                        'revision' => 'Revisi',
                                        'rejected' => 'Ditolak',
                                        'completed' => 'Disetujui'
                                    ][$request->status] ?? 'Menunggu Review';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($request->total_biaya > 0)
                                        Rp {{ number_format($request->total_biaya, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <a href="{{ route('travel-requests.show', $request->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada SPPD</h3>
                                    @if(!in_array(Auth::user()->role, ['admin']))
                                    <p class="text-gray-500 mb-6">Mulai dengan membuat permohonan SPPD pertama</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($travelRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200/50">
                {{ $travelRequests->links() }}
            </div>
            @endif
        </div>
    </main>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('role-filter').value = '';
    location.reload();
}
function exportData() {
    console.log('Exporting data...');
    // Implementasi export
}
setInterval(() => {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            console.log('Data refreshed');
        });
}, 30000);
</script>
@endpush
