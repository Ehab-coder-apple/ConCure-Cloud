#!/bin/bash

# Content Positioning Fix Script for ConCure Cloud
echo "🔧 Fixing content positioning and sidebar sizing..."

# Backup the original file
echo "📋 Creating backup..."
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup.$(date +%Y%m%d_%H%M%S)

# Create the targeted fix
echo "🔧 Applying content positioning fix..."

# Add the fix right before the closing </style> tag
sed -i '/<\/style>/i\
\
        /* CONTENT POSITIONING FIX - MOVE CONTENT RIGHT */\
        .sidebar {\
            position: fixed !important;\
            top: 0 !important;\
            left: 0 !important;\
            width: 250px !important;\
            height: 100vh !important;\
            z-index: 1000 !important;\
            overflow-y: auto !important;\
        }\
\
        .main-content {\
            margin-left: 250px !important;\
            padding-left: 30px !important;\
            padding-right: 20px !important;\
            width: calc(100vw - 280px) !important;\
            min-height: 100vh !important;\
            position: relative !important;\
            z-index: 1 !important;\
        }\
\
        .topbar {\
            position: fixed !important;\
            top: 0 !important;\
            left: 250px !important;\
            right: 0 !important;\
            width: calc(100vw - 250px) !important;\
            height: 60px !important;\
            z-index: 999 !important;\
        }\
\
        .main-footer {\
            margin-left: 250px !important;\
            width: calc(100vw - 250px) !important;\
        }\
\
        /* Content wrapper adjustments */\
        .content-wrapper {\
            padding: 20px !important;\
            margin-top: 60px !important;\
        }\
\
        /* Container adjustments */\
        .container-fluid {\
            max-width: none !important;\
            padding-left: 0 !important;\
            padding-right: 0 !important;\
        }\
\
        /* Card and content adjustments */\
        .card {\
            margin-bottom: 20px !important;\
        }\
\
        /* Mobile responsive */\
        @media (max-width: 991.98px) {\
            .sidebar {\
                transform: translateX(-100%) !important;\
                transition: transform 0.3s ease !important;\
            }\
            .main-content {\
                margin-left: 0 !important;\
                width: 100vw !important;\
                padding-left: 15px !important;\
                padding-right: 15px !important;\
            }\
            .topbar {\
                left: 0 !important;\
                width: 100vw !important;\
            }\
            .main-footer {\
                margin-left: 0 !important;\
                width: 100vw !important;\
            }\
            .content-wrapper {\
                padding: 15px !important;\
            }\
        }\
\
        /* Show sidebar on mobile when toggled */\
        @media (max-width: 991.98px) {\
            .sidebar.show {\
                transform: translateX(0) !important;\
            }\
        }' resources/views/layouts/app.blade.php

# Clear all caches
echo "🧹 Clearing all caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "🎉 Content positioning fix applied successfully!"
echo "📋 Changes made:"
echo "   - Reduced sidebar width from 290px to 250px"
echo "   - Added proper left margin (250px) to main content"
echo "   - Added padding to content wrapper"
echo "   - Fixed topbar positioning"
echo "   - Added mobile responsive handling"
echo ""
echo "🌐 Please hard refresh your browser (Ctrl+F5 or Cmd+Shift+R)"
echo "💡 The content should now be properly positioned to the right of the sidebar"
