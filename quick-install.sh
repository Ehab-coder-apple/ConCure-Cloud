#!/bin/bash

# ConCure Quick Installation Script
echo "🏥 ConCure Quick Installation Starting..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1+ first."
    echo "   Run: brew install php"
    exit 1
fi

# Check if Composer is available
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   Run: brew install composer"
    exit 1
fi

echo "✅ PHP and Composer found!"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction

# Copy environment file
echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Environment file created"
else
    echo "⚠️ Environment file already exists"
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force

# Create database directory and file
echo "🗄️ Setting up database..."
mkdir -p database
if [ ! -f database/concure.sqlite ]; then
    touch database/concure.sqlite
    chmod 664 database/concure.sqlite
    echo "✅ Database file created"
else
    echo "⚠️ Database file already exists"
fi

# Update database path in .env
CURRENT_DIR=$(pwd)
sed -i.bak "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
echo "✅ Database path updated"

# Run migrations and seeders
echo "🌱 Setting up database schema and sample data..."
php artisan migrate --force --seed

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "🎉 ConCure installation completed successfully!"
echo ""
echo "🚀 To start the server, run:"
echo "   php artisan serve"
echo ""
echo "🌐 Then open your browser to:"
echo "   http://localhost:8000"
echo ""
echo "🔑 Default login credentials:"
echo "   Program Owner: program_owner / ConCure2024!"
echo "   Admin: admin / admin123"
echo "   Doctor: doctor / doctor123"
echo ""
