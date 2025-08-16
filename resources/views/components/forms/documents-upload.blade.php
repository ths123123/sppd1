{{-- Upload Dokumen Pendukung --}}
<div class="sppd-form-section sppd-animate-fade-in" style="animation-delay: 0.45s;">
    <div class="sppd-section-header">
        <div class="sppd-section-icon">
            <i class="fas fa-upload"></i>
        </div>
        <div>
            <h3 class="sppd-section-title">Upload Dokumen Pendukung</h3>
            <p class="sppd-section-subtitle">Unggah file PDF/JPG/PNG (opsional, max 2MB per file)</p>
        </div>
    </div>

    <div class="sppd-form-group">
        <label for="dokumen_pendukung" class="sppd-form-label">
            <i class="fas fa-file-upload mr-2 text-blue-500"></i>
            Dokumen Pendukung
        </label>
        <input type="file" id="dokumen_pendukung" name="dokumen_pendukung[]" multiple accept=".pdf,.jpg,.jpeg,.png"
               class="sppd-form-input">
        @if(isset($errors) && $errors->has('dokumen_pendukung'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('dokumen_pendukung') }}
                </p>
            @endif
        @if($errors->has('dokumen_pendukung.*'))
            @foreach($errors->get('dokumen_pendukung.*') as $messages)
                @foreach($messages as $msg)
                    <p class="sppd-text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $msg }}
                    </p>
                @endforeach
            @endforeach
        @endif
    </div>
</div>
