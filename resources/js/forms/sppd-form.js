/**
 * SPPD Form JavaScript Functions
 * Handles automatic calculations, validations, and form interactions
 */

import TomSelect from 'tom-select';

// Global format functions
function formatRupiah(angka) {
    if (!angka) return '';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseNumber(str) {
    if (!str) return 0;
    // Hanya ambil digit angka, hilangkan semua karakter non-digit
    return parseInt((str+'').replace(/[^\d]/g, ''), 10) || 0;
}

// Format currency input untuk semua field biaya
function formatCurrencyInput(input) {
    let value = input.value.replace(/[^\d]/g, ''); // Hanya angka
    if (value) {
        input.value = formatRupiah(value);
    } else {
        input.value = '0';
    }
}

// Setup input formatting untuk semua field currency
function setupCurrencyInputs() {
    const currencyFields = ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'];
    
    currencyFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input) {
            // Remove any existing pattern attribute
            input.removeAttribute('pattern');
            
            // Format on input
            input.addEventListener('input', function() {
                formatCurrencyInput(this);
                updateTotalDanFormat();
            });
            
            // Format on blur
            input.addEventListener('blur', function() {
                if (!this.value || this.value === '0') {
                    this.value = '0';
                }
            });
            
            // Prevent non-numeric input
            input.addEventListener('keypress', function(e) {
                // Allow: backspace, delete, tab, escape, enter
                if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                    // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true)) {
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }
    });
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
            forceRecalculateTotal();
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
        
        // Recalculate total
        forceRecalculateTotal();
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
            forceRecalculateTotal();
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
        notification.style.fontSize = '14px';
        notification.style.maxWidth = '300px';
        document.body.appendChild(notification);
    }

    notification.textContent = message;
    notification.style.display = 'block';
    notification.style.opacity = '1';

    // Hide after 4 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 300);
    }, 4000);
}

// Force recalculate total - utility function
function forceRecalculateTotal() {
    setTimeout(() => {
        calculateTotal();
    }, 100);
}

// Biaya penginapan manual - tidak ada auto-calculate
// User harus mengisi sendiri sesuai kebutuhan

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

// Quick calculate all costs (kecuali penginapan yang manual)
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

    // Auto calculate transportasi dan uang harian (penginapan tetap manual)
    autoSuggestTransportCost();
    setTimeout(() => autoCalculateUangHarian(durasi), 200);

    showCalculationNotification('✅ Biaya transportasi dan uang harian dihitung otomatis. Biaya penginapan silakan isi manual.');
}

// Reset all costs
function resetAllCosts() {
    if (confirm('Yakin ingin reset semua biaya?')) {
        ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'].forEach(id => {
            document.getElementById(id).value = '';
        });
        forceRecalculateTotal();
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

🏨 PENGINAPAN (ISI MANUAL):
• Hotel Standar: Rp 500-800rb/malam
• Hotel Bintang 3: Rp 800rb-1.2jt/malam
• Guest House: Rp 300-500rb/malam

💰 UANG HARIAN:
• Dalam Kota: Rp 200-300rb/hari
• Luar Kota: Rp 300-500rb/hari
• Luar Provinsi: Rp 400-600rb/hari

Klik "Hitung Semua Otomatis" untuk estimasi transportasi & uang harian!
Biaya penginapan silakan isi manual sesuai kebutuhan.
    `;

    alert(guideText);
}

// Auto calculate total budget - IMPROVED VERSION
function calculateTotal() {
    // Get values from all budget inputs
    const transport = parseNumber(document.getElementById('biaya_transport')?.value || '0');
    const penginapan = parseNumber(document.getElementById('biaya_penginapan')?.value || '0');
    const harian = parseNumber(document.getElementById('uang_harian')?.value || '0');
    const lainnya = parseNumber(document.getElementById('biaya_lainnya')?.value || '0');
    
    const total = transport + penginapan + harian + lainnya;
    
    const totalInput = document.getElementById('total_biaya');
    if (totalInput) {
        if (total > 0) {
            totalInput.value = formatRupiah(total);
            
            // Visual feedback ketika total berubah
            totalInput.style.backgroundColor = '#f0fdf4'; // Light green
            totalInput.style.borderColor = '#22c55e'; // Green border
            totalInput.style.transition = 'all 0.3s ease';
            
            // Show calculation notification
            showCalculationNotification('💰 Total biaya: Rp ' + formatRupiah(total));
            
            setTimeout(() => {
                totalInput.style.backgroundColor = '#f9fafb'; // Back to gray
                totalInput.style.borderColor = '#d1d5db'; // Back to gray border
            }, 2000);
        } else {
            totalInput.value = '';
        }
    }
    
    return total;
}

function updateTotalDanFormat() {
    let ids = ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'];
    let total = 0;
    let filledCount = 0;
    
    ids.forEach(function(id) {
        let el = document.getElementById(id);
        if (el) {
            let val = parseNumber(el.value);
            if (val > 0) {
                el.value = formatRupiah(val);
                total += val;
                filledCount++;
            } else {
                el.value = '';
            }
        }
    });
    
    let totalEl = document.getElementById('total_biaya');
    if (totalEl) {
        if (total > 0) {
            totalEl.value = formatRupiah(total);
            
            // Visual feedback untuk total
            totalEl.style.backgroundColor = '#f0fdf4';
            totalEl.style.borderColor = '#22c55e';
            totalEl.style.transition = 'all 0.3s ease';
            
            // Show completion status
            if (filledCount >= 3) { // At least 3 fields filled
                showCalculationNotification('✅ Total biaya: Rp ' + formatRupiah(total) + ' (dari ' + filledCount + ' komponen)');
            } else {
                showCalculationNotification('📊 Total sementara: Rp ' + formatRupiah(total));
            }
            
            setTimeout(() => {
                totalEl.style.backgroundColor = '#f9fafb';
                totalEl.style.borderColor = '#d1d5db';
            }, 2000);
        } else {
            totalEl.value = '';
        }
    }
}

// Inisialisasi Tom Select untuk peserta
function initTomSelectPeserta() {
    const pesertaSelect = document.getElementById('participants');
    const pesertaContainer = document.getElementById('selected-participants');
    if (!pesertaSelect || !pesertaContainer) return;
    // Inisialisasi Tom Select
    new TomSelect(pesertaSelect, {
        plugins: ['remove_button'],
        placeholder: 'Pilih peserta SPPD...'
    });
    // Render chip peserta
    function renderChips() {
        const selected = Array.from(pesertaSelect.selectedOptions);
        pesertaContainer.innerHTML = '';
        if (selected.length === 0) {
            pesertaContainer.innerHTML = '<span class="text-red-500">Belum ada peserta dipilih</span>';
            return;
        }
        selected.forEach(opt => {
            const nama = opt.getAttribute('data-nama');
            const role = opt.getAttribute('data-role');
            const chip = document.createElement('span');
            chip.className = 'chip flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full mb-1';
            chip.innerHTML = `<i class='fas fa-user mr-2'></i> ${nama} <span class='ml-1 text-xs text-gray-500'>(${role})</span>`;
            pesertaContainer.appendChild(chip);
        });
    }
    pesertaSelect.addEventListener('change', renderChips);
    renderChips();
}

// Peserta Modal Logic
function renderPesertaTable(selectedIds, users) {
    const table = document.createElement('table');
    table.className = 'min-w-full text-sm border rounded-lg bg-white';
    table.innerHTML = `<thead><tr class='bg-gray-100'><th class='p-2'>Avatar</th><th class='p-2'>Nama</th><th class='p-2'>Role</th></tr></thead><tbody></tbody>`;
    const tbody = table.querySelector('tbody');
    if (!selectedIds.length) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan='3' class='text-center text-red-500 p-2'>Belum ada peserta dipilih</td>`;
        tbody.appendChild(tr);
    } else {
        selectedIds.forEach(id => {
            const user = users.find(u => u.id == id);
            if (user) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td class='p-2'><img src='${user.avatar_url}' class='w-8 h-8 rounded-full object-cover border'></td><td class='p-2 font-medium'>${user.name}</td><td class='p-2 text-gray-600'>${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</td>`;
                tbody.appendChild(tr);
            }
        });
    }
    return table;
}

// Inisialisasi modal peserta
function initPesertaModal() {
    console.log('Initializing peserta modal...');
    
    // Debug: Cek apakah modal ada di DOM
    const modalElement = document.getElementById('peserta-modal');
    console.log('Modal element found by ID:', !!modalElement);
    
    if (!modalElement) {
        const modalByName = document.querySelector('[name="peserta-modal"]');
        console.log('Modal element found by name:', !!modalByName);
    }
    
    // Data user dari window.users (inject dari blade)
    const users = window.users || [];
    console.log('Users data available:', users.length > 0);
    let selectedPeserta = (window.selectedPeserta || []).slice();
    
    // Cari elemen-elemen terkait
    const pesertaHidden = document.getElementById('participants-hidden');
    const pesertaTable = document.getElementById('peserta-terpilih-table');
    const btnLihat = document.getElementById('btn-lihat-peserta');
    const btnPilih = document.getElementById('btn-pilih-peserta');
    
    console.log('Elements found:', {
        pesertaHidden: !!pesertaHidden,
        pesertaTable: !!pesertaTable,
        btnLihat: !!btnLihat,
        btnPilih: !!btnPilih
    });
    
    // Cari modal dengan berbagai cara untuk memastikan ditemukan
    let modalEl = document.getElementById('peserta-modal');
    if (!modalEl) {
        modalEl = document.querySelector('[name="peserta-modal"]');
    }
    
    // Jika elemen tidak ditemukan, tampilkan warning dan keluar
    if (!pesertaHidden || !pesertaTable) {
        console.warn('Peserta elements not found, skipping initialization');
        return;
    }
    
    // Fungsi untuk update tabel peserta terpilih
    function updatePesertaTable() {
        if (!pesertaTable) return;
        
        pesertaTable.innerHTML = '';
        if (selectedPeserta.length) {
            pesertaTable.appendChild(renderPesertaTable(selectedPeserta, users));
            if (btnLihat) btnLihat.style.display = '';
        } else {
            pesertaTable.innerHTML = '<span class="text-red-500">Belum ada peserta dipilih</span>';
            if (btnLihat) btnLihat.style.display = 'none';
        }
        if (pesertaHidden) {
            // Clear existing hidden inputs
            const existingInputs = document.querySelectorAll('input[name="participants[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Create new hidden inputs for each participant
            selectedPeserta.forEach(participantId => {
                const newInput = document.createElement('input');
                newInput.type = 'hidden';
                newInput.name = 'participants[]';
                newInput.value = participantId;
                pesertaHidden.parentNode.appendChild(newInput);
            });
        }
    }
    
    // Tambahkan event listener untuk tombol OK di modal
    const btnOk = document.getElementById('btn-ok-peserta');
    if (btnOk) {
        console.log('OK button found, attaching event listener');
        btnOk.addEventListener('click', function() {
            console.log('OK button clicked');
            const checked = Array.from(document.querySelectorAll('.peserta-checkbox:checked')).map(cb => cb.value);
            console.log('Selected participants:', checked);
            selectedPeserta = checked;
            updatePesertaTable();
            
            // Close modal - coba berbagai metode untuk kompatibilitas maksimal
            if (modalEl) {
                // Metode 1: Gunakan Alpine.js
                if (typeof window.Alpine !== 'undefined') {
                    window.Alpine.dispatch(modalEl, 'close-modal', { detail: 'peserta-modal' });
                }
                
                // Metode 2: Gunakan _modalInstance jika ada
                if (modalEl._modalInstance) {
                    modalEl._modalInstance.hide();
                }
                
                // Metode 3: Dispatch event
                document.dispatchEvent(new CustomEvent('close-modal', { detail: 'peserta-modal' }));
            }
        });
    } else {
        console.warn('OK button not found');
    }
    
    // Search peserta di modal
    const searchInput = document.getElementById('search-peserta');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.peserta-row').forEach(row => {
                row.style.display = row.dataset.nama.includes(val) ? '' : 'none';
            });
        });
    }
    
    // Tambahkan event listener untuk tombol pilih dan lihat peserta
    if (btnPilih) {
        console.log('Pilih button found, attaching event listener');
        btnPilih.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Pilih button clicked');
            
            // Coba berbagai metode untuk membuka modal
            if (modalEl) {
                console.log('Opening modal using available methods');
                // Metode 1: Gunakan Alpine.js
                if (typeof window.Alpine !== 'undefined') {
                    console.log('Using Alpine.js');
                    window.Alpine.dispatch(modalEl, 'open-modal', { detail: 'peserta-modal' });
                }
                
                // Metode 2: Gunakan _modalInstance jika ada
                if (modalEl._modalInstance) {
                    console.log('Using _modalInstance');
                    modalEl._modalInstance.show();
                }
                
                // Metode 3: Dispatch event
                console.log('Using CustomEvent');
                document.dispatchEvent(new CustomEvent('open-modal', { detail: 'peserta-modal' }));
                
                // Metode 4: Coba langsung dengan style
                try {
                    console.log('Trying direct style manipulation');
                    modalEl.style.display = 'flex';
                    modalEl.classList.add('show');
                } catch (err) {
                    console.error('Error showing modal:', err);
                }
            } else {
                console.error('Modal element not found for opening');
            }
        });
    } else {
        console.warn('Pilih button not found');
    }
    
    if (btnLihat && btnLihat !== btnPilih) {
        btnLihat.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Gunakan kode yang sama dengan btnPilih
            if (modalEl) {
                if (typeof window.Alpine !== 'undefined') {
                    window.Alpine.dispatch(modalEl, 'open-modal', { detail: 'peserta-modal' });
                } else if (modalEl._modalInstance) {
                    modalEl._modalInstance.show();
                } else {
                    document.dispatchEvent(new CustomEvent('open-modal', { detail: 'peserta-modal' }));
                    
                    // Coba langsung dengan style
                    try {
                        modalEl.style.display = 'flex';
                        modalEl.classList.add('show');
                    } catch (err) {
                        console.error('Error showing modal:', err);
                    }
                }
            }
        });
    }
    
    // Inisialisasi awal
    updatePesertaTable();
    console.log('Peserta modal initialized successfully');
}

// Initialize after a small delay to ensure DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_berangkat').setAttribute('min', today);
    document.getElementById('tanggal_kembali').setAttribute('min', today);

    // Setup currency inputs
    setupCurrencyInputs();

    // Initial calculation
    calculateTotal();
    
    // Format existing values on load
    ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.value) {
            const val = parseNumber(el.value);
            el.value = val > 0 ? formatRupiah(val) : '';
        }
    });
    
    // FIXED: Form submission - clean format sebelum submit
    const form = document.getElementById('sppd-form');
    if (form) {
        form.addEventListener('submit', function() {
            // Convert semua currency fields ke angka murni untuk database
            ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya', 'total_biaya'].forEach(id => {
                const input = document.getElementById(id);
                if (input && input.value) {
                    input.value = parseNumber(input.value).toString();
                }
            });
        });
    }

    // Inisialisasi modal peserta dengan delay untuk memastikan DOM siap
    setTimeout(initPesertaModal, 500);

    // Add event listeners for dates
    document.getElementById('tanggal_berangkat').addEventListener('change', function() {
        calculateDuration();
        validateDates();
    });

    document.getElementById('tanggal_kembali').addEventListener('change', function() {
        calculateDuration();
        validateDates();
    });

    // Add event listener for transportasi
    document.getElementById('transportasi').addEventListener('change', function() {
        autoSuggestTransportCost();
    });

    // Event listener untuk semua input biaya - IMPROVED
    ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'].forEach(function(id) {
        let el = document.getElementById(id);
        if (el) {
            // Saat input: hanya izinkan angka, lalu hitung total
            el.addEventListener('input', function(e) {
                let clean = el.value.replace(/[^\d]/g, '');
                el.value = clean;
                
                // Real-time calculation
                setTimeout(() => {
                    calculateTotal();
                }, 100);
            });
            
            // Saat blur: format ribuan dan hitung total
            el.addEventListener('blur', function(e) {
                let val = parseNumber(el.value);
                el.value = val > 0 ? formatRupiah(val) : '';
                
                // Delay untuk memastikan format selesai
                setTimeout(() => {
                    calculateTotal();
                }, 200);
            });
            
            // Saat focus: hilangkan format untuk edit
            el.addEventListener('focus', function(e) {
                let val = parseNumber(el.value);
                el.value = val > 0 ? val.toString() : '';
            });
            
            // Format saat load
            let val = parseNumber(el.value);
            el.value = val > 0 ? formatRupiah(val) : '';
        }
    });

    // Format total saat load
    let totalEl = document.getElementById('total_biaya');
    if (totalEl) {
        let totalVal = parseNumber(totalEl.value);
        totalEl.value = totalVal > 0 ? formatRupiah(totalVal) : '';
        totalEl.readOnly = true; // Make total readonly
    }

    // Hitung total biaya saat load
    setTimeout(() => {
        calculateTotal();
    }, 300);
    
    // Add periodic calculation check
    setInterval(() => {
        calculateTotal();
    }, 3000); // Check every 3 seconds

    initTomSelectPeserta();

    // Data user dari window.users (inject dari blade)
    const users = window.users || [];
    let selectedPeserta = (window.selectedPeserta || []).slice();
    const pesertaHidden = document.getElementById('participants-hidden');
    const pesertaTable = document.getElementById('peserta-terpilih-table');
    const btnLihat = document.getElementById('btn-lihat-peserta');
    function updatePesertaTable() {
        pesertaTable.innerHTML = '';
        if (selectedPeserta.length) {
            pesertaTable.appendChild(renderPesertaTable(selectedPeserta, users));
            btnLihat.style.display = '';
        } else {
            pesertaTable.innerHTML = '<span class="text-red-500">Belum ada peserta dipilih</span>';
            btnLihat.style.display = 'none';
        }
        
        // Clear existing hidden inputs
        const existingInputs = document.querySelectorAll('input[name="participants[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Create new hidden inputs for each participant
        selectedPeserta.forEach(participantId => {
            const newInput = document.createElement('input');
            newInput.type = 'hidden';
            newInput.name = 'participants[]';
            newInput.value = participantId;
            pesertaHidden.parentNode.appendChild(newInput);
        });
    }
    // Modal OK
    document.getElementById('btn-ok-peserta').addEventListener('click', function() {
        const checked = Array.from(document.querySelectorAll('.peserta-checkbox:checked')).map(cb => cb.value);
        selectedPeserta = checked;
        updatePesertaTable();
        document.querySelector('[name=peserta-modal]').dispatchEvent(new CustomEvent('close-modal'));
    });
    // Search peserta
    document.getElementById('search-peserta').addEventListener('input', function(e) {
        const val = e.target.value.toLowerCase();
        document.querySelectorAll('.peserta-row').forEach(row => {
            row.style.display = row.dataset.nama.includes(val) ? '' : 'none';
        });
    });
    // Inisialisasi awal
    updatePesertaTable();
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
