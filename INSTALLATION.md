# 🚀 ConCure Installation Guide

This guide will walk you through the complete installation process for ConCure Clinic Management System.

## 📋 Prerequisites

Before installing ConCure, ensure your system meets these requirements:

### **System Requirements**
- **Operating System**: Windows 10+, macOS 10.15+, or Linux (Ubuntu 18.04+)
- **PHP**: Version 8.1 or higher
- **Memory**: Minimum 512MB RAM (1GB+ recommended)
- **Storage**: 2GB free disk space
- **Web Server**: Apache, Nginx, or PHP built-in server

### **Required PHP Extensions**
- sqlite3
- gd
- zip
- xml
- mbstring
- curl
- json
- openssl
- pdo
- tokenizer

### **Optional but Recommended**
- **Composer**: For PHP dependency management
- **Node.js & NPM**: For frontend asset compilation
- **Git**: For version control

## 🛠️ Installation Methods

### **Method 1: Automated Installation (Recommended)**

#### **For Linux/macOS:**
```bash
# Download and run the installation script
curl -fsSL https://raw.githubusercontent.com/your-repo/concure/main/install.sh -o install.sh
chmod +x install.sh
./install.sh
```

#### **For Windows:**
```cmd
# Download and run the installation script
curl -fsSL https://raw.githubusercontent.com/your-repo/concure/main/install.bat -o install.bat
install.bat
```

### **Method 2: Manual Installation**

#### **Step 1: Install Prerequisites**

**On Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-sqlite3 php8.1-gd php8.1-zip php8.1-xml php8.1-mbstring php8.1-curl composer nodejs npm
```

**On CentOS/RHEL:**
```bash
sudo yum install php php-cli php-sqlite3 php-gd php-zip php-xml php-mbstring php-curl composer nodejs npm
```

**On macOS (using Homebrew):**
```bash
brew install php composer node
```

**On Windows:**
1. Download PHP 8.1+ from [php.net](https://www.php.net/downloads)
2. Download Composer from [getcomposer.org](https://getcomposer.org/download/)
3. Download Node.js from [nodejs.org](https://nodejs.org/)

#### **Step 2: Download ConCure**
```bash
# Clone the repository
git clone https://github.com/your-repo/concure-clinic.git
cd concure-clinic

# Or download and extract ZIP file
wget https://github.com/your-repo/concure-clinic/archive/main.zip
unzip main.zip
cd concure-clinic-main
```

#### **Step 3: Install Dependencies**
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install additional packages
composer require barryvdh/laravel-dompdf intervention/image

# Install Node.js dependencies (optional)
npm install
```

#### **Step 4: Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### **Step 5: Database Setup**
```bash
# Create database directory
mkdir -p database

# Create SQLite database file
touch database/concure.sqlite
chmod 664 database/concure.sqlite

# Update .env with correct database path
# Edit .env file and set: DB_DATABASE=/full/path/to/your/project/database/concure.sqlite
```

#### **Step 6: Initialize ConCure**
```bash
# Run the ConCure setup command
php artisan concure:setup
```

#### **Step 7: Set Permissions**
```bash
# Set storage permissions (Linux/macOS)
chmod -R 775 storage bootstrap/cache
chmod 664 database/concure.sqlite

# For Windows, ensure the web server has read/write access to:
# - storage/ directory
# - bootstrap/cache/ directory
# - database/concure.sqlite file
```

#### **Step 8: Compile Assets (Optional)**
```bash
# Compile frontend assets
npm run build

# Or for development
npm run dev
```

#### **Step 9: Start the Application**
```bash
# Start the development server
php artisan serve

# The application will be available at: http://localhost:8000
```

## 🔐 First Login

After installation, you can log in with these default credentials:

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| **Program Owner** | `program_owner` | `ConCure2024!` | Full System Access |
| **Admin** | `admin` | `admin123` | Clinic Administration |
| **Doctor** | `doctor` | `doctor123` | Patient Management & Prescriptions |
| **Assistant** | `assistant` | `assistant123` | Patient Support |
| **Nurse** | `nurse` | `nurse123` | Patient Care |
| **Accountant** | `accountant` | `accountant123` | Financial Management |

> ⚠️ **Security Notice**: Change all default passwords immediately after first login!

## ⚙️ Configuration

### **Environment Variables**

Edit the `.env` file to customize your installation:

```env
# Application Settings
APP_NAME="Your Clinic Name"
APP_URL=http://your-domain.com

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/database/concure.sqlite

# ConCure Settings
CONCURE_COMPANY_NAME="Your Company Name"
CONCURE_PRIMARY_COLOR="#008080"
CONCURE_DEFAULT_LANGUAGE="en"
CONCURE_SUPPORTED_LANGUAGES="en,ar,ku"

# File Upload Settings
CONCURE_MAX_FILE_SIZE=5120
CONCURE_ALLOWED_FILE_TYPES="pdf,jpg,jpeg,png,doc,docx"

# Email Configuration (Optional)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
```

### **Web Server Configuration**

#### **Apache (.htaccess)**
ConCure includes a `.htaccess` file for Apache. Ensure `mod_rewrite` is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### **Nginx**
Add this configuration to your Nginx server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 🔧 Production Deployment

### **Optimization for Production**
```bash
# Optimize Composer autoloader
composer install --no-dev --optimize-autoloader

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compile assets for production
npm run build
```

### **Security Considerations**
1. **Change default passwords** for all user accounts
2. **Set proper file permissions**:
   - Files: 644
   - Directories: 755
   - Storage: 775
   - Database: 664
3. **Use HTTPS** in production
4. **Regular backups** of database and uploaded files
5. **Keep system updated** with security patches

### **Performance Optimization**
1. **Enable OPcache** for PHP
2. **Use a reverse proxy** (Nginx) for static files
3. **Configure caching** (Redis/Memcached)
4. **Optimize database** with proper indexing
5. **Use CDN** for static assets

## 🔄 Backup and Maintenance

### **Database Backup**
```bash
# Create backup
cp database/concure.sqlite backups/concure_$(date +%Y%m%d_%H%M%S).sqlite

# Automated backup script
#!/bin/bash
BACKUP_DIR="/path/to/backups"
DB_FILE="/path/to/database/concure.sqlite"
DATE=$(date +%Y%m%d_%H%M%S)
cp "$DB_FILE" "$BACKUP_DIR/concure_$DATE.sqlite"
find "$BACKUP_DIR" -name "concure_*.sqlite" -mtime +30 -delete
```

### **File Backup**
```bash
# Backup uploaded files
tar -czf backups/files_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/public/
```

### **System Maintenance**
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Update dependencies
composer update
npm update

# Run database migrations (if any)
php artisan migrate
```

## 🆘 Troubleshooting

### **Common Issues**

#### **Database Connection Error**
```bash
# Check database file permissions
ls -la database/concure.sqlite

# Recreate database file
rm database/concure.sqlite
touch database/concure.sqlite
chmod 664 database/concure.sqlite
php artisan migrate --seed
```

#### **Storage Permission Error**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

#### **Composer Memory Limit**
```bash
# Increase memory limit
php -d memory_limit=-1 /usr/local/bin/composer install
```

#### **Node.js Build Errors**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### **Getting Help**

If you encounter issues:

1. **Check the logs**: `storage/logs/laravel.log`
2. **Verify requirements**: Run `php artisan about`
3. **Check permissions**: Ensure proper file/directory permissions
4. **Review configuration**: Verify `.env` settings
5. **Consult documentation**: Check the full documentation
6. **Community support**: Join our Discord server
7. **Report bugs**: Create an issue on GitHub

## 📚 Next Steps

After successful installation:

1. **Change default passwords**
2. **Configure your clinic information**
3. **Set up user accounts**
4. **Import patient data** (if migrating)
5. **Configure backup schedule**
6. **Train your staff**
7. **Customize settings** to match your workflow

## 🎉 Congratulations!

ConCure is now installed and ready to help you manage your clinic efficiently. Visit the dashboard to start exploring all the features!

For detailed usage instructions, check out our [User Guide](USER_GUIDE.md).
