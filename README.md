# 🏥 ConCure Clinic Management System

A comprehensive, modern clinic management system built with Laravel, designed for healthcare providers to efficiently manage patients, prescriptions, finances, and operations with multilingual support.

## 🏥 About ConCure

ConCure is developed by **Connect Pure** and provides a complete solution for clinic management including patient records, recommendations, financial tracking, and more.

## ✨ Features

- **Patient Management**: Complete patient profiles with medical history, checkups, and file uploads
- **Recommendations System**: Lab requests, prescriptions, and diet plans
- **Food Composition Database**: Comprehensive nutritional information with multilingual support
- **Financial Management**: Invoicing, expense tracking, and financial reporting
- **Advertisement System**: Manage clinic advertisements with automatic expiration
- **Multi-language Support**: English, Arabic, and Kurdish
- **Role-based Access Control**: Admin, Program Owner, Doctor, Assistant, Nurse, Accountant, Patient
- **Communication Integration**: WhatsApp and SMS support for sending documents
- **Audit Logging**: Track all user activities
- **PWA Ready**: Mobile-first design with Progressive Web App capabilities

## 🛠️ Tech Stack

- **Backend**: PHP 8.1+ with Laravel 10
- **Database**: SQLite (easily configurable for MySQL/PostgreSQL)
- **Frontend**: HTML5, CSS3, JavaScript (Responsive Design)
- **PDF Generation**: DomPDF
- **File Handling**: Intervention Image
- **Communication**: Twilio SDK for SMS/WhatsApp

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- SQLite (or MySQL/PostgreSQL)
- Node.js and NPM (for frontend assets)

## 🚀 Installation

### 1. Install Dependencies

**For macOS:**
```bash
# Install Homebrew if not already installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP and Composer
brew install php composer

# Install Node.js
brew install node
```

**For Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-curl php8.1-zip php8.1-gd php8.1-mysql php8.1-xml php8.1-mbstring
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Setup Project

```bash
# Clone or navigate to project directory
cd /path/to/concure

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set up the application
php artisan concure:setup

# Install and compile frontend assets (when available)
npm install
npm run build
```

### 3. Configuration

Edit the `.env` file to configure your settings:

```env
APP_NAME="ConCure Clinic Management"
APP_URL=http://localhost:8000

# Database (SQLite is default)
DB_CONNECTION=sqlite
DB_DATABASE=database/concure.sqlite

# ConCure Settings
CONCURE_COMPANY_NAME="Connect Pure"
CONCURE_PRIMARY_COLOR="#008080"
CONCURE_DEFAULT_LANGUAGE=en

# Communication (optional)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
WHATSAPP_API_URL=your_whatsapp_api_url
```

### 4. Run the Application

```bash
# Start the development server
php artisan serve

# The application will be available at http://localhost:8000
```

## 👥 User Roles

- **Program Owner**: Manages activation codes and clinic access
- **Admin**: Full clinic management access
- **Doctor**: Patient management and recommendations
- **Assistant**: Patient support and basic management
- **Nurse**: Patient care and checkup management
- **Accountant**: Financial management and reporting
- **Patient**: Personal health record access

## 📱 Mobile & PWA Support

ConCure is built with a mobile-first approach and is PWA-ready for future mobile app deployment.

## 🌐 Multilingual Support

- English (default)
- Arabic (RTL support)
- Kurdish

Switch languages using the language selector in the application.

## 🔒 Security Features

- Role-based access control
- Activation code system
- Audit logging
- Secure file uploads
- Data encryption

## 📊 Modules

1. **Patient Management**: Complete patient profiles and medical history
2. **Recommendations**: Lab requests, prescriptions, diet plans
3. **Food Composition**: Nutritional database with search functionality
4. **Finance**: Invoicing, expenses, and financial reporting
5. **Advertisements**: Marketing content management
6. **Settings**: System configuration and user management

## 🤝 Support

For support and questions, contact **Connect Pure**.

## 📄 License

This project is proprietary software developed by Connect Pure.

---

**ConCure** - Empowering Healthcare Management
# ConCure
