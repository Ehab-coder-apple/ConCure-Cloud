#!/bin/bash

# Fix Laravel Package Discovery Error
echo "🔧 Fixing Laravel Package Discovery Error..."
echo "============================================"

# Step 1: Create .env file if missing
if [ ! -f .env ]; then
    echo "📄 Creating .env file..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Step 2: Clear all caches
echo "🧹 Clearing caches..."
composer clear-cache
rm -rf vendor/
rm -f composer.lock

# Step 3: Update database path in .env
echo "🗄️ Updating database configuration..."
CURRENT_DIR=$(pwd)
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
else
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
fi

# Step 4: Create database file
echo "🗄️ Creating database file..."
mkdir -p database
touch database/concure.sqlite
chmod 664 database/concure.sqlite

# Step 5: Install dependencies without scripts
echo "📦 Installing dependencies (without scripts)..."
composer install --no-scripts --no-interaction

# Step 6: Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force

# Step 7: Run package discovery manually
echo "🔍 Running package discovery..."
php artisan package:discover --ansi

# Step 8: Run migrations
echo "🌱 Running database migrations..."
php artisan migrate --force --seed

# Step 9: Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Step 10: Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "🎉 Laravel error fixed! ConCure is ready!"
echo ""
echo "🚀 Starting server..."
echo "📱 Open: http://localhost:8000"
echo ""
echo "🔑 Login with:"
echo "   admin / admin123"
echo ""

# Start the server
php artisan serve
