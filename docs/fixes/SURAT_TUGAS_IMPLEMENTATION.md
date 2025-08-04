# Implementasi Surat Tugas

## Perubahan yang Dilakukan

### 1. Format Surat Tugas Baru
- **File**: `resources/views/travel_requests/partials/surat_persetujuan.blade.php`
- **Perubahan**: Mengubah format dari "Surat Persetujuan Perjalanan Dinas" menjadi "Surat Tugas"
- **Implementasi**:
  - Header: Logo KPU + "KOMISI PEMILIHAN UMUM KABUPATEN CIREBON"
  - Judul: "SURAT TUGAS"
  - Nomor: Menggunakan `$travelRequest->kode_sppd` atau default
  - Struktur: Menimbang, Dasar, Memberi Tugas, Untuk, Anggaran
  - Tanda tangan: Sekretaris KPU

### 2. Struktur Konten Surat Tugas
1. **Menimbang**:
   - a. Dalam Rangka Tertibnya Administrasi;
   - b. Sebagaimana dimaksud dalam huruf a perlu dibuat Surat Tugas.

2. **Dasar**:
   - Surat KPU Provinsi Jawa Barat Nomor 21/PY.02-Und/32/2025 tanggal 7 Maret 2025 Perihal Undangan

3. **Memberi Tugas Kepada**:
   - Menampilkan data peserta dari database
   - Format: Nama/NIP, Pangkat/Gol., Jabatan
   - Fallback ke data pengaju jika tidak ada peserta

4. **Untuk**:
   - Melaksanakan Perjalanan Dinas dalam rangka [keperluan dari database]
   - Selama [lama_perjalanan] hari pada tanggal [tanggal_berangkat]
   - Bertempat di [tujuan dari database]

5. **Anggaran**:
   - Biaya dibebankan pada Anggaran Hibah Pilkada 2024 KPU Kabupaten Cirebon

### 3. Data dari Database
- **Nomor Surat**: `$travelRequest->kode_sppd`
- **Peserta**: `$travelRequest->participants` (nama, NIP, pangkat, golongan, jabatan)
- **Keperluan**: `$travelRequest->keperluan`
- **Tujuan**: `$travelRequest->tujuan`
- **Tanggal**: `$travelRequest->tanggal_berangkat` dan `$travelRequest->tanggal_kembali`
- **Lama Perjalanan**: `$travelRequest->lama_perjalanan`
- **Sekretaris**: Data dari `$approval->approver` atau default

### 4. UI Changes
- **File**: `resources/views/travel_requests/show.blade.php`
- **Perubahan**: Mengubah teks tombol dari "Download Surat Persetujuan Perjalanan Dinas" menjadi "Download Surat Tugas"

### 5. Controller Changes
- **File**: `app/Http/Controllers/TravelRequestController.php`
- **Perubahan**:
  - Nama file download: "Surat-Tugas-[nama].pdf"
  - Pesan error: "Surat tugas hanya bisa diunduh jika status sudah completed"
  - Pesan error export: "Terjadi kesalahan saat export surat tugas"

## Hasil
- ✅ Format surat sesuai dengan contoh yang diberikan
- ✅ Data diambil dari database (peserta, keperluan, tujuan, tanggal, dll)
- ✅ UI menampilkan "Download Surat Tugas"
- ✅ File download bernama "Surat-Tugas-[nama].pdf"
- ✅ Pesan error yang konsisten

## Testing
- Export surat tugas berfungsi normal
- Data peserta ditampilkan dengan format yang tepat
- Fallback ke data pengaju jika tidak ada peserta
- Format surat sesuai standar pemerintah 