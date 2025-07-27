# 🚀 TUTORIAL DEPLOY GRATIS SISTEM SPPD KPU
## Step-by-Step Guide untuk Testing di KPU

---

## 📋 **DAFTAR PLATFORM GRATIS**

### **🥇 OPSI 1: Railway.app (RECOMMENDED)**
- **Gratis:** $5 credit/bulan (cukup untuk testing)
- **Database:** PostgreSQL included
- **SSL:** Otomatis
- **Domain:** Subdomain gratis
- **Deploy:** Git integration

### **🥈 OPSI 2: Render.com**
- **Gratis:** 750 jam/bulan
- **Database:** PostgreSQL included
- **SSL:** Otomatis
- **Domain:** Subdomain gratis

### **🥉 OPSI 3: Heroku**
- **Gratis:** 550-1000 dyno hours/bulan
- **Database:** PostgreSQL addon
- **SSL:** Otomatis
- **Domain:** Subdomain gratis

### **🏅 OPSI 4: Vercel + PlanetScale**
- **Gratis:** Vercel + PlanetScale free tier
- **Database:** MySQL (PlanetScale)
- **SSL:** Otomatis
- **Domain:** Subdomain gratis

---

## 🎯 **TUTORIAL RAILWAY.APP (RECOMMENDED)**

### **Step 1: Persiapan Repository**
```bash
# Pastikan kode sudah di GitHub
git add .
git commit -m "Prepare for deployment"
git push origin main
```

### **Step 2: Setup Railway Account**
1. Buka [railway.app](https://railway.app)
2. Sign up dengan GitHub
3. Klik "New Project"
4. Pilih "Deploy from GitHub repo"
5. Pilih repository SPPD-KPU

### **Step 3: Configure Environment Variables**
```env
# Di Railway Dashboard → Variables
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app
APP_KEY=base64:your-app-key-here

# Database (Railway otomatis generate)
DB_CONNECTION=pgsql
DB_HOST=your-railway-db-host
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=your-railway-db-password

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Mail (gunakan Mailtrap untuk testing)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kpu.go.id
MAIL_FROM_NAME="SPPD KPU"

# WhatsApp (opsional)
WHATSAPP_ENABLED=false
```

### **Step 4: Setup Database**
1. Di Railway Dashboard → "New" → "Database" → "PostgreSQL"
2. Railway otomatis connect ke app
3. Environment variables otomatis ter-set

### **Step 5: Deploy Commands**
```bash
# Railway akan otomatis run commands ini:
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 6: Setup Domain**
1. Railway otomatis kasih subdomain: `https://your-app-name.railway.app`
2. Bisa custom domain jika punya

---

## 🎯 **TUTORIAL RENDER.COM (ALTERNATIVE)**

### **Step 1: Setup Render Account**
1. Buka [render.com](https://render.com)
2. Sign up dengan GitHub
3. Klik "New" → "Web Service"

### **Step 2: Connect Repository**
1. Pilih repository SPPD-KPU
2. Set branch: `main`
3. Runtime: `PHP`
4. Build Command: `composer install --no-dev --optimize-autoloader`
5. Start Command: `php artisan serve --host 0.0.0.0 --port $PORT`

### **Step 3: Environment Variables**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
APP_KEY=base64:your-app-key-here
DB_CONNECTION=pgsql
DB_HOST=your-render-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

### **Step 4: Setup PostgreSQL Database**
1. "New" → "PostgreSQL"
2. Copy connection details ke environment variables

---

## 🎯 **TUTORIAL HEROKU (ALTERNATIVE)**

### **Step 1: Install Heroku CLI**
```bash
# Download dari heroku.com
# Atau pakai command line
curl https://cli-assets.heroku.com/install.sh | sh
```

### **Step 2: Login & Setup**
```bash
heroku login
heroku create sppd-kpu-app
heroku addons:create heroku-postgresql:mini
```

### **Step 3: Configure Environment**
```bash
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_URL=https://sppd-kpu-app.herokuapp.com
```

### **Step 4: Deploy**
```bash
git push heroku main
heroku run php artisan migrate --force
```

---

## 🔧 **SETUP LOKAL UNTUK TESTING**

### **Step 1: Install Dependencies**
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### **Step 2: Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

### **Step 3: Database Setup**
```bash
# Jika pakai PostgreSQL lokal
php artisan migrate
php artisan db:seed

# Jika pakai SQLite (lebih mudah untuk testing)
# Edit .env:
DB_CONNECTION=sqlite
# Hapus DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

### **Step 4: Storage Setup**
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

---

## 🧪 **TESTING SETUP**

### **Step 1: Create Test Users**
```bash
php artisan db:seed --class=UserRoleSeeder
```

### **Step 2: Test Login Credentials**
```
Staff: staff1@kpu.go.id / 72e82b77
Kasubbag: kasubbag.umum@kpu.go.id / 72e82b77
Sekretaris: sekretaris@kpu.go.id / 72e82b77
PPK: ppk@kpu.go.id / 72e82b77
Admin: admin@kpu.go.id / 72e82b77
```

### **Step 3: Run Tests**
```bash
php artisan test
```

---

## 📱 **SETUP WHATSAPP NOTIFICATION (OPSIONAL)**

### **Step 1: Daftar Fonnte**
1. Buka [fonnte.com](https://fonnte.com)
2. Sign up gratis
3. Dapatkan API key

### **Step 2: Configure Environment**
```env
WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_API_KEY=your-fonnte-api-key
```

---

## 🔒 **SECURITY CHECKLIST**

### **Pre-Deployment:**
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY`
- [ ] Secure database password
- [ ] HTTPS enabled
- [ ] File permissions correct

### **Post-Deployment:**
- [ ] Test semua role login
- [ ] Test file upload
- [ ] Test SPPD workflow
- [ ] Test approval process
- [ ] Check error logs
- [ ] Monitor performance

---

## 🚨 **TROUBLESHOOTING**

### **Error: Database Connection**
```bash
# Cek environment variables
php artisan tinker
DB::connection()->getPdo();
```

### **Error: File Upload**
```bash
# Cek storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### **Error: 500 Internal Server Error**
```bash
# Cek logs
tail -f storage/logs/laravel.log
```

### **Error: Memory Limit**
```bash
# Tambah di .env
PHP_MEMORY_LIMIT=512M
```

---

## 📞 **SUPPORT**

### **Jika ada masalah:**
1. Cek logs di platform hosting
2. Test di local environment dulu
3. Pastikan semua environment variables benar
4. Cek database connection

### **Contact Info:**
- **Email:** support@kpu.go.id
- **WhatsApp:** +62xxx (jika setup)
- **Documentation:** Lihat file ini

---

## 🎉 **SUCCESS DEPLOYMENT**

Setelah deploy berhasil, Anda akan dapat:
- ✅ **URL Production:** https://your-app-name.railway.app
- ✅ **Database:** PostgreSQL ter-manage
- ✅ **SSL Certificate:** Otomatis
- ✅ **Auto Deploy:** Setiap push ke main branch
- ✅ **Monitoring:** Logs dan performance metrics

**Sistem SPPD KPU siap untuk testing di lingkungan production!** 🚀 