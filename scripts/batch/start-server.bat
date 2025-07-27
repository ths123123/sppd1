@echo off
echo Starting Laravel Development Server...
echo.
echo Open your browser and go to: http://127.0.0.1:8000
echo.
echo To stop the server, press Ctrl+C
echo.
php artisan serve --host=127.0.0.1 --port=8000
pause
