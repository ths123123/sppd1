#!/bin/bash
cd /var/www/sppd_kpu || exit
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
echo "✅ Deploy selesai!"
