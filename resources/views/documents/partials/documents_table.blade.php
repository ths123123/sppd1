<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode SPPD</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengaju</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @php
                // Mengelompokkan dokumen berdasarkan kode SPPD
                $groupedDocuments = $documents->groupBy(function($doc) {
                    return $doc->travelRequest->kode_sppd ?? 'Tidak Ada Kode';
                });
                $counter = ($documents->currentPage() - 1) * $documents->perPage();
            @endphp
            
            @forelse($groupedDocuments as $kodeSppd => $docs)
                @php
                    $counter++;
                    // Mengambil dokumen pertama sebagai referensi untuk informasi SPPD
                    $firstDoc = $docs->first();
                    $travelRequest = $firstDoc->travelRequest;
                @endphp
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $counter }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-blue-600 font-semibold">
                            {{ $kodeSppd }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">
                                        {{ substr($travelRequest->user->name ?? 'N', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $travelRequest->user->name ?? 'Tidak Diketahui' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $travelRequest->user->email ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $travelRequest->destination ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $travelRequest->created_at->format('d/m/Y H:i') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($travelRequest)
                            @php
                                $status = $travelRequest->status;
                                $statusConfig = [
                                    'in_review' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fas fa-clock', 'text' => 'Diajukan'],
                                    'completed' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fas fa-check-circle', 'text' => 'Disetujui'],
                                    'rejected' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fas fa-times-circle', 'text' => 'Ditolak'],
                                    'revision' => ['class' => 'bg-orange-100 text-orange-800', 'icon' => 'fas fa-redo', 'text' => 'Revisi']
                                ];
                                $config = $statusConfig[$status] ?? $statusConfig['in_review'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                <i class="{{ $config['icon'] }} mr-1"></i>
                                {{ $config['text'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-question mr-1"></i>
                                Tidak Diketahui
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="toggleDocuments('{{ $kodeSppd }}')" 
                               class="inline-flex items-center bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition-colors duration-200">
                                <i class="fas fa-eye mr-1"></i>
                                Lihat Dokumen ({{ $docs->count() }})
                            </button>
                            @if($travelRequest)
                            <a href="{{ route('travel-requests.show', $travelRequest->id) }}"
                               class="inline-flex items-center bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 transition-colors duration-200">
                                <i class="fas fa-info-circle mr-1"></i>
                                Detail SPPD
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                <!-- Baris untuk menampilkan dokumen yang tersembunyi -->
                <tr id="documents-{{ $kodeSppd }}" class="hidden bg-gray-50">
                    <td colspan="7" class="px-6 py-4">
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                <h4 class="font-medium text-gray-700">Daftar Dokumen untuk {{ $kodeSppd }}</h4>
                            </div>
                            <div class="divide-y divide-gray-200">
                                @foreach($docs as $index => $doc)
                                    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <span class="text-gray-500 w-8">{{ $index + 1 }}.</span>
                                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                            <span class="text-sm font-medium text-gray-900">{{ $doc->original_filename }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-gray-500">Diupload: {{ $doc->created_at->format('d/m/Y H:i') }} oleh {{ $doc->uploader->name ?? '-' }}</span>
                                            <a href="{{ route('documents.download', $doc->id) }}"
                                               class="inline-flex items-center bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 transition-colors duration-200">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-500 mb-2">Belum Ada Dokumen</h3>
                        <p class="text-gray-400">Belum ada dokumen SPPD yang terupload di sistem.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($documents->hasPages())
<div class="bg-white px-6 py-3 border-t border-gray-200">
    {{ $documents->links() }}
</div>
@endif