/**
 * SPPD Form JavaScript Functions
 * Handles automatic calculations, validations, and form interactions
 */

// Global format functions
function formatRupiah(angka) {
    if (!angka) return '';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseNumber(str) {
    if (!str) return 0;
    // Hanya ambil digit angka
    return parseInt((str+'').replace(/[^\d]/g, ''), 10) || 0;
}

// Calculate duration automatically
function calculateDuration() {
    const startDate = document.getElementById('tanggal_berangkat').value;
    const endDate = document.getElementById('tanggal_kembali').value;

    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays > 0) {
            document.getElementById('lama_perjalanan').value = diffDays;

            // Auto-calculate uang harian berdasarkan durasi
            autoCalculateUangHarian(diffDays);

            // Update total setelah durasi berubah
            updateTotalDanFormat();
        }
    }
}

// Auto-calculate uang harian berdasarkan durasi dan rate
function autoCalculateUangHarian(durasi) {
    const uangHarianInput = document.getElementById('uang_harian');
    const currentValue = parseNumber(uangHarianInput.value);

    // Jika belum diisi atau 0, otomatis hitung
    if (currentValue === 0) {
        // Rate uang harian standar (bisa disesuaikan)
        const ratePerHari = 350000; // Rp 350.000 per hari
        const totalUangHarian = durasi * ratePerHari;
        uangHarianInput.value = formatRupiah(totalUangHarian);

        // Show notification
        showCalculationNotification('Uang harian dihitung otomatis: Rp ' + formatRupiah(totalUangHarian) + ' (' + durasi + ' hari × Rp ' + formatRupiah(ratePerHari) + ')');
    }
}

// Auto-suggest biaya berdasarkan transportasi
function autoSuggestTransportCost() {
    const transportasi = document.getElementById('transportasi').value;
    const biayaTransportInput = document.getElementById('biaya_transport');
    const currentValue = parseNumber(biayaTransportInput.value);

    if (currentValue === 0) {
        let suggestedCost = 0;
        let suggestion = '';

        switch(transportasi) {
            case 'Pesawat':
                suggestedCost = 2500000; // Rp 2.5 juta
                suggestion = 'Pesawat (estimasi Jakarta-daerah PP)';
                break;
            case 'Kereta Api':
                suggestedCost = 500000; // Rp 500rb
                suggestion = 'Kereta API (estimasi kelas eksekutif PP)';
                break;
            case 'Bus':
                suggestedCost = 250000; // Rp 250rb
                suggestion = 'Bus (estimasi kelas VIP PP)';
                break;
            case 'Kendaraan Dinas':
                suggestedCost = 300000; // BBM + tol
                suggestion = 'Kendaraan Dinas (BBM + tol estimasi)';
                break;
            case 'Kendaraan Pribadi':
                suggestedCost = 400000; // BBM + tol + reimburse
                suggestion = 'Kendaraan Pribadi (reimburse BBM + tol)';
                break;
        }

        if (suggestedCost > 0) {
            biayaTransportInput.value = formatRupiah(suggestedCost);
            showCalculationNotification('Estimasi biaya transportasi: Rp ' + formatRupiah(suggestedCost) + ' (' + suggestion + ')');
            updateTotalDanFormat();
        }
    }
}

// Show calculation notification
function showCalculationNotification(message) {
    // Create notification element if not exists
    let notification = document.getElementById('calc-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'calc-notification';
        notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
        document.body.appendChild(notification);
    }

    notification.textContent = message;
    notification.style.display = 'block';

    // Hide after 4 seconds
    setTimeout(() => {
        notification.style.display = 'none';
    }, 4000);
}

// Auto-calculate biaya penginapan
function autoCalculatePenginapan() {
    const durasi = parseInt(document.getElementById('lama_perjalanan').value) || 0;
    const biayaPenginapanInput = document.getElementById('biaya_penginapan');

    if (durasi > 1) { // Penginapan hanya jika lebih dari 1 hari
        const ratePerMalam = 750000; // Rp 750.000 per malam (hotel standar)
        const malamMenginap = durasi - 1; // Malam menginap = durasi - 1
        const totalPenginapan = malamMenginap * ratePerMalam;

        biayaPenginapanInput.value = formatRupiah(totalPenginapan);
        showCalculationNotification('Biaya penginapan dihitung otomatis: Rp ' + formatRupiah(totalPenginapan) + ' (' + malamMenginap + ' malam × Rp ' + formatRupiah(ratePerMalam) + ')');
        updateTotalDanFormat();
    } else {
        biayaPenginapanInput.value = '';
        showCalculationNotification('Tidak memerlukan penginapan untuk perjalanan 1 hari');
    }
}

// Validate dates
function validateDates() {
    const startDate = document.getElementById('tanggal_berangkat').value;
    const endDate = document.getElementById('tanggal_kembali').value;

    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end < start) {
            showCalculationNotification('⚠️ Tanggal kembali tidak boleh lebih awal dari tanggal berangkat!');
            document.getElementById('tanggal_kembali').value = startDate;
            calculateDuration();
        }
    }
}

// Quick calculate all costs
function quickCalculateAll() {
    const durasi = parseInt(document.getElementById('lama_perjalanan').value) || 0;
    const transportasi = document.getElementById('transportasi').value;

    if (durasi === 0) {
        showCalculationNotification('⚠️ Silakan isi tanggal terlebih dahulu!');
        return;
    }

    if (!transportasi) {
        showCalculationNotification('⚠️ Silakan pilih transportasi terlebih dahulu!');
        return;
    }

    // Auto calculate semua komponen
    autoSuggestTransportCost();
    setTimeout(() => autoCalculatePenginapan(), 100);
    setTimeout(() => autoCalculateUangHarian(durasi), 200);

    showCalculationNotification('✅ Semua biaya dihitung otomatis berdasarkan durasi ' + durasi + ' hari');
}

// Reset all costs
function resetAllCosts() {
    if (confirm('Yakin ingin reset semua biaya?')) {
        ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'].forEach(id => {
            document.getElementById(id).value = '';
        });
        updateTotalDanFormat();
        showCalculationNotification('🔄 Semua biaya telah direset');
    }
}

// Show cost guide
function showCostGuide() {
    const guideText = `
📊 PANDUAN ESTIMASI BIAYA SPPD:

🚗 TRANSPORTASI:
• Pesawat: Rp 1.5-3 juta (PP)
• Kereta Eksekutif: Rp 400-600rb (PP)
• Bus VIP: Rp 200-300rb (PP)
• Kendaraan Dinas: Rp 200-400rb (BBM+tol)

🏨 PENGINAPAN:
• Hotel Standar: Rp 500-800rb/malam
• Hotel Bintang 3: Rp 800rb-1.2jt/malam
• Guest House: Rp 300-500rb/malam

💰 UANG HARIAN:
• Dalam Kota: Rp 200-300rb/hari
• Luar Kota: Rp 300-500rb/hari
• Luar Provinsi: Rp 400-600rb/hari

Klik "Hitung Semua Otomatis" untuk estimasi otomatis!
    `;

    alert(guideText);
}

// Auto calculate total budget
function calculateTotal() {
    function parseNumber(str) {
        if (!str) return 0;
        return parseInt((str+'').replace(/[^\d]/g, ''), 10) || 0;
    }
    function formatRupiah(angka) {
        if (!angka) return '';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    const transport = parseNumber(document.getElementById('biaya_transport').value);
    const penginapan = parseNumber(document.getElementById('biaya_penginapan').value);
    const harian = parseNumber(document.getElementById('uang_harian').value);
    const lainnya = parseNumber(document.getElementById('biaya_lainnya').value);
    const total = transport + penginapan + harian + lainnya;
    document.getElementById('total_biaya').value = total > 0 ? formatRupiah(total) : '';
}

function updateTotalDanFormat() {
    let ids = ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'];
    let total = 0;
    ids.forEach(function(id) {
        let el = document.getElementById(id);
        if (el) {
            let val = parseNumber(el.value);
            el.value = val > 0 ? formatRupiah(val) : '';
            total += val;
        }
    });
    let totalEl = document.getElementById('total_biaya');
    if (totalEl) {
        totalEl.value = total > 0 ? formatRupiah(total) : '';
    }
}

// Initialize form when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_berangkat').setAttribute('min', today);
    document.getElementById('tanggal_kembali').setAttribute('min', today);

    // Initial calculations
    calculateDuration();
    calculateTotal();
    
    // Add event listeners
    document.getElementById('tanggal_berangkat').addEventListener('change', function() {
        calculateDuration();
        validateDates();
    });
    
    document.getElementById('tanggal_kembali').addEventListener('change', function() {
        calculateDuration();
        validateDates();
    });

    // Event listener untuk semua input biaya
    ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'].forEach(function(id) {
        let el = document.getElementById(id);
        if (el) {
            // Saat input: hanya izinkan angka, lalu hitung total
            el.addEventListener('input', function(e) {
                let clean = el.value.replace(/[^\d]/g, '');
                el.value = clean;
                calculateTotal();
            });
            // Saat blur: format ribuan
            el.addEventListener('blur', function(e) {
                let val = parseNumber(el.value);
                el.value = val > 0 ? formatRupiah(val) : '';
                calculateTotal();
            });
            // Format saat load
            let val = parseNumber(el.value);
            el.value = val > 0 ? formatRupiah(val) : '';
        }
    });
    
    // Format total saat load
    let totalEl = document.getElementById('total_biaya');
    if (totalEl) {
        totalEl.value = totalEl.value > 0 ? formatRupiah(totalEl.value) : '';
    }
    
    // Hitung total biaya saat load
    calculateTotal();
});

// Navigation and UI functions
function toggleNotifications() {
    const dropdown = document.getElementById('notification-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const notificationDropdown = document.getElementById('notification-dropdown');
    if (notificationDropdown && !event.target.closest('.relative')) {
        notificationDropdown.classList.add('hidden');
    }
});

// Mobile menu toggle
const mobileMenuButton = document.getElementById('mobile-menu-button');
if (mobileMenuButton) {
    mobileMenuButton.addEventListener('click', () => {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu) {
            mobileMenu.classList.toggle('hidden');
        }
    });
}
