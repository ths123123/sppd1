# Panduan Deploy SPPD-KPU (Laravel + Vite) Gratis

Panduan ini untuk deploy aplikasi SPPD-KPU berbasis Laravel + Vite secara gratis, cocok untuk masa uji coba. Opsi utama: Railway, Render, dan Vercel (khusus frontend).

---

## 1. Railway (Rekomendasi Laravel Fullstack)
Railway mendukung Laravel, database, dan storage gratis (dengan batasan uji coba).

### Langkah-langkah:
1. **Daftar & Login Railway**
   - Kunjungi https://railway.app dan login dengan GitHub.
2. **Buat Project Baru**
   - Pilih "New Project" > "Deploy from GitHub repo".
   - Hubungkan repo SPPD-KPU.
3. **Atur Environment**
   - Railway otomatis mendeteksi Laravel. Jika tidak, atur manual:
     - Start command: `php artisan serve --host=0.0.0.0 --port $PORT`
     - Install command: `composer install && npm install && npm run build`
   - Tambahkan file `.env` (bisa upload manual atau edit di Railway):
     - `APP_KEY` (generate: `php artisan key:generate`)
     - `APP_ENV=production`
     - `APP_DEBUG=false`
     - `DB_CONNECTION=pgsql` (atau mysql, sesuai pilihan)
     - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (isi dari Railway DB)
     - `FILESYSTEM_DISK=public` (atau `local`)
     - Email, WhatsApp, dsb (jika perlu)
4. **Tambahkan Database**
   - Railway: Tambah plugin PostgreSQL/MySQL.
   - Copy env DB ke `.env` project.
   - Jalankan migrasi: di Railway console: `php artisan migrate --seed`
5. **Atur Storage**
   - Railway mendukung storage lokal, tapi tidak persistent. Untuk file upload, gunakan S3 (bisa pakai AWS Free Tier atau MinIO gratis).
   - Atur `.env`:
     - `FILESYSTEM_DISK=s3` (isi credential S3/MinIO)
6. **Deploy**
   - Railway akan auto-deploy setiap push ke branch utama.
   - Cek log dan akses URL Railway.

---

## 2. Render.com (Alternatif Laravel Fullstack)
Render juga gratis (dengan sleep jika idle).

### Langkah-langkah:
1. Daftar di https://render.com
2. New Web Service > Connect repo GitHub
3. Environment: `php`, build command: `composer install && npm install && npm run build`, start: `php artisan serve --host 0.0.0.0 --port $PORT`
4. Tambah PostgreSQL/MySQL (copy env ke `.env`)
5. Jalankan migrasi di shell Render: `php artisan migrate --seed`
6. Atur storage (S3/MinIO jika perlu upload)
7. Deploy & akses URL Render

---

## 3. Vercel (Frontend Saja)
Jika hanya ingin deploy frontend (Blade ke static, atau Vite build), bisa pakai Vercel gratis. Untuk backend tetap butuh Railway/Render.

---

## 4. Heroku (Legacy, Masih Bisa Untuk Uji Coba)
Heroku masih bisa untuk Laravel, tapi storage & DB terbatas.

---

## Tips & Catatan Penting
- **Email/WA Notifikasi:**
  - Railway/Render bisa kirim email (SMTP Gmail, Mailgun, dsb). Untuk WhatsApp, pastikan endpoint API bisa diakses publik.
- **File Upload:**
  - Storage lokal di Railway/Render tidak persistent. Untuk produksi, gunakan S3/MinIO.
- **APP_KEY:**
  - Jangan lupa generate dan set di `.env`.
- **APP_DEBUG:**
  - Set ke `false` di production.
- **Database:**
  - Jangan lupa migrasi & seeder.
- **Cek Log:**
  - Railway/Render punya fitur log untuk debug error.
- **Auto Deploy:**
  - Setiap push ke branch utama akan auto-deploy.

---

## Contoh `.env` Minimal
```
APP_NAME=SPPD-KPU
APP_ENV=production
APP_KEY=base64:xxxxxx
APP_DEBUG=false
APP_URL=https://your-app-url.railway.app

DB_CONNECTION=pgsql
DB_HOST=xxxx
DB_PORT=5432
DB_DATABASE=xxxx
DB_USERNAME=xxxx
DB_PASSWORD=xxxx

FILESYSTEM_DISK=public
```

---

## Selesai
Aplikasi siap diakses publik! Untuk produksi, upgrade ke plan berbayar jika butuh storage/database lebih besar.

Jika butuh bantuan lebih lanjut, cek dokumentasi Railway/Render atau hubungi maintainer projek. 