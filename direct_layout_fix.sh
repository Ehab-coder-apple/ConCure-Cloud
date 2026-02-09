#!/bin/bash

# Direct Layout Fix - Remove Emergency CSS and Apply Clean Fix
echo "🔧 Removing emergency CSS and applying clean layout fix..."

# First, let's remove any emergency CSS that was injected into the head
echo "🧹 Cleaning up emergency CSS..."

# Remove the emergency CSS block that was added to the head section
sed -i '/<style>/,/<\/style>/{
    /EMERGENCY SIDEBAR FIX/,/}<\/style>/d
}' resources/views/layouts/app.blade.php

# Also remove any standalone emergency CSS blocks
sed -i '/EMERGENCY SIDEBAR FIX/,/^[[:space:]]*$/d' resources/views/layouts/app.blade.php

# Now apply a clean, minimal sidebar fix
echo "🎯 Applying clean sidebar positioning..."

# Find the existing style section and add our clean CSS
sed -i '/<\/style>/i\
\
        /* Clean Sidebar Positioning Fix */\
        .sidebar {\
            position: fixed;\
            top: 0;\
            left: 0;\
            width: 250px;\
            height: 100vh;\
            z-index: 1000;\
            overflow-y: auto;\
        }\
\
        .main-content {\
            margin-left: 250px;\
            padding-left: 20px;\
            min-height: 100vh;\
        }\
\
        .topbar {\
            margin-left: 250px;\
            position: fixed;\
            top: 0;\
            right: 0;\
            left: 250px;\
            height: 60px;\
            z-index: 999;\
        }\
\
        .content-wrapper {\
            padding-top: 80px;\
            padding-left: 20px;\
            padding-right: 20px;\
        }\
\
        /* Mobile Responsive */\
        @media (max-width: 768px) {\
            .sidebar {\
                transform: translateX(-100%);\
                transition: transform 0.3s ease;\
            }\
            \
            .main-content {\
                margin-left: 0;\
                padding-left: 15px;\
            }\
            \
            .topbar {\
                left: 0;\
                margin-left: 0;\
            }\
            \
            .content-wrapper {\
                padding-left: 15px;\
                padding-right: 15px;\
            }\
        }' resources/views/layouts/app.blade.php

# Clear all caches
echo "🧹 Clearing all caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "✅ Clean layout fix applied successfully!"
echo ""
echo "🔧 What was fixed:"
echo "   ✅ Removed aggressive emergency CSS"
echo "   ✅ Applied clean sidebar positioning (250px width)"
echo "   ✅ Fixed main content margin (250px left)"
echo "   ✅ Proper topbar positioning"
echo "   ✅ Added content wrapper padding"
echo "   ✅ Mobile responsive design"
echo ""
echo "🌐 Please refresh your browser now"
echo "💡 The layout should be clean and the sidebar properly positioned"
