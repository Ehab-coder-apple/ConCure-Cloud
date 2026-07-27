#!/bin/bash

###############################################################################
# Auto-Logout Feature - Server Deployment Script
# Run this script on your server via SSH
###############################################################################

echo "=========================================="
echo "Auto-Logout Feature Deployment"
echo "=========================================="
echo ""

# Set project path
PROJECT_PATH="/home/master_mmthsmyaaw/public_html"

echo "📂 Project Path: $PROJECT_PATH"
echo ""

# Check if we're in the right directory
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo "❌ Error: artisan file not found in $PROJECT_PATH"
    echo "Please update PROJECT_PATH in this script"
    exit 1
fi

cd $PROJECT_PATH

echo "✅ Found Laravel project"
echo ""

# Step 1: Run Migration
echo "🗄️  Running database migration..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo "❌ Migration failed!"
    exit 1
fi

echo "✅ Migration completed"
echo ""

# Step 2: Clear Caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "✅ Caches cleared"
echo ""

# Step 3: Rebuild Caches
echo "⚡ Rebuilding caches for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Caches rebuilt"
echo ""

# Step 4: Fix Permissions
echo "🔐 Fixing file permissions..."
chmod -R 755 storage bootstrap/cache public/js 2>/dev/null
chown -R master_mmthsmyaaw:master_mmthsmyaaw storage bootstrap/cache public/js 2>/dev/null

echo "✅ Permissions fixed"
echo ""

# Step 5: Verify Files
echo "🔍 Verifying deployment..."

if [ -f "public/js/auto-logout.js" ]; then
    echo "✅ auto-logout.js found"
else
    echo "❌ auto-logout.js NOT found - please upload it manually"
fi

if [ -f "app/Http/Controllers/SessionActivityController.php" ]; then
    echo "✅ SessionActivityController.php found"
else
    echo "❌ SessionActivityController.php NOT found - please upload it manually"
fi

if [ -f "app/Providers/SessionConfigServiceProvider.php" ]; then
    echo "✅ SessionConfigServiceProvider.php found"
else
    echo "❌ SessionConfigServiceProvider.php NOT found - please upload it manually"
fi

echo ""
echo "=========================================="
echo "✅ Deployment Complete!"
echo "=========================================="
echo ""
echo "📋 Next Steps:"
echo "1. Refresh your browser (Ctrl+Shift+R)"
echo "2. Open browser console (F12)"
echo "3. Look for: '🔐 Auto-logout initialized'"
echo "4. Go to Settings > System Settings"
echo "5. Check for 'Session Duration' field"
echo ""
echo "📊 Check logs:"
echo "tail -f storage/logs/laravel.log"
echo ""

