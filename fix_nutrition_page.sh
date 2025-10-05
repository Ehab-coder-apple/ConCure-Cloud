#!/bin/bash

# Fix Nutrition Page Specific Layout Issue
echo "🍎 Fixing nutrition page layout specifically..."

# Add nutrition page specific CSS fix
echo "🎯 Adding nutrition page specific CSS..."

# Add the nutrition page fix to the existing styles
sed -i '/<\/style>/i\
\
        /* Nutrition Page Specific Fix */\
        body.nutrition-page .main-content,\
        .nutrition-container .main-content,\
        .nutrition-wrapper,\
        .nutrition-content {\
            margin-left: 250px !important;\
            padding-left: 20px !important;\
            width: calc(100vw - 270px) !important;\
        }\
\
        /* Target nutrition page containers */\
        .nutrition-plan-container,\
        .weight-loss-container,\
        .plan-details-container {\
            margin-left: 0 !important;\
            padding-left: 20px !important;\
            padding-right: 20px !important;\
            max-width: none !important;\
        }\
\
        /* Fix for nutrition page cards and content */\
        .nutrition-page .card,\
        .nutrition-page .container-fluid,\
        .nutrition-page .row {\
            margin-left: 0 !important;\
            padding-left: 15px !important;\
            padding-right: 15px !important;\
        }\
\
        /* Ensure nutrition page content wrapper has proper spacing */\
        .nutrition-page .content-wrapper {\
            padding-top: 80px !important;\
            padding-left: 20px !important;\
            padding-right: 20px !important;\
        }\
\
        /* Mobile responsive for nutrition page */\
        @media (max-width: 768px) {\
            body.nutrition-page .main-content,\
            .nutrition-container .main-content,\
            .nutrition-wrapper,\
            .nutrition-content {\
                margin-left: 0 !important;\
                width: 100vw !important;\
                padding-left: 15px !important;\
            }\
            \
            .nutrition-plan-container,\
            .weight-loss-container,\
            .plan-details-container {\
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

echo "✅ Nutrition page fix applied successfully!"
echo ""
echo "🔧 What was fixed:"
echo "   ✅ Added specific CSS for nutrition page layout"
echo "   ✅ Fixed margin and padding for nutrition containers"
echo "   ✅ Ensured proper spacing for nutrition content"
echo "   ✅ Added mobile responsive handling"
echo "   ✅ Used !important to override any conflicting styles"
echo ""
echo "🌐 Please refresh the nutrition page: https://www.concure.app/nutrition/12"
echo "💡 The content should now be properly positioned to the right of the sidebar"
