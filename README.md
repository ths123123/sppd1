# SPPD-KPU - Sistem Pengelolaan Perjalanan Dinas KPU Kabupaten Cirebon

## 📋 Deskripsi Proyek

SPPD-KPU adalah sistem web untuk mengelola perjalanan dinas pegawai KPU Kabupaten Cirebon. Sistem ini memungkinkan pengajuan, persetujuan, dan monitoring perjalanan dinas dengan workflow yang terstruktur.

## ✨ Fitur Utama

### 🔐 **Manajemen User & Role**
- Multi-role system (Admin, Sekretaris, Kasubbag, PPK, Staff)
- Role-based access control
- User profile management

### 📝 **Pengelolaan SPPD**
- Form pengajuan SPPD yang lengkap
- Workflow persetujuan bertingkat
- Tracking status real-time
- Export PDF SPPD

### 📊 **Analytics & Laporan**
- Dashboard analytics
- Laporan perjalanan dinas
- Export data (Excel, PDF)
- Filter dan pencarian advanced

### 📁 **Manajemen Dokumen**
- Upload dokumen pendukung
- Dokumen SPPD pribadi
- Rekap seluruh dokumen (admin/sekretaris/kasubbag/ppk)
- Download dengan validasi akses

### ✅ **Workflow Persetujuan**
- Multi-level approval (Sekretaris → PPK)
- Status tracking (Diajukan → Disetujui/Ditolak/Revisi)
- Notifikasi real-time
- History persetujuan

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 10.x
- **Database**: PostgreSQL
- **Frontend**: Blade Templates, Tailwind CSS
- **Authentication**: Laravel Breeze
- **File Storage**: Laravel Storage
- **PDF Generation**: DomPDF
- **Testing**: PHPUnit

## 📦 Instalasi

### Prerequisites
- PHP 8.1+
- Composer
- PostgreSQL
- Node.js & NPM (untuk asset compilation)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/username/SPPD-KPU.git
cd SPPD-KPU
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Konfigurasi Database**
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sppd_kpu
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run Migrations & Seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Setup Storage**
```bash
php artisan storage:link
```

7. **Compile Assets**
```bash
npm run dev
```

8. **Start Server**
```bash
php artisan serve
```

## 👥 Default Users

Setelah menjalankan seeder, tersedia user default:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@kpu.go.id | password |
| Sekretaris | sekretaris@kpu.go.id | password |
| Kasubbag | kasubbag@kpu.go.id | password |
| PPK | ppk@kpu.go.id | password |
| Staff | staff@kpu.go.id | password |

## 🧪 Testing

```bash
# Run semua test
php artisan test

# Run test specific
php artisan test --filter=DocumentWorkflowTest
```

## 📁 Struktur Proyek

```
SPPD-KPU/
├── app/
│   ├── Http/Controllers/    # Controllers
│   ├── Models/             # Eloquent Models
│   ├── Services/           # Business Logic
│   └── Traits/             # Reusable Traits
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   └── views/             # Blade templates
├── routes/
│   └── web.php           # Web routes
└── tests/                # Test files
```

## 🔧 Konfigurasi

### Environment Variables
- `APP_ENV`: Environment (local, production)
- `DB_*`: Database configuration
- `MAIL_*`: Email configuration
- `FILESYSTEM_DISK`: File storage configuration

### Storage Configuration
- Documents disimpan di `storage/app/documents/`
- Public access melalui `public/storage/`

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure database
- [ ] Setup file storage
- [ ] Configure email
- [ ] Setup SSL certificate
- [ ] Configure web server (Apache/Nginx)

## 📝 License

Proyek ini dikembangkan untuk KPU Kabupaten Cirebon.

## 👨‍💻 Developer

Dikembangkan dengan ❤️ untuk KPU Kabupaten Cirebon

---

**Versi**: 1.0.0  
**Update Terakhir**: Juli 2025
