@extends('layouts.app')

@section('content')
<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6 fade-in">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Persetujuan SPPD</h2>
                                    <p class="text-sm text-gray-600">Review dan setujui permohonan perjalanan dinas sebagai {{ $user->role }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-right">
                    <p class="text-sm text-gray-600">Update terakhir</p>
                    <p class="text-sm font-medium text-gray-900">{{ now()->format('d M Y H:i') }} WIB</p>
                </div>
                <button onclick="location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
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

    <!-- Enhanced Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Pending -->
        <div class="glass-card p-4 hover-lift fade-in border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-orange-500"></div>
                        <p class="text-sm font-medium text-gray-700">Menunggu Persetujuan</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_pending'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Budget: Rp {{ number_format($stats['total_budget_pending'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Approved Today -->
        <div class="glass-card p-4 hover-lift fade-in border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-green-500"></div>
                        <p class="text-sm font-medium text-gray-700">Disetujui Hari Ini</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['approved_today'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Oleh saya: {{ $stats['my_approvals_today'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Urgent Requests -->
        <div class="glass-card p-4 hover-lift fade-in border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-red-500"></div>
                        <p class="text-sm font-medium text-gray-700">Perjalanan Mendesak</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['urgent_requests'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Berangkat ≤ 3 hari
                    </p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Processing Time -->
        <div class="glass-card p-4 hover-lift fade-in border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-blue-500"></div>
                        <p class="text-sm font-medium text-gray-700">Rata-rata Proses</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_approval_time'] ?? 0 }}h</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Revisi: {{ $stats['revision_pending'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    @if(isset($recentActivities) && $recentActivities->count() > 0)
    <div class="mb-6">
        <div class="glass-card p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Aktivitas Terakhir Saya</h3>
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
                <div class="p-2 bg-orange-100 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 hover-lift fade-in" style="animation-delay: 0.1s;">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-blue-500"></div>
                        <p class="text-sm font-medium text-gray-700">Baru Diajukan</p>
                    </div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['just_submitted'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">Belum direview</p>
                </div>
                <div class="p-2 bg-blue-100 rounded-full">
                    <i class="fas fa-inbox text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 hover-lift fade-in" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-yellow-500"></div>
                        <p class="text-sm font-medium text-gray-700">Dalam Review</p>
                    </div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['in_review'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">Sedang diproses</p>
                </div>
                <div class="p-2 bg-yellow-100 rounded-full">
                    <i class="fas fa-search text-yellow-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 hover-lift fade-in" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-red-500"></div>
                        <p class="text-sm font-medium text-gray-700">Urgent</p>
                    </div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['urgent'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">Prioritas tinggi</p>
                </div>
                <div class="w-10 h-10 bg-yellow-50 rounded-md flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 hover-lift fade-in" style="animation-delay: 0.1s;">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-green-500"></div>
                        <p class="text-sm font-medium text-gray-700">Disetujui Hari Ini</p>
                    </div>
                    <p class="text-xl font-bold text-gray-900">0</p>
                    <p class="text-xs text-gray-600 mt-1">SPPD approved</p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-md flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 hover-lift fade-in" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-blue-500"></div>
                        <p class="text-sm font-medium text-gray-700">Review Bulan Ini</p>
                    </div>
                    <p class="text-xl font-bold text-gray-900">0</p>
                    <p class="text-xs text-gray-600 mt-1">Total reviewed</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-md flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 hover-lift fade-in" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <div class="status-dot bg-purple-500"></div>
                        <p class="text-sm font-medium text-gray-700">Rata-rata Review</p>
                    </div>
                    <p class="text-xl font-bold text-gray-900">2.3</p>
                    <p class="text-xs text-gray-600 mt-1">hari</p>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-md flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="glass-card fade-in" style="animation-delay: 0.4s;">
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clipboard-check mr-2 text-gray-500"></i>
                        Pengajuan Menunggu Persetujuan
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Review dan proses permohonan SPPD</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800">
                        <div class="status-dot bg-yellow-500 mr-1"></div>
                        {{ $requests->count() }} Permintaan
                    </span>
                    <button onclick="refreshData()" class="px-3 py-1 text-xs text-gray-700 hover:text-gray-900 rounded-md hover:bg-gray-100 transition-colors">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4">
            @if($requests->isEmpty())
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-inbox text-xl text-gray-500"></i>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 mb-2">Tidak ada pengajuan</h3>
                    <p class="text-sm text-gray-600 mb-4">Tidak ada pengajuan yang menunggu persetujuan saat ini.</p>
                    <a href="{{ route('travel-requests.index') }}" class="primary-button px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-list mr-1"></i>Lihat Semua SPPD
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="col-no px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">No</th>
                                <th class="col-kode px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Kode SPPD</th>
                                <th class="col-pemohon px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Pemohon</th>
                                <th class="col-tujuan px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tujuan & Keperluan</th>
                                <th class="col-tanggal px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tanggal Perjalanan</th>
                                <th class="col-status px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="col-prioritas px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Prioritas</th>
                                <th class="col-aksi px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($requests as $index => $request)
                                <tr class="table-row {{ $request->is_urgent ?? false ? 'bg-yellow-50/50' : '' }}">
                                    <td class="col-no px-3 py-2 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="col-kode px-3 py-2 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-gray-900">
                                                @if($request->status === 'completed')
                                                    {{ $request->kode_sppd }}
                                                @endif
                                            </span>
                                            @if($request->is_urgent ?? false)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-800">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Urgent
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="col-pemohon px-3 py-2 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-white text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ strtoupper(optional($request->user)->name ?? '-') }}
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    {{ $request->user->jabatan ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    NIP: {{ $request->user->nip ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-tujuan px-3 py-2 max-w-md">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $request->tujuan }}</div>
                                        <div class="text-sm text-gray-600 truncate">
                                            {{ Str::limit($request->keperluan ?? 'Tidak ada keterangan', 50) }}
                                        </div>
                                    </td>
                                    <td class="col-tanggal px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <div class="font-medium">
                                                {{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d M Y') }}
                                            </div>
                                            <div class="text-gray-600">
                                                s/d {{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d M Y') }}
                                            </div>
                                            <div class="text-xs text-blue-600 mt-1">
                                                {{ \Carbon\Carbon::parse($request->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($request->tanggal_kembali)) + 1 }} hari
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-status px-3 py-2 whitespace-nowrap">
                                        @if($request->status === 'in_review')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800">
                                                <div class="status-dot bg-yellow-500 mr-1"></div>Dalam Review
                                            </span>
                                        @elseif($request->status === 'revision')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800">
                                                <div class="status-dot bg-yellow-500 mr-1"></div>Dalam Review
                                            </span>
                                        @endif
                                        @if($request->current_approver_role)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Menunggu: {{ $request->current_approver_role }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="col-prioritas px-3 py-2 whitespace-nowrap">
                                        @if($request->is_urgent ?? false)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-800">
                                                <div class="status-dot bg-red-500 mr-1"></div>Tinggi
                                            </span>
                                        @elseif(isset($request->created_at) && $request->created_at->diffInDays(now()) > 3)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800">
                                                <div class="status-dot bg-yellow-500 mr-1"></div>Mendesak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-800">
                                                <div class="status-dot bg-green-500 mr-1"></div>Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="col-aksi px-3 py-2 whitespace-nowrap text-sm">
                                        <div class="flex items-center justify-end space-x-1">
                                            <!-- Tombol Review/Detail -->
                                            <a href="{{ route('approval.pimpinan.show', $request->id) }}"
                                               class="inline-flex items-center px-2 py-1 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <i class="fas fa-search mr-1"></i>Review
                                            </a>

                                            <!-- Tombol Setujui -->
                                            <form action="{{ route('approval.pimpinan.approve', $request->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Setujui pengajuan SPPD untuk {{ optional($request->user)->name ?? '-' }}?\n\nTujuan: {{ $request->tujuan }}\nTanggal: {{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d M Y') }} - {{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d M Y') }}')"
                                                        class="inline-flex items-center px-2 py-1 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                                    <i class="fas fa-check mr-1"></i>Setujui
                                                </button>
                                            </form>

                                            <!-- Tombol Revisi -->
                                            @if($request->status === 'in_review')
                                            <button type="button"
                                                    onclick="openRevisionModal({{ $request->id }}, '{{ optional($request->user)->name ?? '-' }}', '{{ $request->kode_sppd }}')"
                                                    class="inline-flex items-center px-2 py-1 border border-yellow-300 text-xs font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                                                <i class="fas fa-edit mr-1"></i>Revisi
                                            </button>
                                            @endif

                                            <!-- Tombol Tolak -->
                                            <button type="button"
                                                    onclick="openRejectModal({{ $request->id }}, '{{ optional($request->user)->name ?? '-' }}')"
                                                    class="inline-flex items-center px-2 py-1 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                <i class="fas fa-times mr-1"></i>Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</main>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 modal-backdrop transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
        <div class="inline-block align-middle modal-content text-left overflow-hidden transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
            <div class="px-4 pt-4 pb-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900" id="modal-title">
                        <i class="fas fa-file-alt mr-2 text-gray-500"></i>
                        Detail Permintaan SPPD
                    </h3>
                    <button type="button" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-gray-100 transition-colors" onclick="closeDetailModal()">
                        <svg class="w-8 h-8" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                            <line x1="6" y1="6" x2="18" y2="18" />
                            <line x1="6" y1="18" x2="18" y2="6" />
                        </svg>
                    </button>
                </div>
                <div id="detailContent" class="space-y-4">
                    <div class="animate-pulse">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-100 rounded w-2/3"></div>
                            </div>
                            <div class="space-y-3">
                                <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-100 rounded w-2/3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-100 px-4 py-3 flex justify-end">
                <button type="button" onclick="closeDetailModal()" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="reject-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 modal-backdrop transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
        <div class="inline-block align-middle modal-content text-left overflow-hidden transform transition-all sm:my-8 sm:max-w-md sm:w-full">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="px-4 pt-4 pb-3">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 mb-2" id="reject-modal-title">
                                Tolak Permintaan SPPD
                            </h3>
                            <p class="text-sm text-gray-600 mb-3" id="rejectText">
                                Apakah Anda yakin ingin menolak permintaan SPPD ini?
                            </p>
                            <div>
                                <label for="reject_reason" class="block text-sm font-medium text-gray-700 mb-1">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea id="reject_reason" name="rejection_reason" rows="3" required
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"
                                          placeholder="Masukkan alasan penolakan yang jelas dan konstruktif..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-100 px-4 py-3 flex justify-end space-x-2">
                    <button type="button" onclick="closeRejectModal()" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="danger-button px-3 py-1 rounded-md text-sm font-medium">
                        <i class="fas fa-times mr-1"></i>Tolak Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div id="revisionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="revision-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 modal-backdrop transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle modal-content text-left overflow-hidden transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <form id="revisionForm" method="POST">
                @csrf
                <div class="glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-2" id="revision-modal-title">
                                Minta Revisi SPPD
                            </h3>
                            <p class="text-sm text-gray-600">Ajukan revisi untuk <span id="revision-user-name" class="font-medium">-</span></p>
                            @if(isset($request) && $request->status === 'completed' && $request->kode_sppd)
  <p class="text-sm text-gray-600 mb-4">
    SPPD: <span id="revisionSppdCode">{{ $request->kode_sppd }}</span>
  </p>
@endif
                        </div>
                        <button type="button" onclick="closeRevisionModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="target" class="block text-sm font-medium text-gray-700 mb-2">
                                Kirim Revisi Ke:
                            </label>
                            <select name="target" id="revision-target" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Target</option>
                                @if(auth()->user()->role === 'sekretaris')
                                    <option value="kasubbag">Kembali ke Kasubbag</option>
                                @elseif(auth()->user()->role === 'ppk')
                                    <option value="sekretaris">Kembali ke Sekretaris</option>
                                    <option value="kasubbag">Kembali ke Kasubbag</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label for="revision_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Revisi: <span class="text-red-500">*</span>
                            </label>
                            <textarea name="revision_reason" id="revision-reason" rows="4" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Jelaskan bagian mana yang perlu direvisi dan alasannya..."></textarea>
                            <small class="text-gray-500">Minimal 10 karakter</small>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 px-6 py-3 bg-gray-50">
                    <button type="button" onclick="closeRevisionModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md text-sm font-medium hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                        <i class="fas fa-edit mr-1"></i>Kirim Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Toggle notifications dropdown
    function toggleNotifications() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationDropdown = document.getElementById('notification-dropdown');
        if (notificationDropdown && !event.target.closest('.relative')) {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu) {
                mobileMenu.classList.toggle('hidden');
            }
        });
    }

    function openDetailModal(requestId) {
        document.getElementById('detailContent').innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-user mr-2 text-gray-500"></i>
                            Informasi Pemohon
                        </h4>
                        <div class="bg-gray-50 rounded-md p-3 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Nama:</span>
                                <span class="text-sm font-medium text-gray-900">Loading...</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Jabatan:</span>
                                <span class="text-sm font-medium text-gray-900">Loading...</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">NIP:</span>
                                <span class="text-sm font-medium text-gray-900">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-calendar mr-2 text-gray-500"></i>
                            Waktu Pengajuan
                        </h4>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-sm text-gray-600">Diajukan pada: <span class="font-medium text-gray-900">Loading...</span></p>
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-500"></i>
                            Informasi Perjalanan
                        </h4>
                        <div class="bg-gray-50 rounded-md p-3 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tujuan:</span>
                                <span class="text-sm font-medium text-gray-900">Loading...</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Keperluan:</span>
                                <span class="text-sm font-medium text-gray-900">Loading...</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Durasi:</span>
                                <span class="text-sm font-medium text-gray-900">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-money-bill mr-2 text-gray-500"></i>
                            Estimasi Biaya
                        </h4>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-sm text-gray-600">Total: <span class="font-medium text-gray-900">Loading...</span></p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    function openRejectModal(requestId, userName) {
        document.getElementById('rejectText').textContent = `Apakah Anda yakin ingin menolak permintaan SPPD untuk ${userName}?`;
        document.getElementById('rejectForm').action = `{{ url('approval/pimpinan/reject') }}/${requestId}`;
        document.getElementById('reject_reason').value = '';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    function openRevisionModal(requestId, userName, kodeSppd) {
        document.getElementById('revision-user-name').textContent = userName;
        var kodeElem = document.getElementById('revisionSppdCode');
        if (kodeElem) {
            kodeElem.textContent = kodeSppd;
        }
        document.getElementById('revisionForm').action = "{{ url('/approval/pimpinan') }}/" + requestId + "/revision";
        document.getElementById('revision-reason').value = '';
        document.getElementById('revision-target').value = '';
        document.getElementById('revisionModal').classList.remove('hidden');
    }

    function closeRevisionModal() {
        document.getElementById('revisionModal').classList.add('hidden');
    }

    function refreshData() {
        window.location.reload();
    }

    document.addEventListener('click', function(event) {
        const detailModal = document.getElementById('detailModal');
        const rejectModal = document.getElementById('rejectModal');
        const revisionModal = document.getElementById('revisionModal');
        const notificationDropdown = document.getElementById('notification-dropdown');

        if (event.target === detailModal) {
            closeDetailModal();
        }

        if (event.target === rejectModal) {
            closeRejectModal();
        }

        if (event.target === revisionModal) {
            closeRevisionModal();
        }

        if (notificationDropdown && !event.target.closest('.relative')) {
            notificationDropdown.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDetailModal();
            closeRejectModal();
            closeRevisionModal();
            const notificationDropdown = document.getElementById('notification-dropdown');
            if (notificationDropdown) {
                notificationDropdown.classList.add('hidden');
            }
        }
    });
</script>
@endpush
