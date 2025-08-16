@extends('layouts.app')

@section('content')
@php
    $statusClass = [
        'in_review' => 'status-in_review',
        'revision' => 'status-revision',
        'rejected' => 'status-rejected',
        'completed' => 'status-completed'
    ][$travelRequest->status] ?? 'status-in_review';

    $statusText = [
        'in_review' => 'Sedang Ditinjau',
        'revision' => 'Revisi',
        'rejected' => 'Ditolak',
        'completed' => 'Disetujui'
    ][$travelRequest->status] ?? 'Sedang Ditinjau';

    $sequence = $travelRequest->getApprovalFlow();
    // Mapping role ke label dinamis sesuai urutan getApprovalFlow
    $roleLabels = [
        'kasubbag' => 'Kasubbag',
        'sekretaris' => 'Sekretaris',
        'ppk' => 'Pejabat Pembuat Komitmen',
        // tambahkan role lain jika ada
    ];
    // Sinkronisasi approval_history
    $history = is_string($travelRequest->approval_history)
        ? json_decode($travelRequest->approval_history, true)
        : $travelRequest->approval_history;
    $approvedRoles = $history && is_array($history) ? array_column($history, 'role') : [];
    $currentIdx = 0;
    $finalStatusIdx = null;
    $finalStatusType = null;
    // Cari index dan tipe step terakhir jika rejected/revision
    if ($history && is_array($history)) {
        foreach ($history as $idx => $item) {
            if (isset($item['status']) && in_array($item['status'], ['rejected','revision','revision_minor'])) {
                $finalStatusIdx = array_search($item['role'], $sequence);
                $finalStatusType = $item['status'];
                break;
            }
        }
    }
    foreach ($sequence as $i => $role) {
        if (!in_array($role, $approvedRoles)) {
            $currentIdx = $i;
            break;
        }
        $currentIdx = $i + 1;
    }
    if ($finalStatusIdx !== null) {
        $currentIdx = $finalStatusIdx;
    }
    if ($currentIdx >= count($sequence)) $currentIdx = count($sequence) - 1;
    $current = $sequence[$currentIdx] ?? null;
@endphp

<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Bar Informasi Detail SPPD -->
        <div class="glass-card rounded-xl p-6 mb-6 fade-in border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">Informasi Detail SPPD</h2>
                    <p class="text-gray-700 text-base">
                        Halaman ini menampilkan detail permohonan SPPD, status, dan riwayat persetujuan. Gunakan halaman ini untuk memantau proses dan detail perjalanan dinas.
                    </p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Approval Progress & History (Gabungan) -->
                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <a href="#" onclick="goBackOrIndex(event)" class="text-gray-600 hover:text-gray-900 transition-colors mr-2">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </a>
                        <span class="text-lg font-semibold text-gray-900">Status & Riwayat Persetujuan</span>
                    </div>
                    <!-- Kode SPPD -->
                    <div class="mb-2">
                        <span class="text-xs text-gray-500 font-mono">
                            @if($travelRequest->status === 'completed')
                                Kode SPPD: {{ $travelRequest->kode_sppd }}
                            @endif
                        </span>
                    </div>
                    <!-- Progress Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-500 mb-2">
                            <span>Progress</span>
                            <span>{{ max(1, $currentIdx+1) }}/{{ count($sequence) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php $sequenceCount = count($sequence); @endphp
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                 style="width: {{ $sequenceCount > 0 ? (($currentIdx >= 0 ? ($currentIdx + 1) : 1) / $sequenceCount * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <!-- Approval Steps with History -->
                    <div class="space-y-4">
                        @foreach($sequence as $i => $role)
                            @php
                                $level = $i + 1;
                                $stepData = collect($history)->first(function($item) use ($role, $level) {
                                    return (isset($item['role']) && $item['role'] === $role) || (isset($item['level']) && $item['level'] == $level);
                                });
                                // Default
                                $stepStatus = 'future';
                                $iconClass = 'text-gray-300';
                                $textClass = 'text-gray-400';
                                $roleLabel = $roleLabels[$role] ?? $role;
                                $statusLabel = '';
                                $statusColor = '';
                                $icon = 'fa-user';
                                
                                // Cari user dengan role yang sesuai untuk mendapatkan avatar
                                $approverUser = null;
                                if (isset($stepData['approver_id'])) {
                                    $approverUser = \App\Models\User::find($stepData['approver_id']);
                                }
                                
                                if ($stepData) {
                                    if (isset($stepData['status']) && $stepData['status'] === 'approved') {
                                        $stepStatus = 'done';
                                        $iconClass = 'text-green-500';
                                        $textClass = 'text-green-700';
                                        $statusLabel = 'Disetujui';
                                        $statusColor = 'text-green-600';
                                        $icon = 'fa-check';
                                    } elseif (isset($stepData['status']) && $stepData['status'] === 'rejected') {
                                        $stepStatus = 'rejected';
                                        $iconClass = 'text-red-500';
                                        $textClass = 'text-red-700';
                                        $statusLabel = 'Ditolak';
                                        $statusColor = 'text-red-600';
                                        $icon = 'fa-times';
                                    } elseif (isset($stepData['status']) && str_contains($stepData['status'], 'revision')) {
                                        $stepStatus = 'revision';
                                        $iconClass = 'text-yellow-500';
                                        $textClass = 'text-yellow-700';
                                        $statusLabel = 'Revisi';
                                        $statusColor = 'text-yellow-700';
                                        $icon = 'fa-edit';
                                    } else {
                                        $stepStatus = 'done';
                                        $iconClass = 'text-green-500';
                                        $textClass = 'text-green-700';
                                        $statusLabel = 'Disetujui';
                                        $statusColor = 'text-green-600';
                                        $icon = 'fa-check';
                                    }
                                } elseif ($i === $currentIdx && $travelRequest->status !== 'completed' && $travelRequest->status !== 'rejected' && $travelRequest->status !== 'revision') {
                                    $stepStatus = 'waiting';
                                    $iconClass = 'text-blue-500';
                                    $textClass = 'text-blue-700';
                                    $statusLabel = 'Menunggu';
                                    $statusColor = 'text-blue-600';
                                    $icon = 'fa-clock';
                                }
                            @endphp
                            <div class="flex items-start space-x-3">
                                @if($stepStatus === 'done' && $approverUser)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $approverUser->avatar_url }}" alt="{{ $stepData['approved_by'] ?? 'Approver' }}" class="w-8 h-8 rounded-full object-cover border-2 border-green-500">
                                    </div>
                                @elseif($stepStatus === 'rejected' && $approverUser)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $approverUser->avatar_url }}" alt="{{ $stepData['approved_by'] ?? 'Approver' }}" class="w-8 h-8 rounded-full object-cover border-2 border-red-500">
                                    </div>
                                @elseif($stepStatus === 'revision' && $approverUser)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $approverUser->avatar_url }}" alt="{{ $stepData['approved_by'] ?? 'Approver' }}" class="w-8 h-8 rounded-full object-cover border-2 border-yellow-500">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full border-2 flex items-center justify-center
                                        @if($stepStatus === 'done') bg-green-50 border-green-500
                                        @elseif($stepStatus === 'rejected') bg-red-50 border-red-500
                                        @elseif($stepStatus === 'revision') bg-yellow-50 border-yellow-500
                                        @elseif($stepStatus === 'waiting') bg-blue-50 border-blue-500
                                        @else bg-gray-50 border-gray-300 @endif">
                                        <i class="fas {{ $icon }} text-sm {{ $iconClass }}"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium {{ $textClass }}">
                                        {{ $roleLabel }}
                                    </p>
                                    <p class="text-xs mt-1 {{ $statusColor }}">
                                        @if($stepStatus === 'done' || $stepStatus === 'rejected' || $stepStatus === 'revision')
                                            {{ $statusLabel }}
                                            @if(isset($stepData['approved_by']))
                                                oleh {{ $stepData['approved_by'] }}
                                            @endif
                                            @if(isset($stepData['approved_at']))
                                                • {{ \Carbon\Carbon::parse($stepData['approved_at'])->format('d M Y, H:i') }}
                                            @elseif(isset($stepData['timestamp']))
                                                • {{ \Carbon\Carbon::parse($stepData['timestamp'])->format('d M Y, H:i') }}
                                            @endif
                                        @elseif($stepStatus === 'waiting')
                                            Menunggu persetujuan
                                        @else
                                            {{ $roleLabel }}
                                        @endif
                                    </p>
                                    @if(($stepStatus === 'done' || $stepStatus === 'rejected' || $stepStatus === 'revision') && ((isset($stepData['notes']) && $stepData['notes']) || (isset($stepData['reason']) && $stepData['reason'])))
                                        <p class="text-xs text-gray-600 mt-1 italic">{{ $stepData['notes'] ?? $stepData['reason'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-6">Informasi Dasar</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pemohon</label>
                            <p class="text-gray-900">{{ optional($travelRequest->user)->name ?? '-' }}</p>
                            <p class="text-sm text-gray-500">{{ $travelRequest->user->role }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengajuan</label>
                            <p class="text-gray-900">{{ $travelRequest->created_at->format('d F Y, H:i') }}</p>
                        </div>

                        
                        <!-- Peserta SPPD -->
                        <div class="md:col-span-2 mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-users text-purple-600 mr-2"></i> Peserta SPPD
                            </label>
                            @php $peserta = $travelRequest->participants ?? []; @endphp
                            @if(count($peserta))
                                <div class="space-y-3">
                                    @foreach($peserta as $p)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-md">
                                            <img src="{{ $p->avatar_url }}" alt="{{ $p->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                            <div class="flex-1">
                                                <span class="font-medium text-gray-900 text-sm">{{ $p->name }}</span>
                                                <span class="text-xs text-gray-500 block">({{ $p->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $p->role }})</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-md">
                                        <img src="{{ $travelRequest->user->avatar_url }}" alt="{{ $travelRequest->user->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                        <div class="flex-1">
                                            <span class="font-medium text-gray-900 text-sm">{{ $travelRequest->user->name }}</span>
                                            <span class="text-xs text-gray-500 block">({{ $travelRequest->user->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $travelRequest->user->role }}) - Pengaju</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 italic">Tidak ada peserta tambahan - pengaju sendiri yang melakukan perjalanan dinas</p>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Saat Ini</label>
                            <p class="text-gray-900">
                                @if($travelRequest->status === 'completed')
                                    Disetujui
                                @elseif($travelRequest->status === 'rejected')
                                    Ditolak
                                @elseif($travelRequest->current_approver_role)
                                    Menunggu {{ $travelRequest->current_approver_role }}
                                @else
                                    {{ $statusText }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>


                <!-- Travel Details & Budget Information Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Travel Details -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-route text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Detail Perjalanan</h2>
                                <p class="text-sm text-gray-600">Informasi jadwal dan keperluan</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan</label>
                                <p class="text-gray-900 leading-relaxed text-sm">{{ $travelRequest->keperluan }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Perjalanan</label>
                                <p class="text-gray-900 font-medium">{{ $travelRequest->tujuan }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Berangkat</label>
                                <p class="text-gray-900 font-medium">{{ $travelRequest->tempat_berangkat }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-alt text-blue-500"></i>
                                        <span class="text-sm font-medium text-gray-700">Tanggal Keberangkatan</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($travelRequest->tanggal_berangkat)->format('d F Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-alt text-green-500"></i>
                                        <span class="text-sm font-medium text-gray-700">Tanggal Kembali</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($travelRequest->tanggal_kembali)->format('d F Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-clock text-orange-500"></i>
                                        <span class="text-sm font-medium text-gray-700">Durasi</span>
                                    </div>
                                    @php
                                        $start = \Carbon\Carbon::parse($travelRequest->tanggal_berangkat);
                                        $end = \Carbon\Carbon::parse($travelRequest->tanggal_kembali);
                                        $duration = $start->diffInDays($end) + 1;
                                    @endphp
                                    <span class="text-sm font-semibold text-gray-900">{{ $duration }} hari</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Information -->
                    @if($travelRequest->total_biaya > 0)
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Rincian Biaya</h2>
                                <p class="text-sm text-gray-600">Estimasi anggaran perjalanan</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Biaya Transportasi</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($travelRequest->biaya_transport ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Biaya Penginapan</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($travelRequest->biaya_penginapan ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Uang Harian</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($travelRequest->uang_harian ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Biaya Lainnya<br><span class=\"text-xs text-gray-500\">(misal: tol, parkir, konsumsi, ATK, dll.)</span></span>
                                <span class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($travelRequest->biaya_lainnya ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="border-t pt-3">
                                <div class="flex justify-between items-center p-3 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200">
                                    <span class="text-base font-semibold text-gray-900">Total Biaya</span>
                                    <span class="text-lg font-bold text-green-600">
                                        Rp {{ number_format($travelRequest->total_biaya ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            @if($travelRequest->sumber_dana)
                            <div class="mt-3 p-3 bg-blue-50 rounded-md border border-blue-200">
                                <label class="block text-xs font-medium text-blue-700 mb-1">Sumber Dana</label>
                                <p class="text-sm font-medium text-blue-900">{{ $travelRequest->sumber_dana }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Aksi</h3>
                    <div class="space-y-3">
                        @if($travelRequest->status === 'draft' && $travelRequest->user_id === auth()->id() && auth()->user()->role === 'kasubbag' && !$travelRequest->submitted_at)
                            <form method="POST" action="{{ route('travel-requests.submit', $travelRequest->id) }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors" onclick="return confirm('Yakin ingin mengajukan SPPD ini untuk persetujuan?')">
                                    <i class="fas fa-paper-plane mr-2"></i>Ajukan Persetujuan
                                </button>
                            </form>
                        @endif
                        @if($travelRequest->status === 'completed')
                            <button onclick="exportSPPDZIP({{ $travelRequest->id }})" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <i class="fas fa-file-archive mr-2"></i>Download ZIP
                            </button>
                            <a href="{{ route('travel-requests.download-approval', $travelRequest->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-500 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" target="_blank">
                                <img src="/images/logo.png" alt="Logo KPU" class="h-5 w-5 mr-2 inline">Download Surat Tugas
                            </a>
                        @else
                            <button class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed" title="Download hanya tersedia jika SPPD sudah disetujui" disabled>
                                <i class="fas fa-file-pdf mr-2"></i>Download (Terkunci)
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Supporting Documents -->
                @php 
                    $supportingDocuments = $travelRequest->documents->where('document_type', 'supporting');
                    $reportDocuments = $travelRequest->documents->where('document_type', 'report');
                    
                    // Cek apakah user bisa upload dokumen
                    $canUpload = auth()->check() && (
                        $travelRequest->user_id === auth()->id() || 
                        in_array(auth()->user()->role, ['admin', 'kasubbag', 'sekretaris', 'ppk'])
                    );
                    
                    // Cek apakah perjalanan sudah selesai untuk upload laporan
                    $travelCompleted = now()->startOfDay()->gte(\Carbon\Carbon::parse($travelRequest->tanggal_kembali)->startOfDay());
                @endphp
                
                <!-- Dokumen Pendukung -->
                @include('travel_requests.partials.document-upload', [
                    'title' => 'Dokumen Pendukung',
                    'description' => 'Unggah dokumen pendukung untuk perjalanan dinas ini',
                    'actionUrl' => route('travel-requests.upload-supporting', $travelRequest->id),
                    'inputName' => 'dokumen_pendukung',
                    'inputId' => 'dokumen_pendukung_input',
                    'fileListId' => 'dokumen_pendukung_list',
                    'documents' => $supportingDocuments,
                    'isEnabled' => $canUpload,
                    'disabledMessage' => 'Anda tidak memiliki akses untuk mengunggah dokumen'
                ])
                
                <!-- Laporan Perjalanan Dinas -->
                @include('travel_requests.partials.document-upload', [
                    'title' => 'Laporan Perjalanan Dinas',
                    'description' => 'Unggah laporan dan bukti perjalanan dinas setelah perjalanan selesai',
                    'actionUrl' => route('travel-requests.upload-reports', $travelRequest->id),
                    'inputName' => 'laporan_perjalanan',
                    'inputId' => 'laporan_perjalanan_input',
                    'fileListId' => 'laporan_perjalanan_list',
                    'documents' => $reportDocuments,
                    'isEnabled' => $canUpload && $travelCompleted,
                    'disabledMessage' => $travelCompleted ? 'Anda tidak memiliki akses untuk mengunggah laporan' : 'Laporan hanya dapat diunggah setelah tanggal perjalanan selesai'
                ])



                <!-- System Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">ID Request</span>
                            <span class="text-sm font-mono text-gray-900">{{ $travelRequest->id }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dibuat</span>
                            <span class="text-sm text-gray-900">{{ $travelRequest->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Terakhir Update</span>
                            <span class="text-sm text-gray-900">{{ $travelRequest->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ $travelRequest->updated_at->format('d/m/Y H:i') }}</p>
    </div>
</div>

<script>
function exportSPPDZIP(id) {
    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating ZIP...';
    button.disabled = true;

    // Simple direct download
    window.location.href = '{{ route("travel-requests.export.zip", ":id") }}'.replace(':id', id);
    
    // Restore button state after a delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 5000); // Longer timeout for ZIP generation
}


</script>
<script>
function goBackOrIndex(e) {
    e.preventDefault();
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = "{{ route('travel-requests.index') }}";
    }
}
</script>

<style>
.status-in_review {
    @apply bg-blue-100 text-blue-800;
}
.status-revision {
    @apply bg-yellow-100 text-yellow-800;
}
.status-rejected {
    @apply bg-red-100 text-red-800;
}
.status-completed {
    @apply bg-green-100 text-green-800;
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endsection
