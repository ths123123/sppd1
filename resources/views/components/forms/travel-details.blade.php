{{-- Informasi Perjalanan - Professional Design --}}
<div class="sppd-form-section sppd-animate-fade-in" style="animation-delay: 0.3s; pointer-events:auto !important; opacity:1 !important;">
    <div class="sppd-section-header">
        <div class="sppd-section-icon">
            <i class="fas fa-route"></i>
        </div>
        <div>
            <h3 class="sppd-section-title">Informasi Perjalanan</h3>
            <p class="sppd-section-subtitle">Detail tujuan dan keperluan perjalanan dinas</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="sppd-form-group">
            <label for="tujuan" class="sppd-form-label required">
                <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>
                Tujuan Perjalanan
            </label>
            <input type="text" id="tujuan" name="tujuan" value="{{ old('tujuan') }}" required
                   class="sppd-form-input"
                   placeholder="Contoh: KPU Provinsi Jawa Barat">
            @error('tujuan')
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="sppd-form-group">
            <label for="keperluan" class="sppd-form-label required">
                <i class="fas fa-clipboard-list mr-2 text-blue-500"></i>
                Keperluan Perjalanan
            </label>
            <textarea id="keperluan" name="keperluan" rows="4" required
                      class="sppd-form-input"
                      placeholder="Jelaskan maksud dan tujuan perjalanan dinas">{{ old('keperluan') }}</textarea>
            @error('keperluan')
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="sppd-form-row">
            <div class="sppd-form-group half-width">
                <label for="tanggal_berangkat" class="sppd-form-label required">
                    Tanggal Berangkat
                </label>
                <input type="date" id="tanggal_berangkat" name="tanggal_berangkat" value="{{ old('tanggal_berangkat') }}" required
                       class="sppd-form-input"
                       onchange="calculateDuration(); validateDates()">
                @error('tanggal_berangkat')
                    <p class="sppd-text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>
            <div class="sppd-form-group half-width">
                <label for="tanggal_kembali" class="sppd-form-label required">
                    Tanggal Kembali
                </label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali') }}" required
                       class="sppd-form-input"
                       onchange="calculateDuration(); validateDates()">
                @error('tanggal_kembali')
                    <p class="sppd-text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div class="sppd-form-row">
            <div class="sppd-form-group half-width">
                <label for="lama_perjalanan" class="sppd-form-label required">
                    Lama Perjalanan (Hari)
                    <span class="text-xs text-green-600 ml-1">✅ Otomatis terhitung</span>
                </label>
                <input type="number" id="lama_perjalanan" name="lama_perjalanan" value="{{ old('lama_perjalanan') }}" min="1" required readonly
                       class="sppd-form-input"
                       style="background: rgba(249, 250, 251, 0.8); cursor: not-allowed;"
                       placeholder="Otomatis terhitung">
                @error('lama_perjalanan')
                    <p class="sppd-text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>
            <div class="sppd-form-group half-width">
                <label for="transportasi" class="sppd-form-label required">
                    Jenis Transportasi
                </label>
                <select id="transportasi" name="transportasi" required
                        class="sppd-form-select"
                        onchange="autoSuggestTransportCost()">
                    <option value="">Pilih Transportasi</option>
                    <option value="Pesawat" {{ old('transportasi') == 'Pesawat' ? 'selected' : '' }}>Pesawat</option>
                    <option value="Kereta Api" {{ old('transportasi') == 'Kereta Api' ? 'selected' : '' }}>Kereta Api</option>
                    <option value="Bus" {{ old('transportasi') == 'Bus' ? 'selected' : '' }}>Bus</option>
                    <option value="Kendaraan Dinas" {{ old('transportasi') == 'Kendaraan Dinas' ? 'selected' : '' }}>Kendaraan Dinas</option>
                    <option value="Kendaraan Pribadi" {{ old('transportasi') == 'Kendaraan Pribadi' ? 'selected' : '' }}>Kendaraan Pribadi</option>
                    <option value="Lainnya" {{ old('transportasi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('transportasi')
                    <p class="sppd-text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div class="sppd-form-group">
            <label for="tempat_menginap" class="sppd-form-label">
                Tempat Menginap
            </label>
            <input type="text" id="tempat_menginap" name="tempat_menginap" value="{{ old('tempat_menginap') }}"
                   class="sppd-form-input"
                   placeholder="Hotel/Guest House (opsional)">
            @error('tempat_menginap')
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</div>
