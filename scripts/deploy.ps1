# 🚀 SPPD KPU Deployment Script (PowerShell)
# Script otomatis untuk deploy ke platform gratis

Write-Host "🚀 SPPD KPU DEPLOYMENT SCRIPT" -ForegroundColor Green
Write-Host "==============================" -ForegroundColor Green

# Function to print colored output
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Check if git is installed
try {
    git --version | Out-Null
} catch {
    Write-Error "Git tidak ditemukan. Silakan install Git terlebih dahulu."
    exit 1
}

# Check if we're in a git repository
try {
    git rev-parse --git-dir | Out-Null
} catch {
    Write-Error "Direktori ini bukan git repository. Silakan init git terlebih dahulu."
    exit 1
}

# Function to prepare for deployment
function Prepare-Deployment {
    Write-Status "Mempersiapkan deployment..."
    
    # Install dependencies
    Write-Status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
    
    # Install NPM dependencies
    if (Test-Path "package.json") {
        Write-Status "Installing NPM dependencies..."
        npm install
        npm run build
    }
    
    # Clear caches
    Write-Status "Clearing Laravel caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Optimize for production
    Write-Status "Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    Write-Status "Preparation completed!"
}

# Function to show deployment options
function Show-Options {
    Write-Host ""
    Write-Host "📋 PILIH PLATFORM DEPLOYMENT:" -ForegroundColor Cyan
    Write-Host "1. Railway.app (Recommended - 5 credit/bulan)" -ForegroundColor White
    Write-Host "2. Render.com (750 jam per bulan)" -ForegroundColor White
    Write-Host "3. Heroku (550-1000 dyno hours per bulan)" -ForegroundColor White
    Write-Host "4. Manual Setup (Local testing)" -ForegroundColor White
    Write-Host "5. Exit" -ForegroundColor White
    Write-Host ""
}

# Function to deploy to Railway
function Deploy-Railway {
    Write-Status "Deploying to Railway..."
    Write-Status "Silakan ikuti langkah berikut:"
    Write-Host "1. Buka https://railway.app" -ForegroundColor White
    Write-Host "2. Sign up dengan GitHub" -ForegroundColor White
    Write-Host "3. Klik 'New Project' → 'Deploy from GitHub repo'" -ForegroundColor White
    Write-Host "4. Pilih repository SPPD-KPU" -ForegroundColor White
    Write-Host "5. Railway otomatis deploy!" -ForegroundColor White
    Write-Host ""
    Write-Status "Environment variables yang perlu diset:"
    Write-Host "APP_ENV=production" -ForegroundColor Gray
    Write-Host "APP_DEBUG=false" -ForegroundColor Gray
    Write-Host "SESSION_SECURE_COOKIE=true" -ForegroundColor Gray
    Write-Host "SESSION_HTTP_ONLY=true" -ForegroundColor Gray
    Write-Host "SESSION_SAME_SITE=lax" -ForegroundColor Gray
}

# Function to deploy to Render
function Deploy-Render {
    Write-Status "Deploying to Render..."
    Write-Status "Silakan deploy manual melalui Render Dashboard:"
    Write-Host "1. Buka https://render.com" -ForegroundColor White
    Write-Host "2. Connect GitHub repository" -ForegroundColor White
    Write-Host "3. Pilih 'Web Service'" -ForegroundColor White
    Write-Host "4. Set build command: composer install --no-dev --optimize-autoloader && npm install && npm run build" -ForegroundColor White
    Write-Host "5. Set start command: php artisan serve --host 0.0.0.0 --port `$PORT" -ForegroundColor White
}

# Function to deploy to Heroku
function Deploy-Heroku {
    Write-Status "Deploying to Heroku..."
    
    # Check if Heroku CLI is installed
    try {
        heroku --version | Out-Null
    } catch {
        Write-Warning "Heroku CLI tidak ditemukan. Install dari https://devcenter.heroku.com/articles/heroku-cli"
        return
    }
    
    Write-Status "Login to Heroku..."
    heroku login
    
    Write-Status "Creating Heroku app..."
    heroku create sppd-kpu-app
    
    Write-Status "Adding PostgreSQL addon..."
    heroku addons:create heroku-postgresql:mini
    
    Write-Status "Setting environment variables..."
    heroku config:set APP_ENV=production
    heroku config:set APP_DEBUG=false
    
    Write-Status "Deploying to Heroku..."
    git push heroku main
    
    Write-Status "Running migrations..."
    heroku run php artisan migrate --force
    
    Write-Status "Heroku deployment completed!"
}

# Function to setup local testing
function Setup-Local {
    Write-Status "Setup local testing..."
    Write-Status "Ikuti langkah berikut:"
    Write-Host "1. Copy .env.example ke .env" -ForegroundColor White
    Write-Host "2. Set APP_ENV=local" -ForegroundColor White
    Write-Host "3. Run: php artisan key:generate" -ForegroundColor White
    Write-Host "4. Run: php artisan migrate" -ForegroundColor White
    Write-Host "5. Run: php artisan db:seed" -ForegroundColor White
    Write-Host "6. Run: php artisan serve" -ForegroundColor White
    Write-Host ""
    Write-Status "Test users:"
    Write-Host "Staff: staff1@kpu.go.id / 72e82b77" -ForegroundColor Gray
    Write-Host "Kasubbag: kasubbag.umum@kpu.go.id / 72e82b77" -ForegroundColor Gray
    Write-Host "Sekretaris: sekretaris@kpu.go.id / 72e82b77" -ForegroundColor Gray
    Write-Host "PPK: ppk@kpu.go.id / 72e82b77" -ForegroundColor Gray
    Write-Host "Admin: admin@kpu.go.id / 72e82b77" -ForegroundColor Gray
}

# Main script
function Main {
    # Prepare deployment
    Prepare-Deployment
    
    # Show options
    do {
        Show-Options
        $choice = Read-Host "Pilih opsi (1-5)"
        
        switch ($choice) {
            "1" {
                Deploy-Railway
                break
            }
            "2" {
                Deploy-Render
                break
            }
            "3" {
                Deploy-Heroku
                break
            }
            "4" {
                Setup-Local
                break
            }
            "5" {
                Write-Status "Exiting..."
                exit 0
            }
            default {
                Write-Error "Opsi tidak valid. Silakan pilih 1-5."
            }
        }
    } while ($choice -notin @("1", "2", "3", "4", "5"))
    
    Write-Host ""
    Write-Status "🎉 Deployment selesai!"
    Write-Status "Jangan lupa untuk:"
    Write-Host "  - Test semua fitur setelah deploy" -ForegroundColor White
    Write-Host "  - Setup email/WhatsApp notification" -ForegroundColor White
    Write-Host "  - Monitor logs dan performance" -ForegroundColor White
    Write-Host "  - Backup database secara berkala" -ForegroundColor White
}

# Run main function
Main 