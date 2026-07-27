#!/bin/bash

# ConCure Installation Without Homebrew
echo "🏥 ConCure Installation (No Homebrew Required)"
echo "=============================================="
echo ""

# Check if we can use system PHP
if command -v php >/dev/null 2>&1; then
    echo "✅ PHP found: $(php --version | head -n1)"
else
    echo "❌ PHP not found. Let's try to install it..."
    
    # Try to install PHP via MacPorts if available
    if command -v port >/dev/null 2>&1; then
        echo "📦 Installing PHP via MacPorts..."
        sudo port install php82 +universal
    else
        echo "⚠️  Please install PHP manually:"
        echo "   1. Download XAMPP from: https://www.apachefriends.org/"
        echo "   2. Or install Homebrew first: /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
        exit 1
    fi
fi

# Check for Composer
if command -v composer >/dev/null 2>&1; then
    echo "✅ Composer found: $(composer --version | head -n1)"
else
    echo "📦 Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Setup ConCure
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
echo "🔑 Generating application key..."
php artisan key:generate --force

# Setup database
echo "🗄️ Setting up database..."
mkdir -p database
if [ ! -f database/concure.sqlite ]; then
    touch database/concure.sqlite
    chmod 664 database/concure.sqlite
fi

# Update database path
CURRENT_DIR=$(pwd)
sed -i.bak "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env

# Run migrations
echo "🌱 Setting up database schema..."
php artisan migrate --force --seed

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "🎉 ConCure setup completed!"
echo ""
echo "🚀 Starting server..."
echo "📱 Open: http://localhost:8000"
echo ""
echo "🔑 Login with:"
echo "   admin / admin123"
echo ""

# Start server
php artisan serve
