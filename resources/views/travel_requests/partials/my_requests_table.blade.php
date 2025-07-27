@php
    $statusConfig = [
        'in_review' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Menunggu'],
        'revision' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Revisi'],
        'rejected' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Ditolak'],
        'completed' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Disetujui']
    ];
@endphp
<div class="divide-y divide-gray-200">
    @forelse($travelRequests as $request)
    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <!-- Header Row -->
                <div class="flex items-center space-x-3 mb-3">
                    <span class="font-mono text-sm font-medium text-gray-900">
                        @if($request->status === 'completed')
                            {{ $request->kode_sppd }}
                        @else
                            Antrian: #{{ $request->id }}
                        @endif
                    </span>
                    @php $status = $statusConfig[$request->status] ?? $statusConfig['in_review']; @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status['class'] }}">
                        {{ $status['text'] }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $request->created_at->format('d M Y') }}</span>
                </div>
                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                    <div>
                        <h3 class="text-base font-medium text-gray-900 mb-1">{{ $request->tujuan }}</h3>
                        <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($request->keperluan, 120) }}</p>
                    </div>
                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-blue-500 mr-2"></i>
                            <span>{{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d/m/Y') }}</span>
                            <span class="mx-1">-</span>
                            <span>{{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d/m/Y') }}</span>
                        </div>
                        @if($request->total_biaya > 0)
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                <span>Rp {{ number_format($request->total_biaya, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Meta Information -->
                <div class="flex items-center space-x-4 text-xs text-gray-500">
                    <span>
                        <i class="fas fa-user mr-1"></i>
                        {{ $request->user->name ?? 'Unknown' }}
                        @if($request->user_id == Auth::id())
                            <span class="ml-1 text-green-600 font-semibold">(Pengaju)</span>
                        @else
                            <span class="ml-1 text-blue-600 font-semibold">(Peserta)</span>
                        @endif
                    </span>
                    @if($request->current_approver_role)
                        <span>
                            <i class="fas fa-clock mr-1"></i>
                            Menunggu {{ $request->current_approver_role }}
                        </span>
                    @endif
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="flex items-center space-x-2 ml-4">
                @if($request->status === 'revision' && $request->user_id == Auth::id())
                    <a href="{{ route('travel-requests.edit', $request->id) }}" class="inline-flex items-center px-3 py-2 border border-yellow-400 rounded-md text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <i class="fas fa-edit mr-1"></i>
                        Edit & Ajukan Ulang
                    </a>
                @endif
                <a href="{{ route('travel-requests.show', $request->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-eye mr-1"></i>
                    Detail
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="px-6 py-12">
        <div class="text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada SPPD</h3>
            <p class="text-gray-500 mb-6">Anda belum memiliki permohonan SPPD. Mulai dengan membuat permohonan baru.</p>
            @if(Auth::user()->role === 'kasubbag')
            <a href="{{ route('travel-requests.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                Buat SPPD Baru
            </a>
            @endif
        </div>
    </div>
    @endforelse
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
                    {!! $travelRequests->appends(request()->except('page'))->links() !!}
                </div>
            </div>
        </div>
    </div>
    @endif
</div> 