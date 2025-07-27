{{-- Charts Section Component --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
    <!-- Monthly Trend Chart -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Tren Bulanan SPPD</h3>
                <p class="text-sm text-gray-600">Data real 12 bulan terakhir dari database</p>
            </div>
        </div>
        <div class="chart-container h-80">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution Chart diganti dengan Interactive Slider Custom -->
    <div class="bg-white rounded-xl shadow-lg p-6 slider-container">
        <!-- Header dengan gradasi merah -->
        <div class="bg-red-600 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <div>
                    <h3 class="text-base font-semibold text-white">Informasi Sistem SPPD</h3>
                    <p class="text-blue-100 text-xs">Geser untuk melihat lebih banyak</p>
                </div>
            </div>
            <!-- Navigation Dots & Arrows (atas) -->
            <div class="flex items-center space-x-2">
                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 shadow transition-colors duration-300 mr-2" id="prev-slide">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="flex space-x-2" id="slider-dots">
                    <button class="w-3 h-3 rounded-full bg-white opacity-50 transition-opacity duration-300 slider-dot active" data-slide="0"></button>
                    <button class="w-3 h-3 rounded-full bg-white opacity-50 transition-opacity duration-300 slider-dot" data-slide="1"></button>
                    <button class="w-3 h-3 rounded-full bg-white opacity-50 transition-opacity duration-300 slider-dot" data-slide="2"></button>
                    <button class="w-3 h-3 rounded-full bg-white opacity-50 transition-opacity duration-300 slider-dot" data-slide="3"></button>
                    <button class="w-3 h-3 rounded-full bg-white opacity-50 transition-opacity duration-300 slider-dot" data-slide="4"></button>
                </div>
                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 shadow transition-colors duration-300 ml-2" id="next-slide">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <!-- Slider Content Area -->
        <div class="relative h-80 overflow-hidden">
            <!-- Slides Container -->
            <div class="flex transition-transform duration-500 ease-in-out h-full" id="slides-container">
                <!-- Slide 1: Tentang Sistem -->
                <div class="w-full flex-shrink-0 flex flex-col justify-center h-full">
                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Sistem SPPD Digital</h4>
                    </div>
                    <div class="overflow-y-auto max-h-64 pr-2">
                        <p class="text-gray-600 text-center leading-relaxed">
                            Platform digital untuk mengelola Surat Perintah Perjalanan Dinas secara efisien, 
                            transparan, dan terintegrasi di lingkungan KPU Kabupaten Cirebon. Mengubah proses 
                            manual menjadi digital untuk meningkatkan produktivitas dan akuntabilitas.
                        </p>
                    </div>
                </div>
                <!-- Slide 2: Tujuan & Manfaat -->
                <div class="w-full flex-shrink-0 flex flex-col justify-center h-full">
                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Tujuan & Manfaat</h4>
                    </div>
                    <div class="overflow-y-auto max-h-64 pr-2">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <i class="fas fa-clock text-blue-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-gray-700">Efisiensi Waktu</p>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-eye text-green-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-gray-700">Transparansi</p>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-shield-alt text-purple-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-gray-700">Akuntabilitas</p>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-leaf text-emerald-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-gray-700">Paperless</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide 3: Dasar Hukum -->
                <div class="w-full flex-shrink-0 flex flex-col justify-center h-full">
                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Dasar Hukum</h4>
                    </div>
                    <div class="overflow-y-auto max-h-64 pr-2 space-y-3">
                        <div class="flex items-start bg-gray-50 rounded-lg p-3">
                            <i class="fas fa-file-alt text-blue-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-800 text-sm flex items-center">1. PMK No. 113/PMK.05/2012 <span class='ml-2 text-green-500'>✅</span></p>
                                <p class="text-xs text-gray-600">Tentang Perjalanan Dinas Dalam Negeri Bagi Pejabat Negara, Pegawai Negeri, Dan Pegawai Tidak Tetap BPK RI — regulasi utama SPPD.</p>
                            </div>
                        </div>
                        <div class="flex items-start bg-gray-50 rounded-lg p-3">
                            <i class="fas fa-file-alt text-yellow-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-800 text-sm flex items-center">2. PMK No. 119/2023 <span class='ml-2 text-green-500'>✅</span></p>
                                <p class="text-xs text-gray-600">Perubahan atas PMK 113/2012 — update terbaru regulasi perjalanan dinas.</p>
                            </div>
                        </div>
                        <div class="flex items-start bg-gray-50 rounded-lg p-3">
                            <i class="fas fa-laptop text-purple-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-800 text-sm flex items-center">3. Perpres No. 95/2018 <span class='ml-2 text-green-500'>✅</span></p>
                                <p class="text-xs text-gray-600">Tentang Sistem Pemerintahan Berbasis Elektronik (SPBE) — dasar hukum digitalisasi sistem pemerintah.</p>
                            </div>
                        </div>
                        <div class="flex items-start bg-gray-50 rounded-lg p-3">
                            <i class="fas fa-gavel text-red-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-800 text-sm flex items-center">4. PP No. 82/2012 <span class='ml-2 text-green-500'>✅</span></p>
                                <p class="text-xs text-gray-600">Tentang Penyelenggaraan Sistem dan Transaksi Elektronik — dasar hukum sistem elektronik.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide 4: Teknologi -->
                <div class="w-full flex-shrink-0 flex flex-col justify-center h-full">
                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Teknologi & Security Sistem</h4>
                    </div>
                    <div class="grid grid-cols-2 gap-4 overflow-y-auto scrollbar-thin max-h-64">
                        <div class="bg-red-50 rounded-lg p-3 text-center">
                            <i class="fab fa-laravel text-red-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Laravel 12</p>
                            <p class="text-xs text-gray-500">Framework Backend</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <i class="fab fa-php text-blue-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">PHP 8.2</p>
                            <p class="text-xs text-gray-500">Bahasa Backend</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <i class="fas fa-database text-blue-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">PostgreSQL</p>
                            <p class="text-xs text-gray-500">Database</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-3 text-center">
                            <i class="fab fa-css3-alt text-blue-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Tailwind CSS</p>
                            <p class="text-xs text-gray-500">Framework CSS</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-3 text-center">
                            <i class="fas fa-bolt text-yellow-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Vite</p>
                            <p class="text-xs text-gray-500">Bundler & Dev Server</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <i class="fab fa-js text-yellow-400 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Alpine.js</p>
                            <p class="text-xs text-gray-500">Interactivity</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <i class="fas fa-plug text-blue-400 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Axios</p>
                            <p class="text-xs text-gray-500">HTTP Client</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3 text-center">
                            <i class="fas fa-user-shield text-purple-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Spatie Permission</p>
                            <p class="text-xs text-gray-500">Role & Permission</p>
                        </div>
                        <div class="bg-pink-50 rounded-lg p-3 text-center">
                            <i class="fas fa-file-pdf text-pink-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">DomPDF</p>
                            <p class="text-xs text-gray-500">Export PDF</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-3 text-center">
                            <i class="fas fa-file-word text-indigo-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">PHPWord</p>
                            <p class="text-xs text-gray-500">Export Word</p>
                        </div>
                        <div class="bg-green-100 rounded-lg p-3 text-center">
                            <i class="fas fa-file-excel text-green-600 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Excel</p>
                            <p class="text-xs text-gray-500">Export Excel</p>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-3 text-center">
                            <i class="fas fa-lock text-yellow-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">SSL/TLS</p>
                            <p class="text-xs text-gray-500">Security</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-3 text-center">
                            <i class="fas fa-key text-blue-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Sanctum</p>
                            <p class="text-xs text-gray-500">API Authentication</p>
                        </div>
                        <div class="bg-pink-100 rounded-lg p-3 text-center">
                            <i class="fas fa-shield-alt text-pink-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">CSRF Protection</p>
                            <p class="text-xs text-gray-500">Anti Cross-Site Request Forgery</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-3 text-center">
                            <i class="fas fa-shield-virus text-yellow-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">XSS Protection</p>
                            <p class="text-xs text-gray-500">Anti Cross-Site Scripting</p>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 text-center">
                            <i class="fas fa-user-lock text-gray-700 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">Custom Middleware</p>
                            <p class="text-xs text-gray-500">Proteksi User & Admin</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <i class="fas fa-shield-check text-green-500 text-2xl mb-2"></i>
                            <p class="font-medium text-gray-700 text-sm">OWASP Compliance</p>
                            <p class="text-xs text-gray-500">Top 10 Security Standard</p>
                        </div>
                    </div>
                </div>
                <!-- Slide 5: Alur Proses -->
                <div class="w-full flex-shrink-0 flex flex-col justify-center h-full">
                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Alur Proses Digital</h4>
                    </div>
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="flex flex-row items-center space-x-4 mb-4">
                            <!-- Kasubbag Ajukan -->
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-user-edit text-blue-500 text-xl"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-700">Kasubbag<br>Ajukan</p>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 text-lg"></i>
                            <!-- Sekretaris Review/Approval -->
                            <div class="text-center">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-user-check text-yellow-500 text-xl"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-700">Sekretaris<br>Review</p>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 text-lg"></i>
                            <!-- PPK Approval -->
                            <div class="text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-user-tie text-green-500 text-xl"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-700">PPK<br>Approval</p>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 text-lg"></i>
                            <!-- Selesai/Laporan -->
                            <div class="text-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-file-alt text-purple-500 text-xl"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-700">Selesai<br>Laporan</p>
                            </div>
                        </div>
                        <!-- Panah revisi -->
                        <div class="flex flex-row items-center justify-center mt-2">
                            <div class="w-1/4"></div>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-arrow-down text-red-400 text-lg"></i>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-undo text-red-400 text-sm"></i>
                                    <span class="text-xs text-red-500 font-semibold">Revisi</span>
                                </div>
                                <i class="fas fa-arrow-up text-red-400 text-lg"></i>
                            </div>
                            <div class="w-1/4"></div>
                        </div>
                        <!-- Penjelasan revisi -->
                        <div class="mt-2 text-center text-xs text-gray-500 max-w-md mx-auto">
                            Jika Sekretaris/PPK menilai ada kekurangan, mereka dapat mengembalikan SPPD ke Kasubbag untuk revisi. Kasubbag memperbaiki dan mengajukan ulang, lalu proses approval berlanjut hingga selesai dan masuk ke laporan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
