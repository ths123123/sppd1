@extends('layouts.app')

@section('title', 'Buat SPPD Baru')

@push('styles')
<link rel="stylesheet" href="/css/pages/sppd-form-professional.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
@php $user = Auth::user(); @endphp
<div class="sppd-container">
    
    <div class="sppd-card max-w-5xl mx-auto">
        <div class="sppd-header">
            <h1 class="text-lg font-bold sppd-title">Buat SPPD Baru</h1>
            <p class="sppd-subtitle">Isi data pengajuan perjalanan dinas dengan lengkap dan benar.</p>
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
            <form id="sppd-form" method="POST" action="{{ route('travel-requests.store') }}" enctype="multipart/form-data">
        @csrf
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
                            <input type="text" class="form-input" value="{{ $user->name }}" readonly aria-label="Nama Lengkap">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">NIP</label>
                            <input type="text" class="form-input" value="{{ $user->nip }}" readonly aria-label="NIP">
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
                            <input type="text" name="tempat_berangkat" class="form-input" required value="{{ old('tempat_berangkat', 'Cirebon (Sumber)') }}" placeholder="Masukkan tempat berangkat">
            </div>
                        <div class="form-group">
                            <label class="form-label required">Tujuan</label>
                            <input type="text" name="tujuan" class="form-input" required value="{{ old('tujuan') }}" placeholder="Masukkan tujuan perjalanan">
            </div>
                        <div class="form-group">
                            <label class="form-label required">Keperluan</label>
                            <input type="text" name="keperluan" class="form-input" required value="{{ old('keperluan') }}" placeholder="Masukkan keperluan perjalanan">
            </div>
                        <div class="form-group">
                            <label class="form-label required">Tanggal Berangkat</label>
                            <input type="date" name="tanggal_berangkat" class="form-input" required value="{{ old('tanggal_berangkat') }}" aria-label="Tanggal Berangkat">
            </div>
                        <div class="form-group">
                            <label class="form-label required">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" class="form-input" required value="{{ old('tanggal_kembali') }}" aria-label="Tanggal Kembali">
            </div>
                        <div class="form-group">
                            <label class="form-label required">Transportasi</label>
                            <select name="transportasi" class="form-select" required aria-label="Pilih Transportasi">
                    <option value="">Pilih Transportasi</option>
                                <option value="Pesawat" {{ old('transportasi') == 'Pesawat' ? 'selected' : '' }}>Pesawat</option>
                                <option value="Kereta Api" {{ old('transportasi') == 'Kereta Api' ? 'selected' : '' }}>Kereta Api</option>
                                <option value="Bus" {{ old('transportasi') == 'Bus' ? 'selected' : '' }}>Bus</option>
                                <option value="Kendaraan Dinas" {{ old('transportasi') == 'Kendaraan Dinas' ? 'selected' : '' }}>Kendaraan Dinas</option>
                                <option value="Kendaraan Pribadi" {{ old('transportasi') == 'Kendaraan Pribadi' ? 'selected' : '' }}>Kendaraan Pribadi</option>
                                <option value="Lainnya" {{ old('transportasi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tempat Menginap</label>
                            <input type="text" name="tempat_menginap" class="form-input" value="{{ old('tempat_menginap') }}" placeholder="Nama hotel/penginapan (opsional)">
                        </div>
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
                            <input type="text" id="biaya_transport" name="biaya_transport" class="form-input biaya-input" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Biaya Penginapan</label>
                            <input type="text" id="biaya_penginapan" name="biaya_penginapan" class="form-input biaya-input" placeholder="0">
            </div>
                        <div class="form-group">
                            <label class="form-label">Uang Harian</label>
                            <input type="text" id="uang_harian" name="uang_harian" class="form-input biaya-input" placeholder="0">
            </div>
                        <div class="form-group">
                            <label class="form-label">Biaya Lainnya</label>
                            <input type="text" id="biaya_lainnya" name="biaya_lainnya" class="form-input biaya-input" placeholder="0">
            </div>
                        <div class="form-group">
                            <label class="form-label">Total Biaya</label>
                            <input type="text" id="total_biaya" name="total_biaya" class="form-input" placeholder="0" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sumber Dana</label>
                            <select name="sumber_dana" class="form-select" aria-label="Pilih Sumber Dana">
                    <option value="">Pilih Sumber Dana</option>
                                <option value="APBD" {{ old('sumber_dana') == 'APBD' ? 'selected' : '' }}>APBD</option>
                                <option value="APBN" {{ old('sumber_dana') == 'APBN' ? 'selected' : '' }}>APBN</option>
                                <option value="Dana Lainnya" {{ old('sumber_dana') == 'Dana Lainnya' ? 'selected' : '' }}>Dana Lainnya</option>
                </select>
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
                        <input type="file" name="dokumen_pendukung[]" class="form-input" multiple accept=".pdf,.jpg,.jpeg,.png" aria-label="Upload Dokumen Pendukung">
                        <div class="participants-help">Bisa upload lebih dari satu file. Format: PDF, JPG, PNG. Maksimal 2MB per file.</div>
                    </div>
            </div>
                <!-- Tombol Submit -->
                <div class="submit-section">
                    <!-- Perbaiki button submit -->
                    <button type="submit" class="submit-btn" aria-label="Ajukan SPPD">
                        <i class="fas fa-paper-plane"></i>
                        Ajukan SPPD
            </button>
        </div>
    </form>
        </div>
    </div>
</div>
<div class="w-full text-center mt-8 mb-4">
    <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
</div>
@include('travel_requests.partials.peserta-modal', ['users' => $users, 'selected' => old('participants', [])])
@endsection

@push('scripts')
<script>
    window.users = @json($users);
    window.selectedPeserta = @json(old('participants', []));
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/forms/sppd-form-professional.js') }}"></script>
@endpush