#!/bin/bash

# Emergency Sidebar Fix - Direct CSS Injection
echo "🚨 Applying emergency sidebar fix with direct CSS injection..."

# Backup the original file
echo "📋 Creating emergency backup..."
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.emergency.$(date +%Y%m%d_%H%M%S)

# Create a comprehensive CSS override that will definitely work
echo "🔧 Injecting emergency CSS fix..."

# Find the <head> section and add our CSS right after it
sed -i '/<head>/a\
    <style>\
        /* EMERGENCY SIDEBAR FIX - HIGHEST PRIORITY */\
        * {\
            box-sizing: border-box !important;\
        }\
        \
        body {\
            margin: 0 !important;\
            padding: 0 !important;\
            overflow-x: hidden !important;\
        }\
        \
        .sidebar {\
            position: fixed !important;\
            top: 0 !important;\
            left: 0 !important;\
            width: 240px !important;\
            height: 100vh !important;\
            z-index: 9999 !important;\
            background: #1e293b !important;\
            overflow-y: auto !important;\
            overflow-x: hidden !important;\
        }\
        \
        .main-content,\
        .content-wrapper,\
        main,\
        .container-fluid {\
            margin-left: 240px !important;\
            padding-left: 20px !important;\
            width: calc(100vw - 260px) !important;\
            max-width: calc(100vw - 260px) !important;\
            min-height: 100vh !important;\
            position: relative !important;\
            z-index: 1 !important;\
        }\
        \
        .topbar,\
        .navbar,\
        .header {\
            position: fixed !important;\
            top: 0 !important;\
            left: 240px !important;\
            right: 0 !important;\
            width: calc(100vw - 240px) !important;\
            height: 60px !important;\
            z-index: 9998 !important;\
        }\
        \
        .main-footer,\
        .footer {\
            margin-left: 240px !important;\
            width: calc(100vw - 240px) !important;\
        }\
        \
        /* Force content area positioning */\
        body > div,\
        body > main,\
        .app-wrapper,\
        .wrapper {\
            margin-left: 240px !important;\
            width: calc(100vw - 240px) !important;\
        }\
        \
        /* Mobile responsive */\
        @media (max-width: 768px) {\
            .sidebar {\
                transform: translateX(-100%) !important;\
                transition: transform 0.3s ease !important;\
            }\
            \
            .main-content,\
            .content-wrapper,\
            main,\
            .container-fluid,\
            body > div,\
            body > main,\
            .app-wrapper,\
            .wrapper {\
                margin-left: 0 !important;\
                width: 100vw !important;\
                max-width: 100vw !important;\
                padding-left: 15px !important;\
            }\
            \
            .topbar,\
            .navbar,\
            .header {\
                left: 0 !important;\
                width: 100vw !important;\
            }\
            \
            .main-footer,\
            .footer {\
                margin-left: 0 !important;\
                width: 100vw !important;\
            }\
        }\
    </style>' resources/views/layouts/app.blade.php

# Clear all caches aggressively
echo "🧹 Clearing all caches aggressively..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Also clear any potential opcache
if command -v php >/dev/null 2>&1; then
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared\n'; }"
fi

echo "🚨 Emergency sidebar fix applied!"
echo "📋 This fix uses the highest CSS priority and targets all possible selectors"
echo "🔧 Changes applied:"
echo "   - Reduced sidebar to 240px width"
echo "   - Added direct CSS injection in <head>"
echo "   - Used highest z-index values"
echo "   - Targeted all possible content selectors"
echo "   - Added mobile responsive handling"
echo ""
echo "🌐 Please hard refresh your browser multiple times:"
echo "   1. Ctrl+F5 or Cmd+Shift+R"
echo "   2. Clear browser cache completely"
echo "   3. Try incognito/private mode"
echo ""
echo "💡 If this doesn't work, there might be external CSS overriding our styles"
