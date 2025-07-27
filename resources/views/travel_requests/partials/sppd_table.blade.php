@if($travelRequests->count() > 0)
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon & Tujuan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Jadwal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Anggaran</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody id="sppd-table-body" class="bg-white divide-y divide-gray-200">
            @foreach($travelRequests as $request)
            <tr class="sppd-row">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-bold text-sm">{{ strtoupper(substr($request->user->name, 0, 2)) }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-bold text-gray-900">{{ $request->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $request->tujuan }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d M Y') }}</div>
                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d M Y') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                    <div class="text-sm text-gray-900">Rp {{ number_format($request->biaya_transport + $request->biaya_penginapan + $request->uang_harian + $request->biaya_lainnya, 0, ',', '.') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @php
                        $statusConfig = [
                            'in_review' => ['label' => 'Review', 'class' => 'bg-yellow-100 text-yellow-800'],
                            'revision' => ['label' => 'Revisi', 'class' => 'bg-orange-100 text-orange-800'],
                            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-800'],
                            'completed' => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-800'],
                            'submitted' => ['label' => 'Diajukan', 'class' => 'bg-blue-100 text-blue-800'],
                        ];
                        $currentStatus = $statusConfig[$request->status] ?? ['label' => ucfirst($request->status), 'class' => 'bg-gray-100 text-gray-800'];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $currentStatus['class'] }}">
                        {{ $currentStatus['label'] }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <a href="{{ route('travel-requests.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900 transition">Detail</a>
                    @if($request->status === 'revision')
                        <a href="{{ route('travel-requests.edit', $request->id) }}" class="ml-4 text-green-600 hover:text-green-900 transition">Revisi</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="px-6 py-4 border-t border-gray-200">
    {{ $travelRequests->links() }}
</div>
@else
<div id="no-results-row" class="text-center py-16">
    <i class="fas fa-search text-gray-400 text-4xl mb-3"></i>
    <h3 class="text-lg font-medium text-gray-700">Tidak ada data ditemukan</h3>
    <p class="text-sm text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
</div>
@endif
