/**
 * FIXED: Currency Real-time Formatting & Auto Calculate
 * Masalah utama: Event listener conflict dan format currency inconsistent
 */

function formatRupiah(angka) {
    if (!angka) return '';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseNumber(str) {
    if (!str) return 0;
    return parseInt((str+'').replace(/[^\d]/g, ''), 10) || 0;
}

// FIXED: Satu function untuk calculate total
function calculateTotal() {
    const fields = ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'];
    let total = 0;
    
    fields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element && element.value) {
            const value = parseNumber(element.value);
            total += value;
        }
    });
    
    const totalInput = document.getElementById('total_biaya');
    if (totalInput) {
        totalInput.value = total > 0 ? formatRupiah(total) : '0';
        
        // Visual feedback
        if (total > 0) {
            totalInput.style.backgroundColor = '#f0fdf4';
            totalInput.style.borderColor = '#22c55e';
            totalInput.style.fontWeight = '600';
        }
    }
    
    return total;
}

// FIXED: Real-time formatting saat mengetik
function handleCurrencyInput(e) {
    let value = e.target.value.replace(/[^\d]/g, ''); // Hapus semua kecuali angka
    
    if (value) {
        e.target.value = formatRupiah(value); // Format dengan titik ribuan
    } else {
        e.target.value = '';
    }
    
    // Update total otomatis
    calculateTotal();
}

// FIXED: Setup currency inputs tanpa conflict
function setupCurrencyInputs() {
    const currencyFields = ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'];
    
    currencyFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (!input) return;
        
        // HAPUS semua event listener lama dengan clone
        const newInput = input.cloneNode(true);
        input.parentNode.replaceChild(newInput, input);
        
        // TAMBAH event listener baru yang benar
        newInput.addEventListener('input', handleCurrencyInput);
        
        // Ketika blur (keluar dari input), pastikan format benar
        newInput.addEventListener('blur', function() {
            const value = parseNumber(this.value);
            this.value = value > 0 ? formatRupiah(value) : '';
            calculateTotal();
        });
        
        // Ketika focus (masuk ke input), tetap tampilkan format ribuan
        newInput.addEventListener('focus', function() {
            // Tidak perlu ubah format, biarkan user lihat format ribuan
            const cursorPos = this.selectionStart;
            setTimeout(() => {
                this.setSelectionRange(cursorPos, cursorPos);
            }, 0);
        });
        
        // Hanya boleh input angka
        newInput.addEventListener('keypress', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].includes(e.keyCode)) return;
            
            // Block jika bukan angka
            if (e.keyCode < 48 || e.keyCode > 57) {
                e.preventDefault();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 dulu
    if (typeof $ !== 'undefined' && $('#participants').length) {
        $('#participants').select2({
            placeholder: 'Pilih peserta SPPD...',
            allowClear: true,
            closeOnSelect: false,
            templateSelection: function() { return null; }
        });
    }
    
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
    
    // Setup peserta terpilih functionality
    $('#btn-lihat-peserta').on('click', function() {
        $('#daftar-peserta-terpilih').toggle();
        updateDaftarPeserta();
    });
    
    $('#participants').on('change', function() {
        if ($('#daftar-peserta-terpilih').is(':visible')) {
            updateDaftarPeserta();
        }
    });
    
    function updateDaftarPeserta() {
        var selected = $('#participants').find('option:selected');
        var list = '';
        if(selected.length === 0) {
            list = '<li style="color:#b71c1c;">Belum ada peserta dipilih</li>';
        } else {
            selected.each(function() {
                var nama = $(this).data('nama');
                var role = $(this).data('role');
                var val = $(this).val();
                list += '<li style="display:flex;align-items:center;gap:8px;">' + nama + ' <span style="color:#888; font-size:0.95em;">(' + role + ')</span>' +
                    '<button type="button" class="remove-peserta" data-val="'+val+'" style="background:none;border:none;color:#b71c1c;font-size:1.1em;cursor:pointer;">&times;</button></li>';
            });
        }
        $('#daftar-peserta-terpilih').html(list);
    }
    
    $(document).on('click', '.remove-peserta', function() {
        var val = $(this).data('val');
        var $select = $('#participants');
        var values = $select.val() || [];
        values = values.filter(function(v) { return v != val; });
        $select.val(values).trigger('change');
        updateDaftarPeserta();
    });
});