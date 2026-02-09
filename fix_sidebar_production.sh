#!/bin/bash

# Production Sidebar Fix Script for ConCure Cloud
echo "🔧 Applying production sidebar fix..."

# Backup the original file
echo "📋 Creating backup..."
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup.$(date +%Y%m%d_%H%M%S)

# Create a temporary CSS fix file
cat > /tmp/sidebar_fix.css << 'EOF'
/* PRODUCTION SIDEBAR FIX - FORCE PROPER SPACING */
body {
    margin: 0;
    padding: 0;
}

.sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 290px !important;
    height: 100vh !important;
    z-index: 1000 !important;
}

.main-content {
    margin-left: 290px !important;
    margin-top: 60px !important;
    padding-left: 20px !important;
    min-width: calc(100vw - 310px) !important;
    position: relative !important;
    z-index: 1 !important;
}

.topbar {
    position: fixed !important;
    top: 0 !important;
    left: 290px !important;
    right: 0 !important;
    height: 60px !important;
    z-index: 999 !important;
}

.main-footer {
    margin-left: 290px !important;
}

/* Mobile responsive */
@media (max-width: 991.98px) {
    .main-content {
        margin-left: 0 !important;
        padding-left: 15px !important;
        min-width: 100% !important;
    }
    .topbar {
        left: 0 !important;
    }
    .main-footer {
        margin-left: 0 !important;
    }
}

/* Container adjustments */
.container-fluid {
    padding-left: 15px !important;
    padding-right: 15px !important;
}
EOF

# Find the closing </style> tag and insert our CSS before it
echo "🔧 Applying CSS fix to layout file..."
sed -i '/<\/style>/i\
\
        /* PRODUCTION SIDEBAR FIX - FORCE PROPER SPACING */\
        body {\
            margin: 0;\
            padding: 0;\
        }\
\
        .sidebar {\
            position: fixed !important;\
            top: 0 !important;\
            left: 0 !important;\
            width: 290px !important;\
            height: 100vh !important;\
            z-index: 1000 !important;\
        }\
\
        .main-content {\
            margin-left: 290px !important;\
            margin-top: 60px !important;\
            padding-left: 20px !important;\
            min-width: calc(100vw - 310px) !important;\
            position: relative !important;\
            z-index: 1 !important;\
        }\
\
        .topbar {\
            position: fixed !important;\
            top: 0 !important;\
            left: 290px !important;\
            right: 0 !important;\
            height: 60px !important;\
            z-index: 999 !important;\
        }\
\
        .main-footer {\
            margin-left: 290px !important;\
        }\
\
        /* Mobile responsive */\
        @media (max-width: 991.98px) {\
            .main-content {\
                margin-left: 0 !important;\
                padding-left: 15px !important;\
                min-width: 100% !important;\
            }\
            .topbar {\
                left: 0 !important;\
            }\
            .main-footer {\
                margin-left: 0 !important;\
            }\
        }\
\
        /* Container adjustments */\
        .container-fluid {\
            padding-left: 15px !important;\
            padding-right: 15px !important;\
        }' resources/views/layouts/app.blade.php

# Clear all caches
echo "🧹 Clearing all caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Clean up
rm -f /tmp/sidebar_fix.css

echo "🎉 Production sidebar fix applied successfully!"
echo "📋 Backup created with timestamp"
echo "🌐 Please refresh your browser and check the website now"
echo "💡 If still having issues, try hard refresh (Ctrl+F5 or Cmd+Shift+R)"
