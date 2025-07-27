{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-5 flex items-center border-l-4 border-blue-500">
        <div class="bg-blue-100 p-3 rounded-full mr-4"><i class="fas fa-list text-blue-600 text-2xl"></i></div>
        <div>
            <div class="text-xs text-gray-500">Total SPPD</div>
            <div class="text-2xl font-bold text-blue-700">{{ number_format($totalSPPD) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 flex items-center border-l-4 border-green-500">
        <div class="bg-green-100 p-3 rounded-full mr-4"><i class="fas fa-check-circle text-green-600 text-2xl"></i></div>
        <div>
            <div class="text-xs text-gray-500">Disetujui</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totalApproved) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 flex items-center border-l-4 border-red-500">
        <div class="bg-red-100 p-3 rounded-full mr-4"><i class="fas fa-times-circle text-red-600 text-2xl"></i></div>
        <div>
            <div class="text-xs text-gray-500">Ditolak</div>
            <div class="text-2xl font-bold text-red-700">{{ number_format($totalRejected) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 flex items-center border-l-4 border-yellow-500">
        <div class="bg-yellow-100 p-3 rounded-full mr-4"><i class="fas fa-coins text-yellow-600 text-2xl"></i></div>
        <div>
            <div class="text-xs text-gray-500">Total Anggaran</div>
            <div class="text-2xl font-bold text-yellow-700">Rp {{ number_format($totalBudget) }}</div>
        </div>
    </div>
</div>



{{-- Tabel Rekap Per Status --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-tasks text-purple-600 mr-2"></i>
        Rekap Per Status
    </h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-right">Jumlah SPPD</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $statusLabels = [
                        'completed' => 'Selesai',
                        'in_review' => 'Review',
                        'rejected' => 'Ditolak',
                        'revision' => 'Revisi',
                    ];
                    $statusBadges = [
                        'completed' => 'bg-green-100 text-green-800',
                        'in_review' => 'bg-yellow-100 text-yellow-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'revision' => 'bg-orange-100 text-orange-800',
                    ];
                @endphp
                @foreach($statusDistribution as $key => $count)
                <tr>
                    <td class="px-4 py-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusBadges[$key] }}">
                            {{ $statusLabels[$key] }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">{{ number_format($count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Tabel Rekap Per Peserta --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-user-friends text-blue-600 mr-2"></i>
        Rekap Per Peserta SPPD
    </h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Nama Peserta</th>
                    <th class="px-4 py-2 text-left">Role</th>
                    <th class="px-4 py-2 text-right">Total SPPD</th>
                    <th class="px-4 py-2 text-right">Disetujui</th>
                    <th class="px-4 py-2 text-right">Ditolak</th>
                    <th class="px-4 py-2 text-right">Revisi</th>
                    <th class="px-4 py-2 text-right">Review</th>
                    <th class="px-4 py-2 text-right">Total Anggaran</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pesertaStats as $peserta)
                <tr>
                    <td class="px-4 py-2">{{ $peserta->name }}</td>
                    <td class="px-4 py-2">{{ ucfirst($peserta->role) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($peserta->total_sppd) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($peserta->approved_count) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($peserta->rejected_count) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($peserta->revision_count) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($peserta->review_count) }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($peserta->total_budget) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-gray-500 py-4">Tidak ada data peserta</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Chart.js scripts (placeholder, data diisi JS di main.blade.php) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

</script> 