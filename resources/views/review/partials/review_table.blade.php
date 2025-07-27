<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Kode SPPD</th>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Pemohon</th>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Tujuan</th>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($travelRequests as $req)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-2">{{ $loop->iteration }}</td>
            <td class="px-4 py-2">
                @if($req->status === 'completed')
                    {{ $req->kode_sppd }}
                @endif
            </td>
            <td class="px-4 py-2">{{ $req->user->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $req->tujuan }}</td>
            <td class="px-4 py-2">{{ $req->tanggal_berangkat }} s/d {{ $req->tanggal_kembali }}</td>
            <td class="px-4 py-2">
                @php
                    $statusClasses = [
                        'in_review' => 'bg-yellow-100 text-yellow-800',
                        'revision_minor' => 'bg-orange-100 text-orange-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'completed' => 'bg-green-100 text-green-800',
                    ];
                    $statusLabels = [
                        'in_review' => 'Review',
                        'revision_minor' => 'Revisi',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ];
                @endphp
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses[$req->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $statusLabels[$req->status] ?? ucfirst($req->status) }}
                </span>
            </td>
            <td class="px-4 py-2">
                <a href="{{ route('travel-requests.show', $req->id) }}" class="text-blue-600 hover:underline">Detail</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-gray-400 py-6">Belum ada SPPD dalam review.</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">{{ $travelRequests->links() }}</div> 