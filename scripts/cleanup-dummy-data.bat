@echo off
echo ========================================
echo    SPPD KPU - Cleanup Dummy Data
echo ========================================
echo.
echo This script will clean up all dummy travel request data
echo from the database including:
echo - Dummy travel requests (SPD/2025/07/xxx)
echo - Related approvals
echo - Related notifications
echo.
echo WARNING: This action cannot be undone!
echo.
set /p confirm="Are you sure you want to continue? (y/N): "

if /i "%confirm%"=="y" (
    echo.
    echo Starting cleanup...
    php artisan cleanup:dummy-data --force
    echo.
    echo Cleanup completed!
) else (
    echo.
    echo Cleanup cancelled.
)

echo.
pause 