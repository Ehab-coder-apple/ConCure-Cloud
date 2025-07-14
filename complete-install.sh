#!/bin/bash

# ConCure Complete Installation Script
# This script will install all requirements and run ConCure

set -e  # Exit on any error

echo "🏥 ConCure Complete Installation Script"
echo "======================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to install Homebrew
install_homebrew() {
    print_info "Installing Homebrew (you may need to enter your password)..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    
    # Add Homebrew to PATH
    if [[ -f "/opt/homebrew/bin/brew" ]]; then
        echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
        eval "$(/opt/homebrew/bin/brew shellenv)"
        export PATH="/opt/homebrew/bin:$PATH"
    elif [[ -f "/usr/local/bin/brew" ]]; then
        echo 'eval "$(/usr/local/bin/brew shellenv)"' >> ~/.zprofile
        eval "$(/usr/local/bin/brew shellenv)"
        export PATH="/usr/local/bin:$PATH"
    fi
}

# Function to install PHP and Composer
install_php_composer() {
    print_info "Installing PHP and Composer..."
    brew install php composer
    
    # Verify installation
    if command_exists php && command_exists composer; then
        print_status "PHP $(php --version | head -n1) installed"
        print_status "Composer $(composer --version | head -n1) installed"
    else
        print_error "PHP or Composer installation failed"
        exit 1
    fi
}

# Function to install Node.js (optional)
install_nodejs() {
    if ! command_exists node; then
        print_info "Installing Node.js for frontend assets..."
        brew install node
    fi
}

# Function to setup ConCure
setup_concure() {
    print_info "Setting up ConCure application..."
    
    # Install PHP dependencies
    print_info "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    
    # Install additional packages
    print_info "Installing additional packages..."
    composer require barryvdh/laravel-dompdf intervention/image --no-interaction
    
    # Setup environment
    if [ ! -f .env ]; then
        cp .env.example .env
        print_status "Environment file created"
    else
        print_warning "Environment file already exists"
    fi
    
    # Generate application key
    print_info "Generating application key..."
    php artisan key:generate --force
    
    # Setup database
    print_info "Setting up database..."
    mkdir -p database
    if [ ! -f database/concure.sqlite ]; then
        touch database/concure.sqlite
        chmod 664 database/concure.sqlite
        print_status "Database file created"
    else
        print_warning "Database file already exists"
    fi
    
    # Update database path in .env
    CURRENT_DIR=$(pwd)
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
    else
        sed -i "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
    fi
    print_status "Database path updated"
    
    # Run migrations and seeders
    print_info "Setting up database schema and sample data..."
    php artisan migrate --force --seed
    
    # Create storage link
    print_info "Creating storage link..."
    php artisan storage:link
    
    # Set permissions
    print_info "Setting file permissions..."
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    
    # Install Node.js dependencies if Node is available
    if command_exists npm; then
        print_info "Installing frontend dependencies..."
        npm install
        print_info "Building frontend assets..."
        npm run build
    fi
}

# Main installation process
main() {
    print_info "Starting ConCure installation process..."
    echo ""
    
    # Check if we're in the right directory
    if [ ! -f "composer.json" ]; then
        print_error "composer.json not found. Please run this script from the ConCure directory."
        exit 1
    fi
    
    # Check if Homebrew is installed
    if ! command_exists brew; then
        print_warning "Homebrew not found. Installing..."
        install_homebrew
        
        # Reload environment
        if [[ -f "/opt/homebrew/bin/brew" ]]; then
            eval "$(/opt/homebrew/bin/brew shellenv)"
            export PATH="/opt/homebrew/bin:$PATH"
        elif [[ -f "/usr/local/bin/brew" ]]; then
            eval "$(/usr/local/bin/brew shellenv)"
            export PATH="/usr/local/bin:$PATH"
        fi
    else
        print_status "Homebrew found"
    fi
    
    # Check if PHP is installed
    if ! command_exists php; then
        print_warning "PHP not found. Installing..."
        install_php_composer
    else
        PHP_VERSION=$(php --version | head -n1)
        print_status "PHP found: $PHP_VERSION"
        
        # Check if Composer is installed
        if ! command_exists composer; then
            print_warning "Composer not found. Installing..."
            brew install composer
        else
            print_status "Composer found: $(composer --version | head -n1)"
        fi
    fi
    
    # Install Node.js (optional)
    install_nodejs
    
    # Setup ConCure
    setup_concure
    
    echo ""
    print_status "🎉 ConCure installation completed successfully!"
    echo ""
    print_info "🚀 Starting ConCure development server..."
    print_info "📱 ConCure will be available at: http://localhost:8000"
    echo ""
    print_info "🔑 Default login credentials:"
    echo "   Program Owner: program_owner / ConCure2024!"
    echo "   Admin: admin / admin123"
    echo "   Doctor: doctor / doctor123"
    echo ""
    print_info "Press Ctrl+C to stop the server"
    echo ""
    
    # Start the development server
    php artisan serve
}

# Check if script is being run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
