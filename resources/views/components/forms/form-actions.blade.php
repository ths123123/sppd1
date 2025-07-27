{{-- Form Actions - Professional Submit Buttons --}}
<div class="sppd-form-actions sppd-animate-fade-in" style="animation-delay: 0.6s;">
    <div class="flex items-center gap-2">
        <i class="fas fa-info-circle text-blue-500"></i>
        <span class="text-sm text-gray-600">Pastikan semua data telah diisi dengan benar</span>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('travel-requests.index') }}" class="sppd-btn sppd-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
        
        <button type="submit" name="action" value="draft" class="sppd-btn sppd-btn-secondary">
            <i class="fas fa-save"></i>
            <span>Draft</span>
        </button>
        <button type="submit" name="action" value="submit" class="sppd-btn sppd-btn-secondary">
            <i class="fas fa-paper-plane"></i>
            <span>Ajukan</span>
        </button>
    </div>
</div>
