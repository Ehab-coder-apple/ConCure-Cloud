#!/bin/bash

# ConCure One-Command Installer and Runner
echo "🏥 ConCure Clinic Management System - One-Command Installer"
echo "=========================================================="
echo ""

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to install Homebrew
install_homebrew() {
    echo "📦 Installing Homebrew..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    
    # Add to PATH
    if [[ -f "/opt/homebrew/bin/brew" ]]; then
        echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
        eval "$(/opt/homebrew/bin/brew shellenv)"
    elif [[ -f "/usr/local/bin/brew" ]]; then
        echo 'eval "$(/usr/local/bin/brew shellenv)"' >> ~/.zprofile
        eval "$(/usr/local/bin/brew shellenv)"
    fi
}

# Function to install PHP and Composer
install_php_composer() {
    echo "🐘 Installing PHP and Composer..."
    brew install php composer
}

# Function to setup ConCure
setup_concure() {
    echo "🏥 Setting up ConCure..."
    
    # Install dependencies
    echo "📦 Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist
    
    # Setup environment
    if [ ! -f .env ]; then
        cp .env.example .env
        echo "✅ Environment file created"
    fi
    
    # Generate key
    php artisan key:generate --force
    
    # Setup database
    mkdir -p database
    if [ ! -f database/concure.sqlite ]; then
        touch database/concure.sqlite
        chmod 664 database/concure.sqlite
    fi
    
    # Update database path
    CURRENT_DIR=$(pwd)
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
    else
        sed -i "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
    fi
    
    # Run migrations
    php artisan migrate --force --seed
    
    # Create storage link
    php artisan storage:link
    
    # Set permissions
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
}

# Main installation process
main() {
    echo "🔍 Checking system requirements..."
    
    # Check if Homebrew is installed
    if ! command_exists brew; then
        echo "❌ Homebrew not found. Installing..."
        install_homebrew
        
        # Reload shell environment
        if [[ -f "/opt/homebrew/bin/brew" ]]; then
            eval "$(/opt/homebrew/bin/brew shellenv)"
        elif [[ -f "/usr/local/bin/brew" ]]; then
            eval "$(/usr/local/bin/brew shellenv)"
        fi
    else
        echo "✅ Homebrew found"
    fi
    
    # Check if PHP is installed
    if ! command_exists php; then
        echo "❌ PHP not found. Installing..."
        install_php_composer
    else
        echo "✅ PHP found: $(php --version | head -n1)"
    fi
    
    # Check if Composer is installed
    if ! command_exists composer; then
        echo "❌ Composer not found. Installing..."
        brew install composer
    else
        echo "✅ Composer found: $(composer --version | head -n1)"
    fi
    
    # Setup ConCure
    setup_concure
    
    echo ""
    echo "🎉 ConCure installation completed successfully!"
    echo ""
    echo "🚀 Starting ConCure server..."
    echo "📱 ConCure will be available at: http://localhost:8000"
    echo ""
    echo "🔑 Default login credentials:"
    echo "   Program Owner: program_owner / ConCure2024!"
    echo "   Admin: admin / admin123"
    echo "   Doctor: doctor / doctor123"
    echo ""
    echo "Press Ctrl+C to stop the server"
    echo ""
    
    # Start the server
    php artisan serve
}

# Run main function
main
