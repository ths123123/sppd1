{{-- Informasi Anggaran - Professional Design --}}
<div class="sppd-form-section sppd-animate-fade-in" style="animation-delay: 0.4s; pointer-events:auto !important; opacity:1 !important;">
    <div class="sppd-section-header">
        <div class="sppd-section-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
            <h3 class="sppd-section-title">Informasi Anggaran</h3>
            <p class="sppd-section-subtitle">Rincian biaya perjalanan dinas dengan kalkulasi otomatis</p>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex flex-wrap gap-3 mb-6">
        <button type="button" onclick="quickCalculateAll()"
                class="sppd-btn sppd-btn-primary">
            <i class="fas fa-magic"></i>
            <span>Hitung Otomatis</span>
        </button>
        <button type="button" onclick="resetAllCosts()"
                class="sppd-btn sppd-btn-secondary">
            <i class="fas fa-undo"></i>
            <span>Reset Biaya</span>
        </button>
        <button type="button" onclick="showCostGuide()"
                class="sppd-btn sppd-btn-success">
            <i class="fas fa-question-circle"></i>
            <span>Panduan Biaya</span>
        </button>
    </div>

    <!-- Budget Grid -->
    <div class="sppd-budget-grid">
        <div class="sppd-budget-card">
            <label for="biaya_transport" class="sppd-form-label required">
                <i class="fas fa-car mr-2 text-blue-500"></i>
                Biaya Transportasi
            </label>
            <div class="sppd-currency-input">
                <input type="text" id="biaya_transport" name="biaya_transport" 
                       value="{{ old('biaya_transport', 0) }}" min="0"
                       class="sppd-form-input"
                       placeholder="0" autocomplete="off" inputmode="numeric">
            </div>
            @if(isset($errors) && $errors->has('biaya_transport'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('biaya_transport') }}
                </p>
            @endif
            <div class="sppd-text-info">
                <i class="fas fa-info-circle"></i>
                <span>Otomatis terhitung berdasarkan transportasi</span>
            </div>
        </div>

        <div class="sppd-budget-card">
            <label for="biaya_penginapan" class="sppd-form-label required">
                <i class="fas fa-bed mr-2 text-purple-500"></i>
                Biaya Penginapan 
                <span class="text-sm bg-purple-100 text-purple-600 px-2 py-1 rounded-full ml-2">Manual</span>
            </label>
            <div class="sppd-currency-input">
                <input type="text" id="biaya_penginapan" name="biaya_penginapan" 
                       value="{{ old('biaya_penginapan', 0) }}" min="0"
                       class="sppd-form-input"
                       placeholder="Masukkan biaya penginapan" autocomplete="off" inputmode="numeric">
            </div>
            @if(isset($errors) && $errors->has('biaya_penginapan'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('biaya_penginapan') }}
                </p>
            @endif
            <div class="sppd-text-info">
                <i class="fas fa-bed"></i>
                <span>Hotel standar: Rp 500-800rb/malam</span>
            </div>
        </div>

        <div class="sppd-budget-card">
            <label for="uang_harian" class="sppd-form-label required">
                <i class="fas fa-utensils mr-2 text-green-500"></i>
                Uang Harian
            </label>
            <div class="sppd-currency-input">
                <input type="text" id="uang_harian" name="uang_harian" 
                       value="{{ old('uang_harian', 0) }}" min="0"
                       class="sppd-form-input"
                       placeholder="0" autocomplete="off" inputmode="numeric">
            </div>
            @if(isset($errors) && $errors->has('uang_harian'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('uang_harian') }}
                </p>
            @endif
            <div class="sppd-text-info">
                <i class="fas fa-info-circle"></i>
                <span>Otomatis terhitung berdasarkan durasi perjalanan</span>
            </div>
        </div>

        <div class="sppd-budget-card">
            <label for="biaya_lainnya" class="sppd-form-label">
                <i class="fas fa-plus-circle mr-2 text-orange-500"></i>
                Biaya Lainnya <span class="text-xs text-gray-500 ml-2">(misal: tol, parkir, konsumsi, ATK, dll.)</span>
            </label>
            <div class="sppd-currency-input">
                <input type="text" id="biaya_lainnya" name="biaya_lainnya" 
                       value="{{ old('biaya_lainnya', 0) }}" min="0"
                       class="sppd-form-input"
                       placeholder="0" autocomplete="off" inputmode="numeric">
            </div>
            @if(isset($errors) && $errors->has('biaya_lainnya'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('biaya_lainnya') }}
                </p>
            @endif
            <div class="sppd-text-info">
                <i class="fas fa-info-circle"></i>
                <span>Biaya tambahan seperti tol, parkir, konsumsi, ATK, dll.</span>
            </div>
        </div>
    </div>

    <!-- Total Budget Display -->
    <div class="sppd-total-budget">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold">Total Biaya Perjalanan</h4>
            <div class="flex items-center gap-2">
                <span class="text-sm opacity-75">Otomatis Terhitung</span>
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
        </div>
        <div class="text-center">
            <div class="amount" id="total_display">Rp 0</div>
            <div class="label">Total Anggaran SPPD</div>
        </div>
        <input type="hidden" id="total_biaya" name="total_biaya" value="{{ old('total_biaya', 0) }}" readonly>
    </div>
    
    <!-- Sumber Dana -->
    <div class="sppd-form-group">
        <label for="sumber_dana" class="sppd-form-label required">
            <i class="fas fa-university mr-2 text-blue-500"></i>
            Sumber Dana
        </label>
        <select id="sumber_dana" name="sumber_dana" class="sppd-form-select">
            <option value="">Pilih Sumber Dana</option>
            <option value="APBD" {{ old('sumber_dana') == 'APBD' ? 'selected' : '' }}>APBD</option>
            <option value="APBN" {{ old('sumber_dana') == 'APBN' ? 'selected' : '' }}>APBN</option>
            <option value="Dana Lainnya" {{ old('sumber_dana') == 'Dana Lainnya' ? 'selected' : '' }}>Dana Lainnya</option>
        </select>
        @if(isset($errors) && $errors->has('sumber_dana'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('sumber_dana') }}
                </p>
            @endif
    </div>
</div>
