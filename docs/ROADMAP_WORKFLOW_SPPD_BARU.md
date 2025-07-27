# ROADMAP WORKFLOW SPPD BARU

## 1. Ringkasan Alur Baru
- Hanya kasubbag yang dapat mengajukan SPPD.
- Kasubbag dapat memilih peserta SPPD dari user lain (kecuali admin dan kasubbag itu sendiri).
- Setelah pengajuan, approval berjenjang: Sekretaris → PPK.
- Admin hanya monitoring.

## 2. Tahapan Implementasi

### Tahap 1: Analisis & Perancangan
- [x] Review struktur tabel users dan travel_requests.
- [x] Rancang tabel pivot untuk peserta SPPD (travel_request_participants).
- [x] Update dokumentasi alur dan role.

### Tahap 2: Update Database
- [x] Buat migration tabel pivot peserta SPPD.
- [x] Update seeder user sesuai role baru (staff, kasubbag, ppk, sekertaris, admin).
- [x] Pastikan enum/validasi role di seluruh sistem sudah sesuai.

### Tahap 3: Update Backend
- [x] Batasi pengajuan SPPD hanya untuk kasubbag (controller & middleware).
- [x] Tambahkan fitur pemilihan peserta pada form pengajuan SPPD.
- [x] Simpan peserta ke tabel pivot saat submit.
- [x] Update approval flow: kasubbag → sekertaris → ppk.
- [x] Pastikan hanya kasubbag yang bisa edit peserta sebelum submit.

### Tahap 4: Update Frontend
- [x] Sembunyikan tombol/menu pengajuan SPPD untuk role selain kasubbag.
- [x] Tambahkan input multiselect peserta pada form pengajuan SPPD.
- [x] Tampilkan daftar peserta di detail SPPD.

### Tahap 5: Notifikasi & Validasi
- [x] Kirim notifikasi ke sekertaris & ppk saat ada pengajuan/approval.
- [x] Validasi peserta hanya bisa dipilih oleh kasubbag.
- [x] Validasi peserta tidak termasuk admin/kasubbag pengaju.

### Tahap 6: Testing & Dokumentasi
- [x] Uji coba end-to-end workflow baru.
- [x] Update dokumentasi dan user manual.

---

**Catatan:**
- Peserta SPPD hanya bisa dipilih saat pengajuan oleh kasubbag.
- Approval wajib berurutan: Sekretaris → PPK.
- Admin hanya monitoring.

_Status: SUDAH DITERAPKAN END-TO-END (JULI 2025)_ 