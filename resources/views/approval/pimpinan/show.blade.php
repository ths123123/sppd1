@extends('layouts.app')

@section('content')
<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header with Back Button -->
    <div class="mb-6 fade-in">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('approval.pimpinan.index') }}"
                   class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan SPPD</h1>
                    <p class="text-sm text-gray-600">
                        @if($request->status === 'completed')
                            {{ $request->kode_sppd }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if($request->is_urgent)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Urgent
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($request->status === 'in_review') bg-yellow-100 text-yellow-800
                    @elseif($request->status === 'revision') bg-orange-100 text-orange-800
                    @elseif($request->status === 'completed') bg-green-100 text-green-800
                    @elseif($request->status === 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Bar Informasi Detail Approval -->
    <div class="glass-card rounded-xl p-6 mb-6 fade-in border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">Informasi Detail Persetujuan SPPD</h2>
                <p class="text-gray-700 text-base">
                    Halaman ini menampilkan detail pengajuan SPPD yang sedang Anda review atau setujui. Pastikan semua data sudah benar sebelum melakukan aksi.
                </p>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Staff -->
            <div class="glass-card p-6 fade-in">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Informasi Staff</h2>
                        <p class="text-sm text-gray-600">Data pemohon SPPD</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nama Lengkap</label>
                            <p class="text-base font-semibold text-gray-900">{{ strtoupper($request->user->name ?? '-') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">NIP</label>
                            <p class="text-base text-gray-900">{{ $request->user->nip ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jabatan</label>
                            <p class="text-base text-gray-900">{{ $request->user->jabatan ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Unit Kerja</label>
                            <p class="text-base text-gray-900">{{ $request->user->unit_kerja ?? 'KPU Kabupaten Cirebon' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Approval -->
            <x-approval-progress :travel-request="$request" />

            <!-- Informasi Perjalanan -->
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.1s;">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-map-marker-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Informasi Perjalanan</h2>
                        <p class="text-sm text-gray-600">Detail tujuan dan keperluan</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tujuan Perjalanan</label>
                            <p class="text-base font-semibold text-gray-900">{{ $request->tujuan }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Transportasi</label>
                            <p class="text-base text-gray-900">{{ ucfirst($request->transportasi ?? '-') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Keperluan/Maksud Perjalanan</label>
                        <p class="text-base text-gray-900 bg-gray-50 p-3 rounded-md">{{ $request->keperluan }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Berangkat</label>
                            <p class="text-base font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($request->tanggal_berangkat)->format('l') }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Kembali</label>
                            <p class="text-base font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($request->tanggal_kembali)->format('l') }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Durasi</label>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ $request->lama_perjalanan ?? \Carbon\Carbon::parse($request->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($request->tanggal_kembali)) + 1 }} hari
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($request->tempat_menginap)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Tempat Menginap</label>
                        <p class="text-base text-gray-900">{{ $request->tempat_menginap }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Rincian Biaya -->
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-yellow-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-money-bill-wave text-white text-lg"></i>
                    </div>                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Rincian Biaya</h2>
                        <p class="text-sm text-gray-600">Estimasi anggaran perjalanan dinas</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Biaya Transportasi</span>
                                <span class="text-base font-semibold text-gray-900">
                                    Rp {{ number_format($request->biaya_transport ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Biaya Penginapan</span>
                                <span class="text-base font-semibold text-gray-900">
                                    Rp {{ number_format($request->biaya_penginapan ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Uang Harian</span>
                                <span class="text-base font-semibold text-gray-900">
                                    Rp {{ number_format($request->uang_harian ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                                <span class="text-sm font-medium text-gray-700">Biaya Lainnya</span>
                                <span class="text-base font-semibold text-gray-900">
                                    Rp {{ number_format($request->biaya_lainnya ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
                            <span class="text-lg font-semibold text-gray-900">Total Biaya</span>
                            <span class="text-xl font-bold text-indigo-600">
                                Rp {{ number_format($request->total_biaya ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @if($request->sumber_dana)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Sumber Dana</label>
                        <p class="text-base text-gray-900">{{ $request->sumber_dana }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Dokumen Pendukung -->
            @if($request->documents && $request->documents->count())
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.3s;">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-file-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Dokumen Pendukung</h2>
                        <p class="text-sm text-gray-600">{{ $request->documents->count() }} dokumen terlampir</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($request->documents as $doc)
                    <a href="{{ asset('storage/' . $doc->file_path) }}"
                       target="_blank"
                       class="flex items-center p-3 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-download text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->original_filename }}</p>
                            <p class="text-xs text-gray-500">
                                {{ \Illuminate\Support\Str::upper(pathinfo($doc->original_filename, PATHINFO_EXTENSION)) }} •
                                {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                            </p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Catatan -->
            @if($request->catatan_pemohon || $request->catatan_approval)
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.4s;">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-sticky-note text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Catatan</h2>
                        <p class="text-sm text-gray-600">Informasi tambahan</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @if($request->catatan_pemohon)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Catatan Pemohon</label>
                        <p class="text-base text-gray-900 bg-blue-50 p-3 rounded-md">{{ $request->catatan_pemohon }}</p>
                    </div>
                    @endif
                    @if($request->catatan_approval)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Catatan Approval</label>
                        <p class="text-base text-gray-900 bg-yellow-50 p-3 rounded-md">{{ $request->catatan_approval }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Action Buttons -->
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.5s;">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Aksi Approval</h3>
                <div class="space-y-3">
                    @php
                        $isApproverParticipant = $request->participants->contains('id', auth()->user()->id);
                    @endphp
                    @if(auth()->user()->role !== 'admin')
                    <form method="POST" action="{{ route('approval.pimpinan.approve', $request->id) }}" class="w-full">
                        @csrf
                        @if($isApproverParticipant)
                            <div class="mb-4">
                                <label for="plt_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Plt/Plh Approver <span class="text-red-500">*</span></label>
                                <input type="text" name="plt_name" id="plt_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" required placeholder="Masukkan nama Plt/Plh yang menandatangani">
                                <p class="text-xs text-gray-500 mt-1">Anda adalah peserta SPPD ini. Approval harus dilakukan oleh Plt/Plh.</p>
                            </div>
                        @endif
                        <button type="submit"
                                onclick="return confirm('Setujui pengajuan SPPD untuk {{ $request->user->name ?? '-' }}?\n\nTujuan: {{ $request->tujuan }}\nTotal Biaya: Rp {{ number_format($request->total_biaya ?? 0, 0, ',', '.') }}')"
                                class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-check mr-2"></i>Setujui SPPD
                        </button>
                    </form>
                    @if($request->status === 'in_review')
                    <button type="button"
                            onclick="openRevisionModal()"
                            class="w-full inline-flex items-center justify-center px-4 py-3 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Minta Revisi
                    </button>
                    @endif
                    @endif

                    @if(auth()->user()->role !== 'admin')
                    <button type="button"
                            onclick="openRejectModal()"
                            class="w-full inline-flex items-center justify-center px-4 py-3 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-times mr-2"></i>Tolak SPPD
                    </button>
                    @endif
                </div>
            </div>

            <!-- Progress Approval -->
            <x-approval-progress :travel-request="$request" />

            <!-- Riwayat Detail Approval -->
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.6s;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-history mr-2 text-gray-500"></i>Riwayat Detail Approval
                </h3>
                <div class="space-y-3">
                    @if($request->approvals && $request->approvals->count())
                        @foreach($request->approvals->sortBy('level') as $approval)
                        <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                @if($approval->status === 'completed') bg-green-100 text-green-600
                                @elseif($approval->status === 'rejected') bg-red-100 text-red-600
                                @elseif($approval->status === 'revision') bg-yellow-100 text-yellow-600
                                @else bg-gray-100 text-gray-600 @endif">
                                @if($approval->status === 'completed')
                                    <i class="fas fa-check"></i>
                                @elseif($approval->status === 'rejected')
                                    <i class="fas fa-times"></i>
                                @elseif($approval->status === 'revision')
                                    <i class="fas fa-edit"></i>
                                @else
                                    <i class="fas fa-clock"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $approval->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $approval->role }}</p>
                                        <p class="text-sm text-gray-600">{{ $approval->approver->name ?? '-' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($approval->status === 'completed') bg-green-100 text-green-800
                                            @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                            @elseif($approval->status === 'revision') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($approval->status) }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($approval->approved_at)
                                        Disetujui: {{ \Carbon\Carbon::parse($approval->approved_at)->format('d M Y H:i') }}
                                    @elseif($approval->rejected_at)
                                        Ditolak: {{ \Carbon\Carbon::parse($approval->rejected_at)->format('d M Y H:i') }}
                                    @else
                                        Dibuat: {{ \Carbon\Carbon::parse($approval->created_at)->format('d M Y H:i') }}
                                    @endif
                                </p>
                                @if($approval->comments)
                                <div class="mt-2 p-2 bg-blue-50 rounded-md">
                                    <p class="text-xs text-gray-700 font-medium">Komentar:</p>
                                    <p class="text-sm text-gray-800 mt-1">{{ $approval->comments }}</p>
                                </div>
                                @endif
                                @if($approval->rejection_reason)
                                <div class="mt-2 p-2 bg-red-50 rounded-md">
                                    <p class="text-xs text-red-700 font-medium">Alasan Penolakan:</p>
                                    <p class="text-sm text-red-800 mt-1">{{ $approval->rejection_reason }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-sm text-gray-500">Belum ada riwayat approval</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info Submission -->
            <div class="glass-card p-6 fade-in" style="animation-delay: 0.7s;">
                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-gray-500 text-base"></i>Informasi Pengajuan
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Diajukan Pada</label>
                        <p class="text-sm text-gray-900">
                            {{ $request->submitted_at ? \Carbon\Carbon::parse($request->submitted_at)->format('d M Y H:i') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Terakhir Update</label>
                        {{-- <p class="text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($request->updated_at)->format('d M Y H:i') }}
                        </p> --}}
                    </div>
                    @if($request->nomor_surat_tugas)
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Nomor Surat Tugas</label>
                        <p class="text-sm text-gray-900">{{ $request->nomor_surat_tugas }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ \Carbon\Carbon::parse($request->updated_at)->format('d M Y H:i') }}</p>
    </div>
</main>

<!-- Modal Revisi -->
<div id="revisionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="revision-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 modal-backdrop transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle modal-content text-left overflow-hidden transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('approval.pimpinan.revision', $request->id) }}">
                @csrf
                <div class="glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-2">
                                Minta Revisi SPPD
                            </h3>
                            <p class="text-sm text-gray-600">Ajukan revisi untuk <span class="font-medium">{{ $request->user->name ?? '-' }}</span></p>
                        </div>
                        <button type="button" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-gray-100 transition-colors" onclick="closeRevisionModal()">
                            <svg class="w-8 h-8" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                                <line x1="6" y1="6" x2="18" y2="18" />
                                <line x1="6" y1="18" x2="18" y2="6" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="target" class="block text-sm font-medium text-gray-700 mb-2">
                                Kirim Revisi Ke:
                            </label>
                            <select name="target" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                            <textarea name="revision_reason" rows="4" required minlength="10"
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

<!-- Modal Penolakan -->
<div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="reject-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 modal-backdrop transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
        <div class="inline-block align-middle modal-content text-left overflow-hidden transform transition-all sm:my-8 sm:max-w-md sm:w-full">
            <form method="POST" action="{{ route('approval.pimpinan.reject', $request->id) }}">
                @csrf
                <div class="px-4 pt-4 pb-3">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 mb-2">
                                Tolak Permintaan SPPD
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">
                                Apakah Anda yakin ingin menolak permintaan SPPD untuk {{ $request->user->name ?? '-' }}?
                            </p>
                            <div>
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea name="rejection_reason" rows="3" required minlength="10"
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
@endsection

@push('scripts')
<script>
    function openRevisionModal() {
        document.getElementById('revisionModal').classList.remove('hidden');
    }

    function closeRevisionModal() {
        document.getElementById('revisionModal').classList.add('hidden');
    }

    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        const revisionModal = document.getElementById('revisionModal');
        const rejectModal = document.getElementById('rejectModal');

        if (event.target === revisionModal) {
            closeRevisionModal();
        }

        if (event.target === rejectModal) {
            closeRejectModal();
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeRevisionModal();
            closeRejectModal();
        }
    });
</script>
@endpush
