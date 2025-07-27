@echo off
echo ========================================
echo   ENABLE GD EXTENSION FOR XAMPP
echo ========================================
echo.
echo This script will help you enable the GD extension in XAMPP
echo which is required for better PDF generation with images.
echo.

echo Checking current PHP configuration...
php -m | findstr gd
if %errorlevel% equ 0 (
    echo ✅ GD extension is already enabled!
    pause
    exit /b 0
) else (
    echo ❌ GD extension is not enabled.
    echo.
    echo To enable GD extension:
    echo 1. Open C:\xampp\php\php.ini
    echo 2. Find the line: ;extension=gd
    echo 3. Remove the semicolon: extension=gd
    echo 4. Save the file
    echo 5. Restart Apache in XAMPP Control Panel
    echo.
    echo After enabling GD, PDF export will include the KPU logo.
    echo Without GD, PDF will still work but without the logo.
    echo.
    pause
) 