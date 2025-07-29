@extends('layouts.app')

@section('content')
<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6 fade-in">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 mb-1">Persetujuan SPPD</h2>
                                    <p class="text-sm text-gray-600">Review dan setujui permohonan perjalanan dinas sebagai {{ $user->role }}</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- <div class="text-right">
                    <p class="text-sm text-gray-600">Update terakhir</p>
                    <p class="text-sm font-medium text-gray-900">{{ now()->format('d M Y H:i') }} WIB</p>
                </div> --}}
                <button onclick="location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sync-alt mr-2 text-base"></i>Refresh
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

    <!-- SPPD Requests Table -->
    <div class="glass-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Daftar SPPD Menunggu Persetujuan</h3>
            <p class="text-sm text-gray-600">{{ $requests->total() }} pengajuan ditemukan</p>
        </div>

        @if($requests->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            SPPD & Pemohon
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tujuan & Keperluan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal & Durasi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Budget
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status & Prioritas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $request)
                    @php
                        $totalBudget = $request->biaya_transport + $request->biaya_penginapan + $request->uang_harian + $request->biaya_lainnya;
                        $isUrgent = \Carbon\Carbon::parse($request->tanggal_berangkat)->diffInDays(now()) <= 3;
                        $duration = \Carbon\Carbon::parse($request->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($request->tanggal_kembali)) + 1;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- SPPD & Pemohon -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm">
                                            {{ strtoupper(substr($request->user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($request->status === 'completed')
                                            {{ $request->kode_sppd }}
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $request->user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $request->user->role }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Tujuan & Keperluan -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $request->tujuan }}</div>
                            <div class="text-sm text-gray-500 max-w-xs truncate">{{ $request->keperluan }}</div>
                        </td>

                        <!-- Tanggal & Durasi -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d M Y') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $duration }} hari
                            </div>
                            @if($isUrgent)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1 text-xs"></i>Mendesak
                                </span>
                            @endif
                        </td>

                        <!-- Budget -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                Rp {{ number_format($totalBudget, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Transport: Rp {{ number_format($request->biaya_transport, 0, ',', '.') }}<br>
                                Penginapan: Rp {{ number_format($request->biaya_penginapan, 0, ',', '.') }}<br>
                                Harian: Rp {{ number_format($request->uang_harian, 0, ',', '.') }}
                            </div>
                        </td>

                        <!-- Status & Prioritas -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClasses = [
                                    'submitted' => 'bg-blue-100 text-blue-800',
                                    'in_review' => 'bg-yellow-100 text-yellow-800',
                                    'revision_minor' => 'bg-orange-100 text-orange-800',
                                ];
                                $statusLabels = [
                                    'submitted' => 'Diajukan',
                                    'in_review' => 'Review',
                                    'revision_minor' => 'Revisi',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$request->status] ?? ucfirst($request->status) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                Diajukan: {{ $request->created_at->format('d M H:i') }}
                            </div>
                        </td>

                        <!-- Aksi -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('travel-requests.show', $request->id) }}"
                                   class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <i class="fas fa-eye mr-1 text-base"></i>Detail
                                </a>

                                <!-- Quick Action Buttons -->
                                @if(auth()->user()->role !== 'admin')
                                    <form method="POST" action="{{ route('approval.pimpinan.approve', $request->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs hover:bg-green-200 transition-colors"
                                                onclick="return confirm('Setujui SPPD ini?')">
                                            <i class="fas fa-check mr-1 text-base"></i>Setujui
                                        </button>
                                    </form>

                                    <button type="button"
                                            class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs hover:bg-red-200 transition-colors"
                                            onclick="showRejectModal('{{ $request->id }}', '{{ $request->kode_sppd }}')">
                                        <i class="fas fa-times mr-1 text-base"></i>Tolak
                                    </button>

                                    @if($request->status === 'in_review')
                                    <button type="button"
                                            class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs hover:bg-yellow-200 transition-colors"
                                            onclick="showRevisionModal('{{ $request->id }}', '{{ $request->kode_sppd }}')">
                                        <i class="fas fa-edit mr-1 text-base"></i>Revisi
                                    </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $requests->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <div class="mx-auto h-24 w-24 text-gray-400">
                <i class="fas fa-inbox text-6xl"></i>
            </div>
            <h3 class="mt-4 text-sm font-medium text-gray-900">Tidak ada SPPD yang menunggu persetujuan</h3>
            <p class="mt-1 text-sm text-gray-500">Semua pengajuan telah diproses atau belum ada pengajuan baru.</p>
        </div>
        @endif
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Update terakhir: {{ now()->format('d M Y H:i') }} WIB</p>
    </div>
</main>

<!-- Modal for Rejection -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tolak SPPD</h3>
                <p class="text-sm text-gray-600 mb-4">SPPD: <span id="rejectSppdCode"></span></p>

                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan</label>
                <textarea name="rejection_reason"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                          rows="3"
                          placeholder="Jelaskan alasan penolakan SPPD ini..."
                          required></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    Tolak SPPD
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Revision -->
<div id="revisionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <form id="revisionForm" method="POST">
            @csrf
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Minta Revisi SPPD</h3>
                @if(isset($request) && $request->status === 'completed' && $request->kode_sppd)
  <p class="text-sm text-gray-600 mb-4">
    SPPD: <span id="revisionSppdCode">{{ $request->kode_sppd }}</span>
  </p>
@endif

                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Revisi</label>
                <textarea name="revision_reason"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                          rows="3"
                          placeholder="Jelaskan perbaikan yang diperlukan..."
                          required></textarea>

                <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Target Revisi</label>
                <select name="target" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                    <option value="">Pilih Target</option>
                    @if(auth()->user()->role === 'sekretaris')
                        <option value="kasubbag">Kembali ke Kasubbag</option>
                    @elseif(auth()->user()->role === 'ppk')
                        <option value="sekretaris">Kembali ke Sekretaris</option>
                        <option value="kasubbag">Kembali ke Kasubbag</option>
                    @endif
                </select>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRevisionModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                    Minta Revisi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(id, code) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectSppdCode').textContent = code;
    document.getElementById('rejectForm').action = `/approval/pimpinan/${id}/reject`;
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function showRevisionModal(id, code) {
    document.getElementById('revisionModal').classList.remove('hidden');
    var kodeElem = document.getElementById('revisionSppdCode');
    if (kodeElem) {
        kodeElem.textContent = code;
    }
    document.getElementById('revisionForm').action = "{{ url('/approval/pimpinan') }}/" + id + "/revision";
}

function closeRevisionModal() {
    document.getElementById('revisionModal').classList.add('hidden');
}

// Auto refresh every 60 seconds
setInterval(function() {
    location.reload();
}, 60000);
</script>
@endsection
