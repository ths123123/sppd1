# 🧹 PANDUAN MEMBERSIHKAN DATA DUMMY SPPD KPU

## 📋 **Informasi Umum**

Dokumen ini menjelaskan cara membersihkan data dummy/temporary dari database SPPD KPU untuk keperluan testing dan development.

## 🎯 **Data yang Akan Dihapus**

### **1. Dummy Travel Requests:**
- ✅ SPPD dengan kode `SPD/2025/07/xxx` (dari DummyTravelRequestSeeder)
- ✅ SPPD yang dibuat oleh user `kasubbag1@kpu.go.id` dalam 7 hari terakhir
- ✅ Semua data terkait (approvals, notifications, participants)

### **2. Data Terkait yang Dihapus:**
- ✅ **Approvals** - Data persetujuan SPPD
- ✅ **Notifications** - Notifikasi terkait SPPD
- ✅ **Travel Request Participants** - Peserta perjalanan
- ✅ **Travel Requests** - Data SPPD utama

## 🚀 **Metode 1: Menggunakan Artisan Command (Recommended)**

### **Command dengan Konfirmasi:**
```bash
php artisan cleanup:dummy-data
```

### **Command Tanpa Konfirmasi (Force):**
```bash
php artisan cleanup:dummy-data --force
```

### **Output yang Diharapkan:**
```
🧹 Starting cleanup of dummy data...
ℹ️  No dummy travel requests found

📊 Database Statistics after cleanup:
   • Travel Requests: 0
   • Approvals: 0
   • Notifications: 0

🎉 Cleanup completed successfully!
```

## 🔧 **Metode 2: Menggunakan Tinker**

### **Quick Delete dengan Tinker:**
```bash
php artisan tinker --execute="App\Models\TravelRequest::where('kode_sppd', 'like', 'SPD/2025/07/%')->delete(); echo 'Dummy travel requests deleted successfully!';"
```

### **Manual Tinker:**
```bash
php artisan tinker
```

```php
// Di dalam tinker:
$dummyRequests = App\Models\TravelRequest::where('kode_sppd', 'like', 'SPD/2025/07/%')->get();
echo "Found " . $dummyRequests->count() . " dummy requests\n";

// Delete related data first
$ids = $dummyRequests->pluck('id')->toArray();
App\Models\Approval::whereIn('travel_request_id', $ids)->delete();
App\Models\Notification::whereIn('travel_request_id', $ids)->delete();

// Delete travel requests
App\Models\TravelRequest::whereIn('id', $ids)->delete();
echo "Cleanup completed!\n";
```

## 📜 **Metode 3: Menggunakan Script PHP**

### **Jalankan Script Cleanup:**
```bash
php scripts/cleanup-dummy-data.php
```

## 🛡️ **Keamanan dan Backup**

### **1. Backup Database (Optional):**
```bash
# Backup sebelum cleanup
php artisan db:backup

# Atau manual backup
mysqldump -u username -p database_name > backup_before_cleanup.sql
```

### **2. Verifikasi Data:**
```bash
# Cek jumlah data sebelum cleanup
php artisan tinker --execute="echo 'Travel Requests: ' . App\Models\TravelRequest::count() . PHP_EOL;"
```

### **3. Rollback (Jika Perlu):**
```bash
# Restore dari backup
mysql -u username -p database_name < backup_before_cleanup.sql
```

## 📊 **Monitoring dan Logging**

### **1. Cek Log Laravel:**
```bash
tail -f storage/logs/laravel.log
```

### **2. Cek Database Statistics:**
```bash
php artisan tinker --execute="
echo 'Database Statistics:' . PHP_EOL;
echo 'Travel Requests: ' . App\Models\TravelRequest::count() . PHP_EOL;
echo 'Approvals: ' . App\Models\Approval::count() . PHP_EOL;
echo 'Notifications: ' . App\Models\Notification::count() . PHP_EOL;
"
```

## ⚠️ **Peringatan Penting**

### **1. Data yang TIDAK Dihapus:**
- ✅ **User accounts** - Akun pengguna tetap aman
- ✅ **Settings** - Pengaturan sistem
- ✅ **Template documents** - Template dokumen
- ✅ **Real SPPD data** - Data SPPD yang dibuat manual

### **2. Data yang Dihapus:**
- ❌ **Dummy travel requests** - SPPD dari seeder
- ❌ **Related approvals** - Persetujuan terkait
- ❌ **Related notifications** - Notifikasi terkait
- ❌ **Participants data** - Data peserta terkait

## 🔄 **Workflow Cleanup**

### **1. Sebelum Cleanup:**
```bash
# 1. Backup database (optional)
php artisan db:backup

# 2. Cek data yang akan dihapus
php artisan tinker --execute="
$count = App\Models\TravelRequest::where('kode_sppd', 'like', 'SPD/2025/07/%')->count();
echo 'Dummy requests to delete: ' . $count . PHP_EOL;
"
```

### **2. Proses Cleanup:**
```bash
# Jalankan cleanup
php artisan cleanup:dummy-data --force
```

### **3. Setelah Cleanup:**
```bash
# Verifikasi hasil
php artisan tinker --execute="
echo 'After cleanup:' . PHP_EOL;
echo 'Travel Requests: ' . App\Models\TravelRequest::count() . PHP_EOL;
echo 'Approvals: ' . App\Models\Approval::count() . PHP_EOL;
echo 'Notifications: ' . App\Models\Notification::count() . PHP_EOL;
"
```

## 🎯 **Use Cases**

### **1. Development Environment:**
- Bersihkan data dummy setelah testing
- Reset database ke kondisi awal
- Siapkan environment untuk testing baru

### **2. Production Environment:**
- Hapus data testing yang tidak sengaja masuk
- Bersihkan data temporary
- Optimasi database

### **3. Testing Environment:**
- Reset data sebelum test suite
- Bersihkan data setelah integration test
- Siapkan data untuk unit test

## 📞 **Troubleshooting**

### **1. Error "Permission Denied":**
```bash
# Pastikan folder storage writable
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### **2. Error "Database Connection":**
```bash
# Cek konfigurasi database
php artisan config:clear
php artisan cache:clear
```

### **3. Error "Transaction Failed":**
```bash
# Cek log error
tail -f storage/logs/laravel.log

# Restart dengan force
php artisan cleanup:dummy-data --force
```

---

## 🎉 **KESIMPULAN**

**Data dummy SPPD KPU berhasil dibersihkan!** 

Database sekarang bersih dan siap untuk:
- ✅ **Testing baru**
- ✅ **Development lanjutan**
- ✅ **Production deployment**
- ✅ **Demo presentation**

**Semua data penting (users, settings, templates) tetap aman!** 🛡️ 