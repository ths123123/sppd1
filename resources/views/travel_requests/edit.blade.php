@extends('layouts.app')

@section('title', 'Edit SPPD')

@push('styles')
<link rel="stylesheet" href="/css/pages/sppd-form-professional.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
@php $user = Auth::user(); @endphp
<div class="sppd-container">
    <div class="sppd-card max-w-5xl mx-auto">
        <div class="sppd-header">
            <h1 class="text-lg font-bold sppd-title">Edit SPPD</h1>
            <p class="sppd-subtitle">Lakukan revisi pengajuan perjalanan dinas sesuai permintaan.</p>
            <div class="mt-2">
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                    Status: Revisi
                </span>
            </div>
        </div>
        <div class="sppd-form-content">
            @if ($errors->any())
                <div class="alert-error">
                    <strong>Terjadi kesalahan:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="sppd-form" method="POST" action="{{ route('travel-requests.update', $travelRequest->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Data Pemohon & Peserta SPPD (Gabung 1 Card) -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-user"></i></div>
                        <div>
                            <h3 class="text-base font-semibold section-title">Data Pemohon & Peserta SPPD</h3>
                            <p class="section-description">Identitas kasubbag pengaju dan peserta perjalanan dinas</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" class="form-input" value="{{ $user->name }}" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">NIP</label>
                            <input type="text" class="form-input" value="{{ $user->nip }}" readonly>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:18px;">
                        <div class="flex justify-center mt-2 mb-3">
                            <a href="#" class="btn flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors shadow-md" id="btn-pilih-peserta" data-modal-target="peserta-modal">
                                <i class="fas fa-users mr-2"></i> Pilih Peserta
                            </a>
                        </div>
                        <input type="hidden" name="participants[]" id="participants-hidden">
                        <div id="peserta-terpilih-table" class="mt-3"></div>
                    </div>
                </div>

                <!-- Data Perjalanan -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-route"></i></div>
                        <div>
                            <h3 class="text-base font-semibold section-title">Data Perjalanan</h3>
                            <p class="section-description">Detail perjalanan dinas</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Tempat Berangkat</label>
                            <input type="text" name="tempat_berangkat" class="form-input" required value="{{ old('tempat_berangkat', $travelRequest->tempat_berangkat) }}" placeholder="Masukkan tempat berangkat">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Tujuan</label>
                            <input type="text" name="tujuan" class="form-input" required value="{{ old('tujuan', $travelRequest->tujuan) }}" placeholder="Masukkan tujuan perjalanan">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Keperluan</label>
                            <input type="text" name="keperluan" class="form-input" required value="{{ old('keperluan', $travelRequest->keperluan) }}" placeholder="Masukkan keperluan perjalanan">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Tanggal Berangkat</label>
                            <input type="date" name="tanggal_berangkat" class="form-input" required value="{{ old('tanggal_berangkat', $travelRequest->tanggal_berangkat->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" class="form-input" required value="{{ old('tanggal_kembali', $travelRequest->tanggal_kembali->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Transportasi</label>
                            <select name="transportasi" class="form-select" required>
                                <option value="Pesawat" {{ old('transportasi', $travelRequest->transportasi) == 'Pesawat' ? 'selected' : '' }}>Pesawat</option>
                                <option value="Kereta Api" {{ old('transportasi', $travelRequest->transportasi) == 'Kereta Api' ? 'selected' : '' }}>Kereta Api</option>
                                <option value="Bus" {{ old('transportasi', $travelRequest->transportasi) == 'Bus' ? 'selected' : '' }}>Bus</option>
                                <option value="Kendaraan Dinas" {{ old('transportasi', $travelRequest->transportasi) == 'Kendaraan Dinas' ? 'selected' : '' }}>Kendaraan Dinas</option>
                                <option value="Kendaraan Pribadi" {{ old('transportasi', $travelRequest->transportasi) == 'Kendaraan Pribadi' ? 'selected' : '' }}>Kendaraan Pribadi</option>
                                <option value="Lainnya" {{ old('transportasi', $travelRequest->transportasi) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tempat Menginap</label>
                            <input type="text" name="tempat_menginap" class="form-input" value="{{ old('tempat_menginap', $travelRequest->tempat_menginap) }}" placeholder="Nama hotel/penginapan (opsional)">
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen Pendukung -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-paperclip"></i></div>
                        <div>
                            <h3 class="text-base font-semibold section-title">Dokumen Pendukung</h3>
                            <p class="section-description">Upload file pendukung perjalanan dinas (PDF/JPG/PNG, max 2MB per file)</p>
                        </div>
                    </div>
                    <div class="form-group" style="padding: 24px;">
                        <label class="form-label">Upload Dokumen</label>
                        <input type="file" name="dokumen_pendukung[]" class="form-input" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <div class="participants-help">Bisa upload lebih dari satu file. Format: PDF, JPG, PNG. Maksimal 2MB per file.</div>

                        @if($travelRequest->documents && $travelRequest->documents->count() > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-semibold mb-2">Dokumen yang sudah diupload:</h4>
                                <ul class="list-disc pl-5">
                                    @foreach($travelRequest->documents as $document)
                                        <li class="text-sm text-gray-700">{{ $document->original_filename }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Anggaran & Biaya -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-calculator"></i></div>
                        <div>
                            <h3 class="text-base font-semibold section-title">Anggaran & Biaya</h3>
                            <p class="section-description">Rincian biaya perjalanan dinas</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Biaya Transportasi</label>
                            <input type="text" id="biaya_transport" name="biaya_transport" class="form-input biaya-input" min="0" value="{{ old('biaya_transport', (int)$travelRequest->biaya_transport) }}" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Biaya Penginapan</label>
                            <input type="text" id="biaya_penginapan" name="biaya_penginapan" class="form-input biaya-input" min="0" value="{{ old('biaya_penginapan', (int)$travelRequest->biaya_penginapan) }}" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Uang Harian</label>
                            <input type="text" id="uang_harian" name="uang_harian" class="form-input biaya-input" min="0" value="{{ old('uang_harian', (int)$travelRequest->uang_harian) }}" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Biaya Lainnya</label>
                            <input type="text" id="biaya_lainnya" name="biaya_lainnya" class="form-input biaya-input" min="0" value="{{ old('biaya_lainnya', (int)$travelRequest->biaya_lainnya) }}" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sumber Dana</label>
                            <select name="sumber_dana" class="form-select">
                                <option value="">Pilih Sumber Dana</option>
                                <option value="APBD" {{ old('sumber_dana', $travelRequest->sumber_dana) == 'APBD' ? 'selected' : '' }}>APBD</option>
                                <option value="APBN" {{ old('sumber_dana', $travelRequest->sumber_dana) == 'APBN' ? 'selected' : '' }}>APBN</option>
                                <option value="Dana Lainnya" {{ old('sumber_dana', $travelRequest->sumber_dana) == 'Dana Lainnya' ? 'selected' : '' }}>Dana Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Catatan Revisi -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-comment-alt"></i></div>
                        <div>
                            <h3 class="text-base font-semibold section-title">Catatan Revisi</h3>
                            <p class="section-description">Informasi terkait permintaan revisi</p>
                        </div>
                    </div>
                    <div class="p-4">
                        @php
                            $history = is_string($travelRequest->approval_history)
                                ? json_decode($travelRequest->approval_history, true)
                                : $travelRequest->approval_history;
                            $revisionNote = '';
                            if ($history && is_array($history)) {
                                foreach ($history as $item) {
                                    if (isset($item['status']) && $item['status'] === 'revision') {
                                        $revisionNote = $item['notes'] ?? $item['reason'] ?? '';
                                        break;
                                    }
                                }
                            }
                        @endphp

                        @if($revisionNote)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Catatan Revisi:</strong> {{ $revisionNote }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 italic">Tidak ada catatan revisi spesifik.</p>
                        @endif

                        <div class="form-group mt-4">
                            <label class="form-label">Catatan Pemohon</label>
                            <textarea name="catatan_pemohon" class="form-input" rows="3" placeholder="Tambahkan catatan untuk perubahan yang dilakukan (opsional)">{{ old('catatan_pemohon', $travelRequest->catatan_pemohon) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="submit-section flex gap-4">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i>
                        Simpan Revisi
                    </button>
                </div>
            </form>
            <form method="POST" action="{{ route('travel-requests.submit', $travelRequest->id) }}" class="mt-4">
                @csrf
                <button type="submit" class="submit-btn bg-indigo-600 text-white hover:bg-indigo-700">
                    <i class="fas fa-paper-plane"></i>
                    Ajukan Ulang
                </button>
            </form>
        </div>
    </div>
</div>
@include('travel_requests.partials.peserta-modal', ['users' => $users, 'selected' => old('participants', $travelRequest->participants->pluck('id')->toArray())])
@endsection

@push('scripts')
<script>
    window.users = @json($users);
    window.selectedPeserta = @json(old('participants', $travelRequest->participants->pluck('id')->toArray()));
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/forms/sppd-form-professional.js') }}"></script>
@endpush
