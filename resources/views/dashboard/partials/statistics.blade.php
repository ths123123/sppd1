{{-- Statistics Cards Component --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Approved Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <svg width="32" height="32" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9 12l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Disetujui</p>
                <p class="text-2xl font-bold text-gray-900" id="approved-count">{{ $approvedCount ?? 0 }}</p>
                <p class="text-xs text-green-600 mt-1 flex items-center">
                    <i class="fas fa-trending-up mr-1"></i>
                    SPPD yang disetujui
                </p>
            </div>
        </div>
    </div>

    <!-- Ditolak Card (mengganti Pending) -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                    <svg width="32" height="32" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="8" y1="8" x2="16" y2="16"></line>
                        <line x1="16" y1="8" x2="8" y2="16"></line>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Ditolak</p>
                <p class="text-2xl font-bold text-gray-900" id="rejected-count">{{ $rejectedCount ?? 0 }}</p>
                <p class="text-xs text-red-600 mt-1 flex items-center">
                    <i class="fas fa-times-circle mr-1"></i>
                    SPPD yang ditolak
                </p>
            </div>
        </div>
    </div>

    <!-- Review Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <svg width="32" height="32" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Review</p>
                <p class="text-2xl font-bold text-gray-900" id="review-count">{{ $reviewCount ?? 0 }}</p>
                <p class="text-xs text-purple-600 mt-1 flex items-center">
                    <i class="fas fa-search mr-1"></i>
                    Dalam proses
                </p>
            </div>
        </div>
    </div>

    <!-- Documents Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <svg width="32" height="32" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Dokumen</p>
                <p class="text-2xl font-bold text-gray-900" id="document-count">{{ $documentCount ?? 0 }}</p>
                <p class="text-xs text-blue-600 mt-1 flex items-center">
                    <i class="fas fa-folder mr-1"></i>
                    Arsip digital
                </p>
            </div>
        </div>
    </div>
</div>
