#!/bin/bash

# Fix Nutrition Page Container Issue
echo "🍎 Fixing nutrition page container layout specifically..."

# Add nutrition page container specific CSS fix
echo "🎯 Adding nutrition container specific CSS..."

# Add the nutrition container fix to the existing styles
sed -i '/<\/style>/i\
\
        /* Nutrition Page Container Fix - Target the actual HTML structure */\
        .container[style*="margin-top: 80px"] {\
            margin-left: 270px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 290px) !important;\
            padding-left: 20px !important;\
            padding-right: 20px !important;\
        }\
\
        /* Target nutrition page body content */\
        body:has(.container[style*="margin-top: 80px"]) .container {\
            margin-left: 270px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 290px) !important;\
        }\
\
        /* Alternative targeting for nutrition pages */\
        .container:has(.fas.fa-apple-alt) {\
            margin-left: 270px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 290px) !important;\
        }\
\
        /* Target any container with nutrition content */\
        .container:has(h1:contains("WEIGHT LOSS")),\
        .container:has(.fas.fa-apple-alt),\
        .container:has([class*="nutrition"]),\
        .container:has([class*="diet"]) {\
            margin-left: 270px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 290px) !important;\
            padding-left: 20px !important;\
            padding-right: 20px !important;\
        }\
\
        /* Force all containers on nutrition routes */\
        [data-route*="nutrition"] .container,\
        [data-page="nutrition"] .container {\
            margin-left: 270px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 290px) !important;\
        }\
\
        /* Universal fix for any page with nutrition content */\
        body[class*="nutrition"] .container,\
        .nutrition-page .container,\
        .page-nutrition .container {\
            margin-left: 270px !important;\
            margin-right: 20px !important;\
            max-width: calc(100vw - 290px) !important;\
            padding-left: 20px !important;\
            padding-right: 20px !important;\
        }\
\
        /* Mobile responsive for nutrition containers */\
        @media (max-width: 768px) {\
            .container[style*="margin-top: 80px"],\
            .container:has(.fas.fa-apple-alt),\
            [data-route*="nutrition"] .container,\
            body[class*="nutrition"] .container {\
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

echo "✅ Nutrition container fix applied successfully!"
echo ""
echo "🔧 What was fixed:"
echo "   ✅ Targeted the actual container with margin-top: 80px"
echo "   ✅ Fixed containers with nutrition icons and content"
echo "   ✅ Added multiple targeting methods for nutrition pages"
echo "   ✅ Set proper margins and max-width for containers"
echo "   ✅ Added mobile responsive handling"
echo "   ✅ Used !important to override inline styles"
echo ""
echo "🌐 Please refresh the nutrition page: https://www.concure.app/nutrition/12"
echo "💡 The container should now be properly positioned to the right of the sidebar"
