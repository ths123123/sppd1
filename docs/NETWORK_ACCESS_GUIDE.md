# 🌐 PANDUAN AKSES JARINGAN SPPD KPU

## 📱 Cara Mengakses SPPD KPU dari HP/Device Lain

### 🚀 **Metode 1: Menggunakan Script Batch (Recommended)**

1. **Jalankan Script Server:**
   ```bash
   # Double click file ini atau jalankan di terminal:
   scripts/start-server-network.bat
   ```

2. **Server akan berjalan di:**
   - **Local (PC):** http://localhost:8000
   - **Network (HP/Device lain):** http://192.168.18.5:8000

### 🔧 **Metode 2: Manual Command**

1. **Buka Terminal/PowerShell di folder proyek:**
   ```bash
   cd D:\pkl\SPPD-KPU
   ```

2. **Jalankan server:**
   ```bash
   php artisan serve --host=192.168.18.5 --port=8000
   ```

3. **Server akan muncul:**
   ```
   INFO  Server running on [http://192.168.18.5:8000].
   ```

### 📱 **Cara Akses dari HP:**

#### **1. Pastikan HP dan PC dalam jaringan yang sama:**
- ✅ HP dan PC terhubung ke WiFi yang sama
- ✅ Atau HP terhubung ke hotspot PC

#### **2. Buka browser di HP:**
- **Chrome/Safari/Firefox**
- **Masukkan URL:** `http://192.168.18.5:8000`

#### **3. Login dengan akun yang sudah ada:**
- **Admin:** admin@kpu.go.id / password
- **Sekretaris:** sekretaris@kpu.go.id / password
- **PPK:** ppk@kpu.go.id / password
- **Kasubbag:** kasubbag1@kpu.go.id / password

### 🔒 **Keamanan Jaringan:**

#### **1. Firewall Windows:**
- ✅ Port 8000 harus diizinkan
- ✅ Jika ada popup firewall, klik "Allow"

#### **2. Antivirus:**
- ✅ Pastikan antivirus tidak memblokir PHP/Laravel
- ✅ Tambahkan folder proyek ke whitelist jika perlu

### 🛠️ **Troubleshooting:**

#### **1. HP tidak bisa akses:**
```bash
# Cek apakah server berjalan:
netstat -an | findstr :8000

# Cek IP PC:
ipconfig
```

#### **2. Error "Connection refused":**
- ✅ Pastikan server berjalan
- ✅ Cek firewall Windows
- ✅ Restart server dengan: `Ctrl+C` lalu jalankan ulang

#### **3. Error "Page not found":**
- ✅ Pastikan URL benar: `http://192.168.18.5:8000`
- ✅ Pastikan tidak ada spasi atau typo

### 📋 **Daftar URL Penting:**

| Halaman | URL |
|---------|-----|
| **Login** | http://192.168.18.5:8000/login |
| **Dashboard** | http://192.168.18.5:8000/dashboard |
| **Approval SPPD** | http://192.168.18.5:8000/approval/pimpinan |
| **Daftar SPPD** | http://192.168.18.5:8000/travel-requests |
| **Analytics** | http://192.168.18.5:8000/analytics |

### 🎯 **Fitur yang Bisa Diakses dari HP:**

#### **✅ Semua Fitur Utama:**
- 📝 **Buat SPPD Baru**
- ✅ **Approve/Reject SPPD**
- 🔄 **Request Revisi**
- 📊 **Lihat Dashboard**
- 📈 **Analytics & Laporan**
- 👥 **Manajemen User**

#### **📱 Responsive Design:**
- ✅ **Mobile-friendly interface**
- ✅ **Touch-friendly buttons**
- ✅ **Optimized for small screens**

### 🚀 **Quick Start Commands:**

```bash
# 1. Start server untuk jaringan
php artisan serve --host=192.168.18.5 --port=8000

# 2. Atau gunakan script
scripts/start-server-network.bat

# 3. Cek status server
netstat -an | findstr :8000

# 4. Stop server
Ctrl+C
```

### 📞 **Support:**

Jika ada masalah, cek:
1. **Server berjalan:** `netstat -an | findstr :8000`
2. **IP PC benar:** `ipconfig`
3. **Jaringan sama:** HP dan PC WiFi sama
4. **Firewall:** Port 8000 diizinkan

---

## 🎉 **SELAMAT MENCOBA!**

**SPPD KPU sekarang bisa diakses dari HP dan device lain dalam jaringan yang sama!** 📱✨ 