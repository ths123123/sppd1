# 📁 STRUKTUR FILE SISTEM SPPD KPU KABUPATEN CIREBON
## 🏛️ ENTERPRISE-GRADE FILE ORGANIZATION
### 📅 Last Updated: December 2024

---

## 🎯 OVERVIEW
Sistem SPPD menggunakan struktur file yang **profesional, konsisten, dan enterprise-grade** untuk memudahkan maintenance, development, dan onboarding developer baru.

---

## 📂 STRUKTUR BLADE VIEWS

### 🎨 **Templates (Manajemen Template Dokumen)**
```
resources/views/templates/
├── manage.blade.php          # Halaman utama manajemen template
├── form.blade.php            # Form tambah/edit template
└── preview.blade.php         # Preview template dokumen
```

### 🚀 **Travel Requests (SPPD)**
```
resources/views/travel_requests/
├── list.blade.php            # Daftar SPPD
├── form.blade.php            # Form tambah/edit SPPD
└── detail.blade.php          # Detail SPPD
```

### 👥 **User Management**
```
resources/views/users/
├── list.blade.php            # Daftar user
└── form.blade.php            # Form tambah/edit user
```

### 📊 **Dashboard**
```
resources/views/dashboard/
├── main.blade.php            # Dashboard utama
└── partials/
    └── charts.blade.php      # Komponen grafik
```

### 📋 **Documents**
```
resources/views/documents/
├── list.blade.php            # Daftar dokumen
├── all-documents.blade.php   # Semua dokumen (admin)
└── my-documents.blade.php    # Dokumen saya (user)
```

### ✅ **Approval**
```
resources/views/approval/
└── pimpinan/
    ├── list.blade.php        # Daftar approval pimpinan
    └── show.blade.php        # Detail approval
```

### 📈 **Analytics**
```
resources/views/analytics/
└── dashboard.blade.php       # Dashboard analytics
```

### 📄 **Laporan**
```
resources/views/laporan/
├── main.blade.php            # Halaman utama laporan
└── export_pdf.blade.php      # Template export PDF
```

### ⚙️ **Settings**
```
resources/views/settings/
└── main.blade.php            # Halaman pengaturan
```

### 👤 **Profile**
```
resources/views/profile/
├── main.blade.php            # Halaman utama profil
├── edit.blade.php            # Edit profil
├── show.blade.php            # Tampilkan profil
└── partials/                 # Komponen profil
```

### 🔍 **Review**
```
resources/views/review/
└── list.blade.php            # Daftar review
```

### 🔐 **Authentication**
```
resources/views/auth/
├── login.blade.php           # Halaman login
└── register.blade.php        # Halaman register
```

### 🧩 **Components**
```
resources/views/components/
├── navbar.blade.php          # Navbar utama
├── toast.blade.php           # Notifikasi toast
└── navigation/
    └── mobile-menu.blade.php # Menu mobile
```

### 🏗️ **Layouts**
```
resources/views/layouts/
└── app.blade.php             # Layout utama aplikasi
```

---

## 🎛️ CONTROLLERS

### 📋 **Main Controllers**
```
app/Http/Controllers/
├── TemplateDokumenController.php      # Manajemen template
├── TravelRequestController.php        # SPPD management
├── UserManagementController.php       # User management
├── DashboardController.php            # Dashboard
├── DocumentController.php             # Document management
├── ApprovalPimpinanController.php     # Approval workflow
├── AnalyticsController.php            # Analytics & reports
├── LaporanController.php              # Laporan
├── SettingsController.php             # Settings
├── ProfileController.php              # Profile management
├── ReviewController.php               # Review system
└── Auth/
    ├── AuthenticatedSessionController.php # Authentication
    └── PasswordController.php         # Password management
```

---

## 🔧 SERVICES

### 🛠️ **Business Logic Services**
```
app/Services/
├── TemplateDokumenService.php         # Template business logic
├── TravelRequestService.php           # SPPD business logic
├── UserManagementService.php          # User business logic
├── DashboardService.php               # Dashboard business logic
├── DocumentService.php                # Document business logic
├── ApprovalService.php                # Approval business logic
├── AnalyticsService.php               # Analytics business logic
├── LaporanService.php                 # Laporan business logic
└── ExportService.php                  # Export functionality
```

---

## 📊 MODELS

### 🗃️ **Database Models**
```
app/Models/
├── TemplateDokumen.php                # Template model
├── TravelRequest.php                  # SPPD model
├── User.php                           # User model
├── Document.php                       # Document model
├── Approval.php                       # Approval model
└── ActivityLog.php                    # Audit trail
```

---

## 🎨 ASSETS

### 🎨 **CSS Styles**
```
resources/css/
└── app.css                           # Main stylesheet
```

### ⚡ **JavaScript**
```
resources/js/
├── app.js                            # Main JavaScript
└── dashboard/
    └── charts.js                     # Dashboard charts
```

---

## 🛣️ ROUTES

### 🌐 **Web Routes**
```
routes/
├── web.php                           # Main web routes
├── api.php                           # API routes
└── auth.php                          # Authentication routes
```

---

## 📝 NAMING CONVENTIONS

### 🎯 **File Naming Rules**
- **Controllers**: `{Fitur}Controller.php` (PascalCase)
- **Services**: `{Fitur}Service.php` (PascalCase)
- **Models**: `{Fitur}.php` (PascalCase)
- **Views**: `{aksi}.blade.php` (lowercase, descriptive)
- **Components**: `{nama}.blade.php` (lowercase, descriptive)

### 📋 **View Naming Standards**
- `list.blade.php` - Daftar data
- `form.blade.php` - Form input/edit
- `detail.blade.php` - Detail data
- `main.blade.php` - Halaman utama fitur
- `manage.blade.php` - Manajemen data
- `dashboard.blade.php` - Dashboard fitur

---

## 🔄 MIGRATION SUMMARY

### ✅ **Completed Renames**
1. **Templates**: `index.blade.php` → `manage.blade.php`
2. **Travel Requests**: `index.blade.php` → `list.blade.php`
3. **Users**: `index.blade.php` → `list.blade.php`
4. **Dashboard**: `index.blade.php` → `main.blade.php`
5. **Documents**: `index.blade.php` → `list.blade.php`
6. **Approval**: `index.blade.php` → `list.blade.php`
7. **Analytics**: `index.blade.php` → `dashboard.blade.php`
8. **Settings**: `index.blade.php` → `main.blade.php`
9. **Profile**: `index.blade.php` → `main.blade.php`
10. **Review**: `index.blade.php` → `list.blade.php`
11. **Laporan**: `index.blade.php` → `main.blade.php`

### 🔧 **Updated References**
- All controller methods updated
- All route references maintained
- All navigation links preserved
- Cache cleared and system tested

---

## 🎯 BENEFITS

### ✅ **Professional Structure**
- **Konsisten**: Pola penamaan yang seragam
- **Deskriptif**: Nama file jelas menunjukkan fungsi
- **Scalable**: Mudah dikembangkan untuk fitur baru
- **Maintainable**: Mudah dipelihara dan debug

### 🏛️ **Enterprise-Grade**
- **Government Compliance**: Sesuai standar pemerintah
- **Security**: Struktur yang mendukung keamanan
- **Performance**: Optimized untuk performa tinggi
- **Documentation**: Terdokumentasi dengan baik

---

**🏛️ "Melayani Dengan Integritas - Serving With Integrity"** 