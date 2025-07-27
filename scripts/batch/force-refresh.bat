@echo off
echo Clearing browser cache and rebuilding assets...
echo.

REM Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

REM Build assets with Vite
npm run build

echo.
echo Done! Now refresh your browser with Ctrl+F5
pause
