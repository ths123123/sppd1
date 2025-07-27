# Perpindahan Alur Pengajuan SPPD KPU

## Latar Belakang
Sesuai kebijakan terbaru dari KPU, alur pengajuan SPPD diubah agar **hanya role kasubbag** yang dapat melakukan pengajuan SPPD. Sebelumnya, pengajuan dapat dilakukan oleh staff, kasubbag, sekretaris, maupun ketua.

## Alur Sebelumnya
- Pengajuan SPPD dapat dilakukan oleh: staff, kasubbag, sekertaris, pkk.
- Approval berjenjang sesuai role pengaju:
  - Staff → Kasubbag → Sekretaris
  - Kasubbag → Sekretaris → PKK
  - Sekretaris → Kasubbag → PKK
  - PKK → Kasubbag → Sekretaris

## Alur Baru (Mulai Berlaku: [TANGGAL PERUBAHAN])
- **Hanya kasubbag** yang dapat mengajukan SPPD.
- Role lain (staff, sekertaris, pkk, admin) tidak dapat mengakses/mengajukan SPPD.
- Approval flow setelah kasubbag mengajukan:
  - Kasubbag → Sekretaris → PKK

## Dampak Perubahan
- Form pengajuan SPPD hanya dapat diakses oleh kasubbag.
- Tombol/menu pengajuan SPPD disembunyikan untuk role lain.
- Validasi di backend (controller) memastikan hanya kasubbag yang bisa submit.
- Proses approval tetap mengikuti urutan kasubbag → sekertaris → pkk.

## Catatan Implementasi
- Pembatasan akses dilakukan di controller dan view.
- Jika user non-kasubbag mencoba mengakses, akan diarahkan/ditolak dengan pesan error.
- Perubahan ini bertujuan untuk menyesuaikan sistem dengan kebijakan KPU terbaru.

---

_Dokumen ini dibuat otomatis oleh sistem pada [TANGGAL DOKUMENTASI]._ 