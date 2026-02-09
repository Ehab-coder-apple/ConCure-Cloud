#!/bin/bash

echo "🏥 Starting ConCure Installation..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed."
    echo "Please install PHP first using one of these methods:"
    echo "1. Homebrew: brew install php composer"
    echo "2. XAMPP: Download from https://www.apachefriends.org/"
    echo "3. Manual: Download from https://www.php.net/"
    exit 1
fi

# Check if Composer is available
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed."
    echo "Please install Composer: brew install composer"
    exit 1
fi

echo "✅ PHP $(php --version | head -n1) found!"
echo "✅ Composer $(composer --version | head -n1) found!"

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist

# Setup environment
echo "⚙️ Setting up environment..."
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
    echo "✅ Database file created"
fi

# Update database path
CURRENT_DIR=$(pwd)
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
else
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
fi
echo "✅ Database path updated"

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
echo "🎉 ConCure is ready!"
echo ""
echo "🚀 Starting the development server..."
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
