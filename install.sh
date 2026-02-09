#!/bin/bash

# ConCure Clinic Management System Installation Script
# This script will set up the complete ConCure system

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_header() {
    echo -e "${PURPLE}🏥 $1${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check PHP version
check_php_version() {
    if command_exists php; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        if php -r "exit(version_compare(PHP_VERSION, '8.1.0', '<') ? 1 : 0);"; then
            print_status "PHP version $PHP_VERSION (✓ >= 8.1.0)"
            return 0
        else
            print_error "PHP version $PHP_VERSION is too old. PHP 8.1.0 or higher is required."
            return 1
        fi
    else
        print_error "PHP is not installed."
        return 1
    fi
}

# Function to check PHP extensions
check_php_extensions() {
    local extensions=("sqlite3" "gd" "zip" "xml" "mbstring" "curl" "json" "openssl" "pdo" "tokenizer")
    local missing_extensions=()
    
    for ext in "${extensions[@]}"; do
        if php -m | grep -q "^$ext$"; then
            print_status "PHP extension: $ext"
        else
            missing_extensions+=("$ext")
            print_error "Missing PHP extension: $ext"
        fi
    done
    
    if [ ${#missing_extensions[@]} -ne 0 ]; then
        print_error "Please install missing PHP extensions: ${missing_extensions[*]}"
        return 1
    fi
    
    return 0
}

# Main installation function
main() {
    clear
    print_header "ConCure Clinic Management System - Installation"
    echo
    print_info "This script will install and configure ConCure for you."
    echo
    
    # Step 1: Check system requirements
    print_header "Step 1: Checking System Requirements"
    
    # Check PHP
    if ! check_php_version; then
        print_error "PHP requirements not met. Please install PHP 8.1+ and try again."
        exit 1
    fi
    
    # Check PHP extensions
    if ! check_php_extensions; then
        print_error "PHP extension requirements not met. Please install missing extensions and try again."
        exit 1
    fi
    
    # Check Composer
    if command_exists composer; then
        COMPOSER_VERSION=$(composer --version | cut -d' ' -f3)
        print_status "Composer version $COMPOSER_VERSION"
    else
        print_error "Composer is not installed. Please install Composer and try again."
        print_info "Visit: https://getcomposer.org/download/"
        exit 1
    fi
    
    # Check Node.js (optional but recommended)
    if command_exists node; then
        NODE_VERSION=$(node --version)
        print_status "Node.js version $NODE_VERSION"
    else
        print_warning "Node.js is not installed. Frontend assets won't be compiled."
        print_info "You can install Node.js later from: https://nodejs.org/"
    fi
    
    echo
    
    # Step 2: Install PHP dependencies
    print_header "Step 2: Installing PHP Dependencies"
    
    if [ ! -f "composer.json" ]; then
        print_error "composer.json not found. Are you in the correct directory?"
        exit 1
    fi
    
    print_info "Running composer install..."
    composer install --no-dev --optimize-autoloader
    print_status "PHP dependencies installed"
    
    echo
    
    # Step 3: Install additional packages
    print_header "Step 3: Installing Additional Packages"
    
    print_info "Installing PDF generation package..."
    composer require barryvdh/laravel-dompdf --no-interaction
    
    print_info "Installing image processing package..."
    composer require intervention/image --no-interaction
    
    print_status "Additional packages installed"
    
    echo
    
    # Step 4: Environment setup
    print_header "Step 4: Environment Configuration"
    
    if [ ! -f ".env" ]; then
        print_info "Creating .env file from .env.example..."
        cp .env.example .env
        print_status ".env file created"
    else
        print_warning ".env file already exists. Skipping..."
    fi
    
    # Generate application key
    print_info "Generating application key..."
    php artisan key:generate --force
    print_status "Application key generated"
    
    echo
    
    # Step 5: Database setup
    print_header "Step 5: Database Setup"
    
    # Create database directory
    if [ ! -d "database" ]; then
        mkdir -p database
        print_status "Database directory created"
    fi
    
    # Create SQLite database file
    DB_FILE="database/concure.sqlite"
    if [ ! -f "$DB_FILE" ]; then
        touch "$DB_FILE"
        chmod 664 "$DB_FILE"
        print_status "SQLite database file created"
    else
        print_warning "Database file already exists"
    fi
    
    # Update .env with correct database path
    CURRENT_DIR=$(pwd)
    sed -i.bak "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/${DB_FILE}|" .env
    print_status "Database path updated in .env"
    
    echo
    
    # Step 6: Run ConCure setup
    print_header "Step 6: ConCure System Setup"
    
    print_info "Running ConCure setup command..."
    php artisan concure:setup
    
    echo
    
    # Step 7: Frontend assets (if Node.js is available)
    if command_exists npm; then
        print_header "Step 7: Frontend Assets"
        
        print_info "Installing Node.js dependencies..."
        npm install
        
        print_info "Building frontend assets..."
        npm run build
        
        print_status "Frontend assets compiled"
        echo
    fi
    
    # Step 8: Set permissions
    print_header "Step 8: Setting Permissions"
    
    # Set storage permissions
    if [ -d "storage" ]; then
        chmod -R 775 storage
        print_status "Storage permissions set"
    fi
    
    # Set bootstrap cache permissions
    if [ -d "bootstrap/cache" ]; then
        chmod -R 775 bootstrap/cache
        print_status "Bootstrap cache permissions set"
    fi
    
    # Set database permissions
    if [ -f "$DB_FILE" ]; then
        chmod 664 "$DB_FILE"
        print_status "Database file permissions set"
    fi
    
    echo
    
    # Step 9: Final checks
    print_header "Step 9: Final System Check"
    
    # Test database connection
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection: OK';" 2>/dev/null; then
        print_status "Database connection test passed"
    else
        print_error "Database connection test failed"
    fi
    
    # Check if storage link exists
    if [ -L "public/storage" ]; then
        print_status "Storage link exists"
    else
        print_warning "Storage link missing (this is normal for fresh installations)"
    fi
    
    echo
    
    # Installation complete
    print_header "🎉 Installation Complete!"
    echo
    print_status "ConCure Clinic Management System has been successfully installed!"
    echo
    
    # Display next steps
    echo -e "${CYAN}📋 Next Steps:${NC}"
    echo "  1. Start the development server:"
    echo -e "     ${YELLOW}php artisan serve${NC}"
    echo
    echo "  2. Open your browser and visit:"
    echo -e "     ${YELLOW}http://localhost:8000${NC}"
    echo
    echo "  3. Login with these default credentials:"
    echo
    echo -e "${CYAN}👤 Default Login Credentials:${NC}"
    echo "  • Program Owner: program_owner / ConCure2024!"
    echo "  • Admin:         admin / admin123"
    echo "  • Doctor:        doctor / doctor123"
    echo "  • Assistant:     assistant / assistant123"
    echo "  • Nurse:         nurse / nurse123"
    echo "  • Accountant:    accountant / accountant123"
    echo
    echo -e "${YELLOW}⚠️  Important: Change default passwords after first login!${NC}"
    echo
    echo -e "${CYAN}🌟 Features Available:${NC}"
    echo "  • Patient Management with Medical Records"
    echo "  • Prescription & Lab Request System"
    echo "  • Diet Planning with Nutrition Database"
    echo "  • Financial Management (Invoices & Expenses)"
    echo "  • Advertisement Management"
    echo "  • Multi-language Support (English, Arabic, Kurdish)"
    echo "  • Role-based Access Control"
    echo "  • Audit Logging & Activity Monitoring"
    echo
    echo -e "${GREEN}🚀 Ready to manage your clinic efficiently!${NC}"
    echo
}

# Run the main function
main "$@"
