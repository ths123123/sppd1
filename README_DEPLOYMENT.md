# 🚀 QUICK DEPLOYMENT GUIDE - SPPD KPU

## ⚡ **DEPLOYMENT CEPAT (5 MENIT)**

### **🥇 OPSI TERBAIK: Railway.app**

1. **Buka [railway.app](https://railway.app)**
2. **Sign up dengan GitHub**
3. **Klik "New Project" → "Deploy from GitHub repo"**
4. **Pilih repository SPPD-KPU**
5. **Railway otomatis deploy!**

**Hasil:** `https://your-app-name.railway.app`

---

## 📋 **PLATFORM GRATIS LAINNYA**

| Platform | Gratis | Database | SSL | Setup |
|----------|--------|----------|-----|-------|
| **Railway.app** | $5/bulan | ✅ PostgreSQL | ✅ | Otomatis |
| **Render.com** | 750 jam/bulan | ✅ PostgreSQL | ✅ | Manual |
| **Heroku** | 550-1000 jam/bulan | ✅ PostgreSQL | ✅ | CLI |
| **Vercel** | Unlimited | ❌ (PlanetScale) | ✅ | Manual |

---

## 🔧 **SETUP MANUAL (Jika Perlu)**

### **1. Environment Variables**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-key-here

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### **2. Deploy Commands**
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **3. Test Users**
```
Staff: staff1@kpu.go.id / 72e82b77
Kasubbag: kasubbag.umum@kpu.go.id / 72e82b77
Sekretaris: sekretaris@kpu.go.id / 72e82b77
PPK: ppk@kpu.go.id / 72e82b77
Admin: admin@kpu.go.id / 72e82b77
```

---

## 🎯 **STEP-BY-STEP RAILWAY (RECOMMENDED)**

### **Step 1: Upload ke GitHub**
```bash
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

### **Step 2: Deploy di Railway**
1. Buka [railway.app](https://railway.app)
2. Sign up dengan GitHub
3. "New Project" → "Deploy from GitHub repo"
4. Pilih repository SPPD-KPU
5. Tunggu deploy selesai (2-3 menit)

### **Step 3: Setup Database**
1. Di Railway Dashboard → "New" → "Database" → "PostgreSQL"
2. Railway otomatis connect ke app
3. Environment variables otomatis ter-set

### **Step 4: Setup Domain**
- Railway otomatis kasih: `https://your-app-name.railway.app`
- Bisa custom domain jika punya

### **Step 5: Test**
1. Buka URL yang diberikan Railway
2. Login dengan test users di atas
3. Test semua fitur SPPD

---

## 🚨 **TROUBLESHOOTING CEPAT**

### **Error: Database Connection**
- Cek environment variables di Railway Dashboard
- Pastikan database sudah dibuat

### **Error: 500 Internal Server Error**
- Cek logs di Railway Dashboard
- Pastikan `APP_DEBUG=false`

### **Error: File Upload**
- Cek storage permissions
- Pastikan storage link sudah dibuat

### **Error: Memory Limit**
- Tambah di environment variables: `PHP_MEMORY_LIMIT=512M`

---

## 📞 **SUPPORT**

### **Jika ada masalah:**
1. Cek logs di platform hosting
2. Test di local environment dulu
3. Pastikan semua environment variables benar
4. Cek database connection

### **Contact:**
- **Email:** support@kpu.go.id
- **Documentation:** Lihat `docs/TUTORIAL_DEPLOY_GRATIS.md`

---

## 🎉 **SUCCESS!**

Setelah deploy berhasil:
- ✅ **URL Production:** https://your-app-name.railway.app
- ✅ **Database:** PostgreSQL ter-manage
- ✅ **SSL Certificate:** Otomatis
- ✅ **Auto Deploy:** Setiap push ke main branch
- ✅ **Monitoring:** Logs dan performance metrics

**Sistem SPPD KPU siap untuk testing di lingkungan production!** 🚀

---

## 📚 **DOKUMENTASI LENGKAP**

Untuk tutorial lengkap, lihat: `docs/TUTORIAL_DEPLOY_GRATIS.md` 