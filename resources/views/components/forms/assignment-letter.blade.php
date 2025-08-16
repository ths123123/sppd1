{{-- Surat Tugas --}}
<div class="sppd-form-section sppd-animate-fade-in" style="animation-delay: 0.25s; pointer-events:auto !important; opacity:1 !important;">
    <div class="sppd-section-header">
        <div class="sppd-section-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <div>
            <h3 class="sppd-section-title">Surat Tugas</h3>
            <p class="sppd-section-subtitle">Nomor dan tanggal surat tugas (jika sudah ada)</p>
        </div>
    </div>

    <div class="sppd-form-row">
        <div class="sppd-form-group half-width">
            <label for="nomor_surat_tugas" class="sppd-form-label">Nomor Surat Tugas</label>
            <input type="text" id="nomor_surat_tugas" name="nomor_surat_tugas" value="{{ old('nomor_surat_tugas') }}"
                   class="sppd-form-input" placeholder="Nomor surat tugas">
            @if(isset($errors) && $errors->has('nomor_surat_tugas'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('nomor_surat_tugas') }}
                </p>
            @endif
        </div>

        <div class="sppd-form-group half-width">
            <label for="tanggal_surat_tugas" class="sppd-form-label">Tanggal Surat Tugas</label>
            <input type="date" id="tanggal_surat_tugas" name="tanggal_surat_tugas" value="{{ old('tanggal_surat_tugas') }}"
                   class="sppd-form-input">
            @if(isset($errors) && $errors->has('tanggal_surat_tugas'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('tanggal_surat_tugas') }}
                </p>
            @endif
        </div>
    </div>
</div>
