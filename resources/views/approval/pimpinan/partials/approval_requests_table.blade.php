@if($requests->count() > 0)
<!-- Table Content -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    SPPD & PEMOHON
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    TUJUAN & KEPERLUAN
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    TANGGAL & DURASI
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    BUDGET
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    URGENSI
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    AKSI
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($requests as $request)
            <tr data-row-id="{{ $request->id }}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-semibold">{{ substr($request->user->name ?? 'U', 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $request->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $request->user->role }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ $request->tujuan }}</div>
                    <div class="text-sm text-gray-500">{{ $request->keperluan }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d M Y') }}</div>
                    <div class="text-sm text-gray-500">{{ $request->durasi }} hari</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">Rp {{ number_format($request->total_budget ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">
                        <div>Transport: Rp {{ number_format($request->biaya_transport ?? 0, 0, ',', '.') }}</div>
                        <div>Penginapan: Rp {{ number_format($request->biaya_penginapan ?? 0, 0, ',', '.') }}</div>
                        <div>Harian: Rp {{ number_format($request->uang_harian ?? 0, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @if($request->is_urgent)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Mendesak</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Normal</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-medium space-x-2">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('travel-requests.show', $request->id) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-all duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Detail</span>
                        </a>
                        <button type="button" 
                                data-approve-btn 
                                data-id="{{ $request->id }}" 
                                data-url="{{ route('approval.pimpinan.approve', $request->id) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded-md border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setujui</span>
                        </button>
                        <button type="button" 
                                data-reject-btn 
                                data-id="{{ $request->id }}" 
                                data-url="{{ route('approval.pimpinan.reject', $request->id) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded-md border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Tolak</span>
                        </button>
                        @if($request->status === 'in_review')
                        <button type="button" 
                                data-revision-btn 
                                data-id="{{ $request->id }}" 
                                data-url="{{ route('approval.pimpinan.revision', $request->id) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-amber-50 text-amber-700 rounded-md border border-amber-200 hover:bg-amber-100 hover:border-amber-300 transition-all duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Revisi</span>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Info hasil dan Pagination -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
    <div class="text-sm text-gray-500 mb-2 sm:mb-0">
        @if($requests->total() > 0)
            Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} results
        @else
            Tidak ada data
        @endif
    </div>
    <div>
        {{ $requests->links() }}
    </div>
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