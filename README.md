# SPPD KPU Kabupaten Cirebon

Sistem Pengelolaan Perjalanan Dinas (SPPD) untuk Komisi Pemilihan Umum Kabupaten Cirebon.

## 🚀 Features

- **User Management**: Multi-role system (Admin, Pimpinan, Staff)
- **SPPD Management**: Create, edit, approve travel requests
- **Document Management**: Upload and manage supporting documents
- **Approval Workflow**: Multi-level approval system
- **Reporting**: Excel export with professional styling
- **Real-time Dashboard**: Live statistics and analytics
- **WhatsApp Integration**: Automated notifications

## 🛠️ Tech Stack

- **Backend**: Laravel 10 (PHP 8.1)
- **Frontend**: Blade Templates + Tailwind CSS
- **Database**: PostgreSQL/MySQL
- **Export**: Maatwebsite Excel
- **Charts**: Chart.js
- **Deployment**: Render/Railway/VPS

## 📋 Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- PostgreSQL/MySQL
- Web Server (Apache/Nginx)

## 🚀 Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/your-username/sppd-kpu-cirebon.git
cd sppd-kpu-cirebon
```
### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets
```bash
npm run build
```

### 6. Start Development Server
```bash
php artisan serve
```

## 🌐 Deployment



### VPS Setup
```bash
# Install LAMP Stack
sudo apt update
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-xml php8.1-curl

# Deploy Laravel
cd /var/www/html
git clone [repository]
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate
```
## 📁 Project Structure

```
SPPD-KPU/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Services/
│   └── Exports/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── images/
│   └── js/
└── docs/
    └── fixes/
```

## 🔧 Configuration

### Environment Variables
```env
APP_NAME="SPPD KPU Cirebon"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

## 📊 Features Detail

### User Management
- Multi-role authentication
- Profile management
- Password reset

### SPPD Management
- Create travel requests
- Participant management
- Budget calculation
- Document upload

### Approval Workflow
- Multi-level approval
- Status tracking
- Email notifications
- WhatsApp integration

### Reporting
- Excel export with KPU logo
- Professional styling
- Real-time data
- Multiple report types

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

This project is developed for KPU Kabupaten Cirebon.

## 📞 Support

For support, email: support@kpu-cirebon.go.id

---

**SPPD KPU Kabupaten Cirebon** - Sistem Pengelolaan Perjalanan Dinas yang Profesional

