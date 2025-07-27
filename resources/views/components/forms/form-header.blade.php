{{-- Form Header dengan Progress Steps - Professional Design --}}
<div class="sppd-form-header sppd-animate-fade-in">
    <!-- Navigation Back -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('travel-requests.index') }}" 
           class="flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-all duration-200 hover:transform hover:scale-105">
            <i class="fas fa-arrow-left text-lg"></i>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>
        
        <!-- Quick Actions -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="showCostGuide()" 
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-all duration-200">
                <i class="fas fa-info-circle"></i>
                <span>Panduan Biaya</span>
            </button>
            <button type="button" onclick="quickCalculateAll()" 
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-black hover:bg-gray-800 rounded-lg transition-all duration-200">
                <i class="fas fa-calculator"></i>
                <span>Hitung Otomatis</span>
            </button>
        </div>
    </div>
    
    <!-- Main Title -->
    <div class="text-center mb-8">
        <h1 class="sppd-form-title">Pengajuan SPPD Baru</h1>
        <p class="sppd-form-subtitle">
            Isi formulir perjalanan dinas dengan lengkap dan akurat
        </p>
        <div class="flex items-center justify-center gap-4 mt-4 text-sm text-gray-500">
            <div class="flex items-center gap-1">
                <i class="fas fa-user-circle text-blue-500"></i>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <div class="flex items-center gap-1">
                <i class="fas fa-calendar-alt text-green-500"></i>
                <span>{{ date('d M Y') }}</span>
            </div>
            <div class="flex items-center gap-1">
                <i class="fas fa-clock text-purple-500"></i>
                <span>{{ date('H:i') }} WIB</span>
            </div>
        </div>
    </div>
    
    <!-- Progress Steps -->
    <div class="sppd-progress-container">
        <div class="sppd-progress-step active">
            <div class="sppd-progress-circle">
                <i class="fas fa-user"></i>
            </div>
            <div class="sppd-progress-label">Data Pemohon</div>
        </div>
        <div class="sppd-progress-step">
            <div class="sppd-progress-circle">
                <i class="fas fa-route"></i>
            </div>
            <div class="sppd-progress-label">Perjalanan</div>
        </div>
        <div class="sppd-progress-step">
            <div class="sppd-progress-circle">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="sppd-progress-label">Anggaran</div>
        </div>
        <div class="sppd-progress-step">
            <div class="sppd-progress-circle">
                <i class="fas fa-paperclip"></i>
            </div>
            <div class="sppd-progress-label">Dokumen</div>
        </div>
        <div class="sppd-progress-step">
            <div class="sppd-progress-circle">
                <i class="fas fa-check"></i>
            </div>
            <div class="sppd-progress-label">Selesai</div>
        </div>
    </div>
</div>

<!-- Error Messages -->
@if ($errors->any())
    <div class="sppd-form-section sppd-animate-slide-in" style="background: rgba(254, 242, 242, 0.95); border-color: rgba(248, 113, 113, 0.3);">
        <div class="flex items-center mb-3">
            <i class="fas fa-exclamation-triangle mr-2 text-red-600 text-lg"></i>
            <h4 class="font-semibold text-red-600">Terdapat kesalahan dalam pengisian form:</h4>
        </div>
        <ul class="list-disc list-inside text-sm space-y-2 text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
