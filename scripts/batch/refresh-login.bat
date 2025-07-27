@echo off
echo Menerapkan perubahan login page dan membersihkan cache...
echo.

REM Clear Laravel cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

REM Build assets with Vite
call npm run build

REM Tambahan: Hard Refresh CSS
echo.
echo ==================================
echo PERHATIAN:
echo ==================================
echo 1. Jika login masih berantakan, coba refresh browser dengan CTRL+F5
echo 2. Jika masih belum berubah, coba buka browser di mode incognito
echo 3. Jika masih belum berubah, pastikan file CSS di-compile dengan benar
echo.
echo Untuk memaksa pembersihan cache browser, gunakan:
echo - Chrome: CTRL+SHIFT+DELETE
echo - Firefox: CTRL+SHIFT+DELETE
echo - Edge: CTRL+SHIFT+DELETE
echo.
echo Pilih opsi untuk menghapus "Cached images and files" dan klik Clear Data
echo ==================================
echo.

pause
