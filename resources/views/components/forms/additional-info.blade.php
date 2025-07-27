{{-- Informasi Tambahan --}}
<div class="sppd-form-section sppd-animate-fade-in" style="animation-delay: 0.5s;">
    <div class="sppd-section-header">
        <div class="sppd-section-icon">
            <i class="fas fa-sticky-note"></i>
        </div>
        <div>
            <h3 class="sppd-section-title">Informasi Tambahan</h3>
            <p class="sppd-section-subtitle">Catatan dan keterangan lainnya</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="sppd-form-group">
            <label for="catatan_pemohon" class="sppd-form-label">
                <i class="fas fa-comment-alt mr-2 text-blue-500"></i>
                Catatan Pemohon
            </label>
            <textarea id="catatan_pemohon" name="catatan_pemohon" rows="3"
                      class="sppd-form-input"
                      placeholder="Catatan atau keterangan tambahan (opsional)">{{ old('catatan_pemohon') }}</textarea>
            @error('catatan_pemohon')
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="sppd-form-group">
            <div class="flex items-center space-x-3">
                <input type="checkbox" id="is_urgent" name="is_urgent" value="1" {{ old('is_urgent') ? 'checked' : '' }}
                       class="w-4 h-4 text-black bg-gray-100 border-gray-300 rounded focus:ring-black">
                <label for="is_urgent" class="sppd-form-label">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>
                    Perjalanan Dinas Mendesak
                </label>
            </div>
        </div>
    </div>
</div>
