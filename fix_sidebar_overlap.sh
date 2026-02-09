#!/bin/bash

# Fix Sidebar Overlap Script for ConCure Cloud
echo "🔧 Fixing sidebar overlap issue..."

# Backup the original file
echo "📋 Creating backup..."
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup

# Check if the fix is already applied
if grep -q "margin-left: 290px !important" resources/views/layouts/app.blade.php; then
    echo "✅ Fix already applied! Clearing cache..."
    php artisan view:clear
    php artisan config:clear
    php artisan route:clear
    echo "🎉 Cache cleared! Check your website now."
    exit 0
fi

# Apply the CSS fix
echo "🔧 Applying CSS fix..."

# First, update the .main-content class to add position: relative and z-index: 1
sed -i 's/transition: margin-left 0.3s ease;/transition: margin-left 0.3s ease;\n            position: relative;\n            z-index: 1;/' resources/views/layouts/app.blade.php

# Add the media query fix after .content-wrapper
sed -i '/\.content-wrapper {/,/}/ {
    /}/a\
        \
        /* Force proper spacing for main content */\
        @media (min-width: 992px) {\
            .main-content {\
                margin-left: 290px !important;\
            }\
            .main-footer {\
                margin-left: 290px !important;\
            }\
            .topbar {\
                left: 290px !important;\
            }\
        }
}' resources/views/layouts/app.blade.php

# Clear cache
echo "🧹 Clearing cache..."
php artisan view:clear
php artisan config:clear
php artisan route:clear

echo "🎉 Sidebar overlap fix applied successfully!"
echo "📋 Backup saved as: resources/views/layouts/app.blade.php.backup"
echo "🌐 Please check your website now - the sidebar should no longer overlap."
