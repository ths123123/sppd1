<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Umum | SPPD KPU Kabupaten Cirebon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#8B0000',
                        'primary-dark': '#700000',
                        'primary-light': '#A52A2A',
                        'primary-bg': '#FFF5F5'
                    },
                    boxShadow: {
                        'elegant': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
                        'button': '0 4px 6px -1px rgba(139, 0, 0, 0.2), 0 2px 4px -1px rgba(139, 0, 0, 0.1)'
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }
        
        .form-input:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.2);
            border-color: #8B0000;
        }
        
        .upload-area {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: #8B0000;
            background-color: #FFF5F5;
        }
        
        .logo-preview {
            transition: transform 0.3s ease;
        }
        
        .logo-preview:hover {
            transform: scale(1.03);
        }
        
        .save-btn {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(139, 0, 0, 0.2), 0 2px 4px -1px rgba(139, 0, 0, 0.1);
        }
        
        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(139, 0, 0, 0.3), 0 4px 6px -2px rgba(139, 0, 0, 0.15);
        }
        
        .save-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl">
        <!-- Card Container -->
        <div class="bg-white rounded-xl shadow-elegant overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary to-primary-light px-6 py-5 flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-white/90 shadow-md">
                    <i class="fas fa-cog text-2xl text-primary"></i>
            </div>
            <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight mb-1">Pengaturan Umum</h2>
                    <p class="text-sm text-white/90">Konfigurasi sistem aplikasi SPPD KPU Kabupaten Cirebon</p>
                </div>
            </div>
            
            <!-- Form Section -->
            <form class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Sistem -->
                    <div class="space-y-2">
                        <label class="block text-base font-semibold text-gray-800">Nama Sistem <span class="text-primary">*</span></label>
                        <input type="text" value="Sistem SPPD KPU Kab. Cirebon" 
                               class="form-input w-full px-4 py-3 rounded-lg border-gray-300 focus:ring-primary focus:border-primary text-base transition-colors">
                    </div>
                    
                    <!-- Bahasa -->
                    <div class="space-y-2">
                        <label class="block text-base font-semibold text-gray-800">Bahasa</label>
                        <select class="form-select w-full px-4 py-3 rounded-lg border-gray-300 focus:ring-primary focus:border-primary text-base transition-colors">
                            <option selected>Bahasa Indonesia</option>
                            <option>English</option>
                </select>
            </div>
                    
                    <!-- Tema Sistem -->
                    <div class="space-y-2">
                        <label class="block text-base font-semibold text-gray-800">Tema Sistem</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-primary hover:bg-primary-bg transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-bg">
                                <input type="radio" name="theme" class="hidden peer" checked>
                                <div class="mr-3">
                                    <div class="w-4 h-4 rounded-full border border-gray-400 peer-checked:border-primary peer-checked:bg-primary peer-checked:ring-2 peer-checked:ring-primary/30"></div>
                                </div>
                                <span class="text-gray-700">Terang</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-primary hover:bg-primary-bg transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-bg">
                                <input type="radio" name="theme" class="hidden peer">
                                <div class="mr-3">
                                    <div class="w-4 h-4 rounded-full border border-gray-400 peer-checked:border-primary peer-checked:bg-primary peer-checked:ring-2 peer-checked:ring-primary/30"></div>
                                </div>
                                <span class="text-gray-700">Gelap</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-primary hover:bg-primary-bg transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-bg">
                                <input type="radio" name="theme" class="hidden peer">
                                <div class="mr-3">
                                    <div class="w-4 h-4 rounded-full border border-gray-400 peer-checked:border-primary peer-checked:bg-primary peer-checked:ring-2 peer-checked:ring-primary/30"></div>
                                </div>
                                <span class="text-gray-700">Sistem</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Logo Sistem -->
                    <div class="space-y-2">
                        <label class="block text-base font-semibold text-gray-800">Logo Sistem</label>
                        <div class="upload-area rounded-lg p-5 text-center cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600 mb-2 font-medium">Klik untuk mengunggah logo</p>
                            <p class="text-gray-500 text-sm">Format: PNG, JPG, SVG. Maks. 2MB</p>
                            <input type="file" class="hidden">
                        </div>
                        
                        <!-- Logo Preview -->
                        <div class="mt-4">
                            <p class="text-gray-700 font-medium mb-2">Logo Saat Ini:</p>
                            <div class="flex items-center">
                                <img src="https://via.placeholder.com/80x80?text=LOGO" alt="Logo Sistem" class="logo-preview h-16 w-16 rounded-lg border border-gray-200 object-contain p-1">
                                <button type="button" class="ml-3 text-sm font-medium text-primary hover:text-primary-dark transition-colors">
                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                </button>
                            </div>
                        </div>
            </div>
        </div>
                
                <!-- Action Buttons -->
                <div class="mt-10 flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button" class="px-6 py-3 rounded-lg text-base font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i> Batal
                    </button>
                    <button type="submit" class="save-btn px-6 py-3 rounded-lg text-base font-bold text-white bg-primary hover:bg-primary-dark transition-all">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div> 
        
        <!-- Footer Note -->
        <p class="mt-6 text-center text-gray-500 text-sm">
            Sistem Pengelolaan SPPD © 2023 KPU Kabupaten Cirebon. Versi 2.1.4
        </p>
    </div>
    
    <script>
        // Demo functionality for file upload
        document.querySelector('.upload-area').addEventListener('click', function() {
            this.querySelector('input[type="file"]').click();
        });
        
        // Animation for save button
        document.querySelector('.save-btn').addEventListener('click', function(e) {
            e.preventDefault();
            
            const btn = this;
            const originalHtml = btn.innerHTML;
            
            // Show loading state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
            btn.disabled = true;
            
            // Simulate API request
            setTimeout(() => {
                // Create success feedback
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg flex items-center animate-fade-in';
                successDiv.innerHTML = `
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <div>
                        <p class="font-bold">Pengaturan berhasil disimpan!</p>
                        <p class="text-sm">Perubahan telah diterapkan pada sistem.</p>
                    </div>
                `;
                document.body.appendChild(successDiv);
                
                // Remove after delay
                setTimeout(() => {
                    successDiv.classList.add('animate-fade-out');
                    setTimeout(() => successDiv.remove(), 300);
                }, 3000);
                
                // Reset button
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, 1500);
        });
    </script>
</body>
</html>