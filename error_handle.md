# Error Handling & Troubleshooting SPPD Form (Input Biaya)

## Masalah Umum

### 1. Angka Total Biaya Tidak Muncul Otomatis
- **Gejala:** Kolom total biaya tetap 0 atau kosong meskipun input biaya sudah diisi.
- **Penyebab:**
  - Event handler input biaya tidak berjalan.
  - Ada duplikasi event handler yang saling menimpa.
  - Fungsi `calculateTotal()` tidak dipanggil pada event input.
- **Solusi:**
  - Pastikan hanya ada satu event handler input untuk semua input biaya (`biaya_transport`, `biaya_penginapan`, `uang_harian`, `biaya_lainnya`).
  - Gunakan fungsi `handleCurrencyInput` yang langsung memformat ribuan dan memanggil `calculateTotal()` setiap kali input berubah.
  - Cek file: `public/js/forms/sppd-form-professional.js`

### 2. Format Ribuan Tidak Konsisten
- **Gejala:** Input biaya tidak terformat ribuan saat mengetik, atau format hilang setelah input.
- **Penyebab:**
  - Ada event handler yang hanya menghapus titik/non-digit tanpa memformat ulang.
  - Ada dua sistem event handler (duplikat) di file JS.
- **Solusi:**
  - Hapus semua event handler lama, gunakan hanya `setupCurrencyInputs()` dengan `handleCurrencyInput`.
  - Pastikan input biaya selalu diformat ribuan pada event input dan blur.
  - Cek file: `public/js/forms/sppd-form-professional.js`

### 3. Event Listener Conflict
- **Gejala:** Input biaya kadang tidak terformat, total tidak update, atau format hilang saat mengetik.
- **Penyebab:**
  - Ada dua atau lebih event handler pada input yang sama (misal: `.biaya-input`).
- **Solusi:**
  - Hapus semua event handler lama dengan cara clone element pada `setupCurrencyInputs()`.
  - Pastikan hanya satu event handler input yang aktif.

### 4. Form Tidak Bisa Submit/Validasi Error
- **Gejala:** Form tidak bisa submit, error validasi tidak jelas.
- **Penyebab:**
  - Input biaya masih mengandung karakter non-digit saat submit.
- **Solusi:**
  - Pada event submit, konversi semua input biaya ke angka murni (`parseNumber`) sebelum submit ke backend.

## Langkah Debugging
1. **Cek file:** `public/js/forms/sppd-form-professional.js` dan pastikan tidak ada event handler input duplikat.
2. **Cek HTML:** Input biaya harus bertipe `text`, punya id dan class yang konsisten.
3. **Cek Console:** Jika ada error JS, perbaiki syntax atau duplikasi event handler.
4. **Cek Form Submission:** Pastikan input biaya sudah dalam format angka sebelum submit.

## File Terkait
- `resources/views/travel_requests/create.blade.php`
- `public/js/forms/sppd-form-professional.js`
- (Jika ada) `resources/views/components/forms/budget-section.blade.php`

## Catatan
- Semua perubahan sudah didokumentasikan di sini. Jika ada error baru, tambahkan deskripsi dan solusi di file ini.
- Untuk debugging lebih lanjut, gunakan browser console (F12) dan cek event handler pada input biaya. 