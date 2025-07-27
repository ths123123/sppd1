#!/bin/bash

# 🚀 SPPD KPU Deployment Script
# Script otomatis untuk deploy ke platform gratis

echo "🚀 SPPD KPU DEPLOYMENT SCRIPT"
echo "=============================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if git is installed
if ! command -v git &> /dev/null; then
    print_error "Git tidak ditemukan. Silakan install Git terlebih dahulu."
    exit 1
fi

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    print_error "Direktori ini bukan git repository. Silakan init git terlebih dahulu."
    exit 1
fi

# Function to prepare for deployment
prepare_deployment() {
    print_status "Mempersiapkan deployment..."
    
    # Install dependencies
    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
    
    # Install NPM dependencies
    if [ -f "package.json" ]; then
        print_status "Installing NPM dependencies..."
        npm install
        npm run build
    fi
    
    # Clear caches
    print_status "Clearing Laravel caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Optimize for production
    print_status "Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    print_status "Preparation completed!"
}

# Function to deploy to Railway
deploy_railway() {
    print_status "Deploying to Railway..."
    
    # Check if Railway CLI is installed
    if ! command -v railway &> /dev/null; then
        print_warning "Railway CLI tidak ditemukan. Install dengan: npm install -g @railway/cli"
        print_status "Atau deploy manual melalui Railway Dashboard"
        return 1
    fi
    
    # Login to Railway
    railway login
    
    # Deploy
    railway up
    
    print_status "Railway deployment completed!"
}

# Function to deploy to Render
deploy_render() {
    print_status "Deploying to Render..."
    print_status "Silakan deploy manual melalui Render Dashboard:"
    echo "1. Buka https://render.com"
    echo "2. Connect GitHub repository"
    echo "3. Pilih 'Web Service'"
    echo "4. Set build command: composer install --no-dev --optimize-autoloader && npm install && npm run build"
    echo "5. Set start command: php artisan serve --host 0.0.0.0 --port \$PORT"
}

# Function to deploy to Heroku
deploy_heroku() {
    print_status "Deploying to Heroku..."
    
    # Check if Heroku CLI is installed
    if ! command -v heroku &> /dev/null; then
        print_warning "Heroku CLI tidak ditemukan. Install dari https://devcenter.heroku.com/articles/heroku-cli"
        return 1
    fi
    
    # Login to Heroku
    heroku login
    
    # Create app if not exists
    if ! heroku apps:info &> /dev/null; then
        heroku create sppd-kpu-app
    fi
    
    # Add PostgreSQL addon
    heroku addons:create heroku-postgresql:mini
    
    # Set environment variables
    heroku config:set APP_ENV=production
    heroku config:set APP_DEBUG=false
    
    # Deploy
    git push heroku main
    
    # Run migrations
    heroku run php artisan migrate --force
    
    print_status "Heroku deployment completed!"
}

# Function to show deployment options
show_options() {
    echo ""
    echo "📋 PILIH PLATFORM DEPLOYMENT:"
    echo "1. Railway.app (Recommended - $5 credit/bulan)"
    echo "2. Render.com (750 jam/bulan)"
    echo "3. Heroku (550-1000 dyno hours/bulan)"
    echo "4. Manual Setup (Local testing)"
    echo "5. Exit"
    echo ""
}

# Main script
main() {
    # Prepare deployment
    prepare_deployment
    
    # Show options
    while true; do
        show_options
        read -p "Pilih opsi (1-5): " choice
        
        case $choice in
            1)
                deploy_railway
                break
                ;;
            2)
                deploy_render
                break
                ;;
            3)
                deploy_heroku
                break
                ;;
            4)
                print_status "Setup local testing..."
                print_status "1. Copy .env.example ke .env"
                print_status "2. Set APP_ENV=local"
                print_status "3. Run: php artisan key:generate"
                print_status "4. Run: php artisan migrate"
                print_status "5. Run: php artisan db:seed"
                print_status "6. Run: php artisan serve"
                break
                ;;
            5)
                print_status "Exiting..."
                exit 0
                ;;
            *)
                print_error "Opsi tidak valid. Silakan pilih 1-5."
                ;;
        esac
    done
    
    echo ""
    print_status "🎉 Deployment selesai!"
    print_status "Jangan lupa untuk:"
    echo "  - Test semua fitur setelah deploy"
    echo "  - Setup email/WhatsApp notification"
    echo "  - Monitor logs dan performance"
    echo "  - Backup database secara berkala"
}

# Run main function
main 