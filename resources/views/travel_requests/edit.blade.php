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
        
        <!-- Pesan Revisi -->
        @php
            $latestRevision = $travelRequest->approvals()
                ->whereIn('status', ['revision', 'revision_minor'])
                ->orderBy('created_at', 'desc')
                ->first();
        @endphp
        
        @if($latestRevision)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        Pesan Revisi dari {{ $latestRevision->approver->name ?? 'Approver' }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p class="font-medium">Alasan Revisi:</p>
                        <p class="mt-1">{{ $latestRevision->comments ?? 'Tidak ada pesan revisi' }}</p>
                    </div>
                    <div class="mt-2 text-xs text-red-600">
                        <i class="fas fa-clock mr-1"></i>
                        {{ $latestRevision->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
        @endif
        
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
                        <input type="hidden" name="participants[]" id="participants-hidden" value="{{ $travelRequest->participants ? $travelRequest->participants->pluck('id')->implode(',') : '' }}">
                        <!-- Tambahan hidden inputs untuk setiap peserta -->
                        @if($travelRequest->participants)
                            @foreach($travelRequest->participants as $participant)
                                <input type="hidden" name="participants[]" value="{{ $participant->id }}">
                            @endforeach
                        @endif
                        
                        <!-- Tampilan Peserta yang Sudah Dipilih -->
                        <div id="peserta-terpilih-table" class="mt-3">
                            @if($travelRequest->participants && $travelRequest->participants->count() > 0)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-blue-800 mb-2">
                                        <i class="fas fa-users mr-1"></i> Peserta yang Sudah Dipilih ({{ $travelRequest->participants->count() }})
                                    </h4>
                                    <div class="space-y-2">
                                        @foreach($travelRequest->participants as $participant)
                                            <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-blue-100">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium mr-3">
                                                        {{ substr($participant->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $participant->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $participant->role }}</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="text-red-500 hover:text-red-700 remove-peserta-btn" data-participant-id="{{ $participant->id }}" title="Hapus peserta">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-blue-600 mt-2">
                                        <i class="fas fa-info-circle mr-1"></i> Klik tombol "Pilih Peserta" untuk menambah peserta baru
                                    </p>
                                </div>
                            @else
                                <div class="text-center text-blue-500 py-4">
                                    <i class="fas fa-user text-2xl mb-2"></i>
                                    <p class="font-medium">Tidak ada peserta tambahan</p>
                                    <p class="text-sm text-gray-500 mt-1">Anda sendiri yang akan melakukan perjalanan dinas</p>
                                </div>
                            @endif
                        </div>
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
                                <div class="flex items-start">
                                    @php
                                        $revisionData = null;
                                        if ($history && is_array($history)) {
                                            foreach ($history as $item) {
                                                if (isset($item['status']) && $item['status'] === 'revision') {
                                                    $revisionData = $item;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($revisionData && isset($revisionData['approver_avatar']))
                                        <div class="flex-shrink-0 mr-3">
                                            <img src="{{ $revisionData['approver_avatar'] }}" alt="{{ $revisionData['approved_by'] ?? 'Approver' }}" class="w-8 h-8 rounded-full object-cover border border-yellow-400">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 mr-3">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Catatan Revisi dari {{ $revisionData['approved_by'] ?? 'Approver' }}:</strong>
                                        </p>
                                        <p class="text-sm text-yellow-700 mt-1">{{ $revisionNote }}</p>
                                        @if(isset($revisionData['timestamp']))
                                            <p class="text-xs text-yellow-600 mt-1">{{ \Carbon\Carbon::parse($revisionData['timestamp'])->format('d M Y, H:i') }}</p>
                                        @endif
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
            <form method="POST" action="{{ route('travel-requests.submit', $travelRequest->id) }}" class="mt-4" id="submit-form">
                @csrf
                <!-- Hidden inputs untuk peserta akan di-update oleh JavaScript secara dinamis -->
                <button type="submit" class="submit-btn bg-indigo-600 text-white hover:bg-indigo-700">
                    <i class="fas fa-paper-plane"></i>
                    Ajukan Ulang
                </button>
            </form>
        </div>
    </div>
</div>
@include('travel_requests.partials.peserta-modal', ['users' => $users, 'selected' => old('participants', $travelRequest->participants ? $travelRequest->participants->pluck('id')->toArray() : [])])
@endsection

@push('scripts')
<script>
    window.users = @json($users);
    window.selectedPeserta = @json(old('participants', $travelRequest->participants ? $travelRequest->participants->pluck('id')->toArray() : []));
    
    // Debug: Log data peserta untuk memastikan ter-load dengan benar
    console.log('Debug - Travel Request ID:', @json($travelRequest->id));
    console.log('Debug - Travel Request Participants:', @json($travelRequest->participants));
    console.log('Debug - Selected Participants:', window.selectedPeserta);
    console.log('Debug - Users Count:', window.users.length);
    
    // Ensure participants are properly initialized
    document.addEventListener('DOMContentLoaded', function() {
        const participantsHidden = document.getElementById('participants-hidden');
        const pesertaTable = document.getElementById('peserta-terpilih-table');
        
        console.log('Debug - Hidden Input Value:', participantsHidden ? participantsHidden.value : 'No hidden input found');
        console.log('Debug - Peserta Table Element:', pesertaTable);
        
        // Force refresh peserta table if needed
        if (window.selectedPeserta && window.selectedPeserta.length > 0) {
            console.log('Debug - Found selected participants, ensuring they are displayed');
            // Trigger a custom event to refresh the table
            const event = new CustomEvent('refreshPesertaTable', { 
                detail: { participants: window.selectedPeserta } 
            });
            document.dispatchEvent(event);
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/forms/sppd-form-professional.js') }}"></script>
<script>
$(document).ready(function() {
    // Handle remove participant button
    $(document).on('click', '.remove-peserta-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const participantId = $(this).data('participant-id');
        const participantCard = $(this).closest('.flex.items-center.justify-between');
        
        console.log('Debug - Remove button clicked for participant ID:', participantId);
        console.log('Debug - Before removal, selectedPeserta:', window.selectedPeserta);
        console.log('Debug - Before removal, selectedPeserta type:', typeof window.selectedPeserta);
        
        // Ensure selectedPeserta is an array
        if (!Array.isArray(window.selectedPeserta)) {
            console.log('Debug - selectedPeserta is not an array, converting...');
            window.selectedPeserta = window.selectedPeserta ? [window.selectedPeserta] : [];
        }
        
        // Remove from selectedPeserta array
        window.selectedPeserta = window.selectedPeserta.filter(id => id != participantId);
        
        console.log('Debug - After removal, selectedPeserta:', window.selectedPeserta);
        console.log('Debug - After removal, selectedPeserta type:', typeof window.selectedPeserta);
        
        // Update hidden input
        $('#participants-hidden').val(window.selectedPeserta.join(','));
        console.log('Debug - Updated hidden input value:', $('#participants-hidden').val());
        
        // Update hidden inputs for form submission
        updateHiddenInputs(window.selectedPeserta);
        
        // Remove the card with animation
        participantCard.fadeOut(300, function() {
            $(this).remove();
            
            // Update participant count
            const remainingParticipants = $('.remove-peserta-btn').length;
            const countElement = $('h4:contains("Peserta yang Sudah Dipilih")');
            if (countElement.length) {
                countElement.text(`Peserta yang Sudah Dipilih (${remainingParticipants})`);
            }
            
            // Show "no participants" message if none left
            if (remainingParticipants === 0) {
                $('#peserta-terpilih-table').html(`
                    <div class="text-center text-blue-500 py-4">
                        <i class="fas fa-user text-2xl mb-2"></i>
                        <p class="font-medium">Tidak ada peserta tambahan</p>
                        <p class="text-sm text-gray-500 mt-1">Anda sendiri yang akan melakukan perjalanan dinas</p>
                    </div>
                `);
            }
            
            console.log('Debug - Participant removed successfully. Remaining count:', remainingParticipants);
            console.log('Debug - Final selectedPeserta after removal:', window.selectedPeserta);
        });
    });
    
    // Update hidden input when modal adds new participants
    $(document).on('participantsUpdated', function(e, newParticipants) {
        console.log('Debug - participantsUpdated event triggered');
        console.log('Debug - Event detail:', e.detail);
        console.log('Debug - New participants:', newParticipants);
        console.log('Debug - Current selectedPeserta before update:', window.selectedPeserta);
        
        // Extract participants from event detail if available
        if (e.detail && e.detail.participants !== undefined) {
            newParticipants = e.detail.participants;
            console.log('Debug - Extracted participants from event detail:', newParticipants);
        }
        
        // Handle undefined or null newParticipants
        if (newParticipants === undefined || newParticipants === null) {
            console.log('Debug - newParticipants is undefined/null, using empty array');
            newParticipants = [];
        }
        
        // Ensure newParticipants is an array
        if (!Array.isArray(newParticipants)) {
            console.log('Debug - newParticipants is not an array, converting to array');
            newParticipants = [newParticipants].filter(item => item !== undefined && item !== null);
        }
        
        // Merge existing participants with new participants (preserve existing data)
        const existingParticipants = window.selectedPeserta || [];
        const mergedParticipants = [...new Set([...existingParticipants, ...newParticipants])];
        
        console.log('Debug - Existing participants:', existingParticipants);
        console.log('Debug - New participants:', newParticipants);
        console.log('Debug - Merged participants:', mergedParticipants);
        
        window.selectedPeserta = mergedParticipants;
        $('#participants-hidden').val(mergedParticipants.join(','));
        
        // Update hidden inputs for form submission
        updateHiddenInputs(mergedParticipants);
        
        console.log('Debug - Updated window.selectedPeserta:', window.selectedPeserta);
        console.log('Debug - Updated hidden input value:', $('#participants-hidden').val());
    });
    
    // Function to update hidden inputs
    function updateHiddenInputs(participants) {
        // Remove existing hidden inputs
        $('input[name="participants[]"]').not('#participants-hidden').remove();
        
        // Add new hidden inputs for each participant
        participants.forEach(function(participantId) {
            if (participantId && participantId.toString().trim() !== '') {
                const input = $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'participants[]')
                    .val(participantId.toString().trim());
                $('#participants-hidden').after(input);
            }
        });
        
        console.log('Debug - Updated hidden inputs:', participants);
    }
    
    // Handle form submission to ensure participants data is sent
    $('#sppd-form').on('submit', function(e) {
        console.log('Debug - Form submission started');
        console.log('Debug - Current selectedPeserta:', window.selectedPeserta);
        
        // Ensure hidden inputs are up to date
        updateHiddenInputs(window.selectedPeserta);
        
        // Log all hidden inputs before submission
        const hiddenInputs = $('input[name="participants[]"]');
        console.log('Debug - Hidden inputs before submission:', hiddenInputs.length);
        hiddenInputs.each(function(index) {
            console.log(`Debug - Hidden input ${index}:`, $(this).val());
        });
        
        // Continue with form submission
        console.log('Debug - Proceeding with form submission');
    });
    
    // Handle submit form submission to ensure participants data is sent
    $('#submit-form').on('submit', function(e) {
        console.log('Debug - Submit form submission started');
        console.log('Debug - Current selectedPeserta:', window.selectedPeserta);
        console.log('Debug - selectedPeserta type:', typeof window.selectedPeserta);
        console.log('Debug - selectedPeserta length:', window.selectedPeserta ? window.selectedPeserta.length : 'undefined');
        
        // Ensure selectedPeserta is an array
        if (!Array.isArray(window.selectedPeserta)) {
            console.log('Debug - selectedPeserta is not an array, converting...');
            window.selectedPeserta = window.selectedPeserta ? [window.selectedPeserta] : [];
        }
        
        // CRITICAL FIX: Clear ALL existing hidden inputs in submit form
        $('#submit-form input[name="participants[]"]').remove();
        
        // Add new hidden inputs for each participant
        if (window.selectedPeserta && window.selectedPeserta.length > 0) {
            console.log('Debug - Adding hidden inputs for participants:', window.selectedPeserta);
            window.selectedPeserta.forEach(function(participantId, index) {
                console.log(`Debug - Processing participant ${index}:`, participantId);
                if (participantId && participantId.toString().trim() !== '') {
                    const input = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'participants[]')
                        .val(participantId.toString().trim());
                    $('#submit-form').append(input);
                    console.log(`Debug - Added hidden input for participant ${index}:`, participantId);
                }
            });
        } else {
            console.log('Debug - No participants to add or selectedPeserta is empty');
        }
        
        // Log all hidden inputs before submission
        const submitHiddenInputs = $('#submit-form input[name="participants[]"]');
        console.log('Debug - Submit form hidden inputs before submission:', submitHiddenInputs.length);
        submitHiddenInputs.each(function(index) {
            console.log(`Debug - Submit form hidden input ${index}:`, $(this).val());
        });
        
        // CRITICAL: Ensure form data is properly set before submission
        console.log('Debug - Final check before submission:');
        console.log('Debug - Form action:', $('#submit-form').attr('action'));
        console.log('Debug - Form method:', $('#submit-form').attr('method'));
        console.log('Debug - All form inputs:');
        $('#submit-form input').each(function(index) {
            console.log(`Debug - Input ${index}: name="${$(this).attr('name')}", value="${$(this).val()}"`);
        });
        
        // Continue with form submission
        console.log('Debug - Proceeding with submit form submission');
    });
});
</script>
@endpush
