@echo off
echo ========================================
echo    SPPD KPU - Network Server
echo ========================================
echo.
echo Starting Laravel server for network access...
echo Server will be available at:
echo - Local: http://localhost:8000
echo - Network: http://192.168.18.5:8000
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php artisan serve --host=192.168.18.5 --port=8000

pause 