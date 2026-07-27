#!/bin/bash

# Fix Duplicate CSS Issue - Remove Conflicting Styles
echo "🔧 Fixing duplicate CSS that's causing layout issues..."

# Remove the duplicate/conflicting CSS blocks that were added
echo "🧹 Removing duplicate CSS blocks..."

# Remove the "Optimized Nutrition Page Spacing" block that's causing conflicts
sed -i '/\/\* Optimized Nutrition Page Spacing \*\//,/^[[:space:]]*$/d' resources/views/layouts/app.blade.php

# Also remove any other duplicate nutrition CSS blocks
sed -i '/\/\* Nutrition Page Container Fix - Target the actual HTML structure \*\//,/^[[:space:]]*$/d' resources/views/layouts/app.blade.php
sed -i '/\/\* Nutrition Page Specific Fix \*\//,/^[[:space:]]*$/d' resources/views/layouts/app.blade.php

# Now add back ONLY the working CSS with slight optimization
echo "✅ Adding back the working CSS with gentle optimization..."

sed -i '/<\/style>/i\
\
        /* Working Nutrition Page Fix - Optimized */\
        .container[style*="margin-top: 80px"] {\
            margin-left: 265px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 285px) !important;\
            padding-left: 20px !important;\
            padding-right: 20px !important;\
        }\
\
        /* Target nutrition page body content */\
        body:has(.container[style*="margin-top: 80px"]) .container {\
            margin-left: 265px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 285px) !important;\
        }\
\
        /* Alternative targeting for nutrition pages */\
        .container:has(.fas.fa-apple-alt) {\
            margin-left: 265px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 285px) !important;\
        }\
\
        /* Mobile responsive for nutrition containers */\
        @media (max-width: 768px) {\
            .container[style*="margin-top: 80px"],\
            .container:has(.fas.fa-apple-alt),\
            body:has(.container[style*="margin-top: 80px"]) .container {\
                margin-left: 15px !important;\
                margin-right: 15px !important;\
                max-width: calc(100vw - 30px) !important;\
                padding-left: 15px !important;\
                padding-right: 15px !important;\
            }\
        }' resources/views/layouts/app.blade.php

# Clear caches
echo "🧹 Clearing caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "✅ Duplicate CSS fixed and working layout restored!"
echo ""
echo "🔧 What was fixed:"
echo "   ✅ Removed duplicate/conflicting CSS blocks"
echo "   ✅ Restored working nutrition page layout"
echo "   ✅ Applied gentle optimization (265px margin instead of 270px)"
echo "   ✅ Kept proper mobile responsive handling"
echo "   ✅ Cleaned up CSS conflicts"
echo ""
echo "🌐 Please refresh the nutrition page: https://www.concure.app/nutrition/12"
echo "💡 The layout should be back to working with slightly better spacing"
