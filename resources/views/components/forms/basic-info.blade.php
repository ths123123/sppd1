{{-- Informasi Pemohon - Professional Design --}}
<div class="sppd-form-section sppd-animate-fade-in" style="animation-delay: 0.2s;">
    <div class="sppd-section-header">
        <div class="sppd-section-icon">
            <i class="fas fa-user"></i>
        </div>
        <div>
            <h3 class="sppd-section-title">Informasi Pemohon</h3>
            <p class="sppd-section-subtitle">Data staff yang mengajukan perjalanan dinas</p>
        </div>
    </div>

    <div class="sppd-form-row">
        @if(Auth::user()->hasRole(['kasubbag','sekretaris','ppk']))
        <div class="sppd-form-group">
            <label for="user_id" class="sppd-form-label required">Pilih Staff</label>
            <select id="user_id" name="user_id" class="sppd-form-select">
                <option value="">-- Pilih Staff --</option>
                @foreach(\App\Models\User::where('role','staff')->where('is_active',1)->orderBy('name')->get() as $staff)
                    <option value="{{ $staff->id }}" {{ old('user_id', $user->id) == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }} ({{ $staff->nip }})
                    </option>
                @endforeach
            </select>
            @if(isset($errors) && $errors->has('user_id'))
                <p class="sppd-text-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('user_id') }}
                </p>
            @endif
        </div>
        @endif

        <div class="sppd-form-group half-width">
            <label for="nama" class="sppd-form-label">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="{{ $user->name ?? '-' }}" readonly
                   class="sppd-form-input" 
                   style="background: rgba(249, 250, 251, 0.8); cursor: not-allowed;">
        </div>

        <div class="sppd-form-group half-width">
            <label for="nip" class="sppd-form-label">NIP</label>
            <input type="text" id="nip" name="nip" value="{{ $user->nip ?? '-' }}" readonly
                   class="sppd-form-input" 
                   style="background: rgba(249, 250, 251, 0.8); cursor: not-allowed;">
        </div>

        <div class="sppd-form-group half-width">
            <label for="jabatan" class="sppd-form-label">Jabatan</label>
            <input type="text" id="jabatan" name="jabatan" value="{{ $user->jabatan ?? $user->role }}" readonly
                   class="sppd-form-input" 
                   style="background: rgba(249, 250, 251, 0.8); cursor: not-allowed;">
        </div>

        <div class="sppd-form-group half-width">
            <label for="unit_kerja" class="sppd-form-label">Unit Kerja</label>
            <input type="text" id="unit_kerja" name="unit_kerja" value="KPU Kabupaten Cirebon" readonly
                   class="sppd-form-input" 
                   style="background: rgba(249, 250, 251, 0.8); cursor: not-allowed;">
        </div>
    </div>
</div>
</div>
