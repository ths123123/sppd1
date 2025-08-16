# Dokumentasi Deployment Laravel ke VPS IDCloudHost

## 📋 Informasi Proyek
- **Nama Proyek:** SPPD KPU Kabupaten Cirebon
- **Framework:** Laravel 12 + Vite + TailwindCSS
- **Database:** PostgreSQL
- **VPS Provider:** IDCloudHost
- **IP Server:** 103.139.193.218

## 🚀 Langkah-langkah Deployment

### 1. Persiapan VPS di IDCloudHost

#### 1.1 Membuat VPS Baru
- **Type:** Virtual machine
- **OS:** Ubuntu 24.04 LTS
- **Location:** Indonesia (SouthJKT-a)
- **Server class:** Basic Standard
- **Size:** 2 CPU, 2 GB RAM, 20 GB DISK
- **Public IPv4:** ✅ Create a public IPv4 address
- **Username:** adminfillahi
- **Password:** sppd123
- **SSH Public Key:** ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIEqOo1zoRCG+ZdKIaXGoeVuO6Qvpt7n6oLTPUK0cSEpf fillahi hazarul@LAPTOP-CDUKR9AE
- **Resource name:** sppd_kpu

#### 1.2 Generate SSH Key di Windows
```bash
ssh-keygen -t ed25519 -C "fillahi hazarul@LAPTOP-CDUKR9AE"
# Tekan Enter untuk semua pertanyaan (default settings)
# Copy isi file: C:\Users\Fillahi Hazarul\.ssh\id_ed25519.pub
```

### 2. Akses VPS via SSH

#### 2.1 Login ke Server
```bash
ssh adminfillahi@103.139.193.218
# Masukkan passphrase SSH key jika ada
```

#### 2.2 Update Server
```bash
sudo apt update && sudo apt upgrade -y
sudo reboot
# Login ulang setelah reboot
```

### 3. Install Software Pendukung

#### 3.1 Install Nginx
```bash
sudo apt install nginx -y
```

#### 3.2 Install PHP 8.2 dan Extensions Laravel
```bash
sudo apt install php8.2 php8.2-fpm php8.2-pgsql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-cli unzip -y
```

#### 3.3 Install PostgreSQL
```bash
sudo apt install postgresql postgresql-contrib -y
```

#### 3.4 Setup Database PostgreSQL
```bash
sudo -u postgres psql
# Di dalam PostgreSQL prompt:
CREATE USER fillahi WITH PASSWORD 'sppd123';
CREATE DATABASE sppd_kpu OWNER fillahi;
\q
```

#### 3.5 Install Composer
```bash
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

#### 3.6 Install Node.js & npm
```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

#### 3.7 Install Git
```bash
sudo apt install git -y
```

### 4. Deploy Aplikasi Laravel

#### 4.1 Clone Repository dari GitHub
```bash
cd /var/www
sudo git clone https://github.com/ths123123/sppd1.git sppd_kpu
sudo chown -R $USER:$USER sppd_kpu
cd sppd_kpu
```

#### 4.2 Setup Environment Laravel
```bash
cp .env.example .env
nano .env
```

**Edit file .env:**
```env
APP_NAME="SPPD KPU Cirebon"
APP_ENV=production
APP_URL=http://103.139.193.218

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sppd_kpu
DB_USERNAME=fillahi
DB_PASSWORD=sppd123
```

#### 4.3 Install Dependencies Laravel
```bash
composer install
php artisan key:generate
```

#### 4.4 Jalankan Migrasi Database
```bash
php artisan migrate
```

#### 4.5 Build Frontend (Vite)
```bash
npm install
npm run build
```

### 5. Konfigurasi Nginx

#### 5.1 Buat Konfigurasi Nginx
```bash
sudo nano /etc/nginx/sites-available/sppd_kpu
```

**Isi dengan konfigurasi:**
```nginx
server {
    listen 80;
    server_name 103.139.193.218;

    root /var/www/sppd_kpu/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    location /build/ {
        try_files $uri $uri/ =404;
    }
}
```

#### 5.2 Aktifkan Konfigurasi Nginx
```bash
sudo ln -s /etc/nginx/sites-available/sppd_kpu /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### 6. Setup Permission

#### 6.1 Set Permission yang Benar
```bash
sudo usermod -a -G www-data adminfillahi
sudo chown -R adminfillahi:www-data /var/www/sppd_kpu
sudo chmod -R 775 /var/www/sppd_kpu/storage
sudo chmod -R 775 /var/www/sppd_kpu/bootstrap/cache
sudo systemctl reload nginx
```

### 7. Verifikasi Deployment

#### 7.1 Cek Status Services
```bash
sudo systemctl status nginx
sudo systemctl status postgresql
sudo ss -tuln | grep :80
```

#### 7.2 Akses Aplikasi
- Buka browser dan akses: `http://103.139.193.218`
- Aplikasi Laravel seharusnya sudah bisa diakses

## 🔧 Troubleshooting

### Masalah Permission
Jika muncul error "Permission denied" untuk file log:
```bash
sudo chown -R www-data:www-data /var/www/sppd_kpu
sudo chmod -R 775 /var/www/sppd_kpu/storage
sudo chmod -R 775 /var/www/sppd_kpu/bootstrap/cache
```

### Nginx Tidak Listen di Port 80
```bash
sudo ss -tuln | grep :80
# Jika kosong, cek konfigurasi:
ls -la /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Error bzip2 saat npm install
```bash
sudo apt install bzip2 -y
npm install
```

## 📝 Catatan Penting

1. **IP Publik:** `103.139.193.218` - bisa diakses dari mana saja di internet
2. **Username VPS:** adminfillahi
3. **Database:** PostgreSQL dengan user fillahi
4. **Web Server:** Nginx dengan PHP-FPM
5. **Framework:** Laravel 12 dengan Vite

## 🔒 Keamanan

- SSH key sudah dikonfigurasi untuk akses aman
- Firewall UFW tidak aktif (bisa diaktifkan jika diperlukan)
- File .htaccess sudah dikonfigurasi untuk deny access

## 📞 Support

Jika ada masalah, hubungi:
- **Phone:** (0231) 123456
- **Email:** admin@kpucirebon.go.id

---

**Status:** ✅ **DEPLOYMENT BERHASIL**  
**Tanggal:** 16 Agustus 2025  
**Aplikasi Online:** http://103.139.193.218

## 🔐 Setup User/Akun Login

### 1. Login ke Server VPS
```bash
ssh adminfillahi@103.139.193.218
cd /var/www/sppd_kpu
```

### 2. Jalankan Seeder untuk Membuat User Default
```bash
php artisan db:seed
```

### 3. Akun Default yang Tersedia
**Password untuk semua akun:** `72e82b77`

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@kpu.go.id | 72e82b77 |
| Sekretaris | sekretaris@kpu.go.id | 72e82b77 |
| Kasubbag1 | kasubbag1@kpu.go.id | 72e82b77 |
| Kasubbag2 | kasubbag2@kpu.go.id | 72e82b77 |

---

## 🔧 Update Sistem (16 Agustus 2025)

### ✅ **PPK Dapat Akses User Management**
- **Role PPK** sekarang dapat mengakses fitur **Kelola User**
- **Routes yang diupdate:** User Management middleware
- **JavaScript:** Navbar access control sudah mendukung PPK
- **GitHub Actions:** Deploy otomatis saat push ke main branch

### 📋 **Role yang Dapat Akses User Management:**
- ✅ **Admin** - Akses penuh
- ✅ **Kasubbag** - Akses penuh  
- ✅ **Sekretaris** - Akses penuh
- ✅ **PPK** - Akses penuh (BARU)

### 🔄 **Cara Deploy Update:**
1. **Otomatis:** Push ke branch `main` → GitHub Actions deploy otomatis
2. **Manual:** Ikuti langkah di bagian "Update Aplikasi" di bawah

---

## 🔄 Update Aplikasi (Deploy Perubahan)

### Metode 1: Update Manual (Pull dari GitHub)

#### 1. Login ke Server
```bash
ssh adminfillahi@103.139.193.218
cd /var/www/sppd_kpu
```

#### 2. Backup Database (Opsional)
```bash
pg_dump -U fillahi -h localhost sppd_kpu > backup_$(date +%Y%m%d_%H%M%S).sql
```

#### 3. Pull Perubahan dari GitHub
```bash
git pull origin main
# atau git pull origin master (sesuaikan dengan branch utama)
```

#### 4. Install Dependencies Baru
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

#### 5. Jalankan Migrasi (jika ada perubahan database)
```bash
php artisan migrate
```

#### 6. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### 7. Restart Services (jika perlu)
```bash
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
```

### Metode 2: Deploy Otomatis dengan GitHub Actions (Opsional)

#### 1. Buat File `.github/workflows/deploy.yml` di Repository
```yaml
name: Deploy to VPS

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy to VPS
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: 103.139.193.218
        username: adminfillahi
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /var/www/sppd_kpu
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm install
          npm run build
          php artisan migrate --force
          php artisan config:clear
          php artisan cache:clear
          php artisan view:clear
          php artisan route:clear
          sudo systemctl reload nginx
```

#### 2. Setup GitHub Secrets
- Buka repository GitHub → Settings → Secrets and variables → Actions
- Tambahkan `SSH_PRIVATE_KEY` dengan isi private key SSH Anda
- **Cara mendapatkan private key:**
  ```bash
  # Di Windows, buka file private key:
  notepad C:\Users\Fillahi Hazarul\.ssh\id_ed25519
  # Copy seluruh isi file (termasuk baris BEGIN dan END)
  ```
- **Paste ke GitHub Secrets:** `SSH_PRIVATE_KEY`

### Metode 3: Deploy dengan Script Otomatis

#### 1. Buat Script Deploy di Server
```bash
sudo nano /var/www/deploy.sh
```

#### 2. Isi Script
```bash
#!/bin/bash
cd /var/www/sppd_kpu

echo "🔄 Pulling latest changes..."
git pull origin main

echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install
npm run build

echo "🗄️ Running migrations..."
php artisan migrate --force

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "🔄 Reloading services..."
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm

echo "✅ Deployment completed!"
```

#### 3. Beri Permission Execute
```bash
sudo chmod +x /var/www/deploy.sh
```

#### 4. Jalankan Script
```bash
sudo /var/www/deploy.sh
```

---

## 📝 Catatan Update

1. **Selalu backup database** sebelum update besar
2. **Test di local** sebelum deploy ke production
3. **Cek log error** jika ada masalah: `tail -f /var/log/nginx/error.log`
4. **Monitor disk space**: `df -h`
5. **Cek memory usage**: `free -h`

---

## 🚨 Troubleshooting Update

### Jika Git Pull Error
```bash
git stash
git pull origin main
git stash pop
```

### Jika Composer Error
```bash
composer install --ignore-platform-reqs
```

### Jika NPM Build Error
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Jika Permission Error
```bash
sudo chown -R adminfillahi:www-data /var/www/sppd_kpu
sudo chmod -R 775 /var/www/sppd_kpu/storage
sudo chmod -R 775 /var/www/sppd_kpu/bootstrap/cache
```
