#!/bin/bash

# Optimize Nutrition Page Spacing for Better Content Area
echo "📐 Optimizing nutrition page spacing for better content visibility..."

# Adjust the nutrition container spacing for better content area
echo "🎯 Adjusting nutrition container margins for optimal viewing..."

# Update the nutrition container fix with better spacing
sed -i '/<\/style>/i\
\
        /* Optimized Nutrition Page Spacing */\
        .container[style*="margin-top: 80px"] {\
            margin-left: 260px !important;\
            margin-right: 15px !important;\
            max-width: calc(100vw - 275px) !important;\
            padding-left: 15px !important;\
            padding-right: 15px !important;\
        }\
\
        /* Optimized targeting for nutrition pages */\
        body:has(.container[style*="margin-top: 80px"]) .container {\
            margin-left: 260px !important;\
            margin-right: 15px !important;\
            max-width: calc(100vw - 275px) !important;\
        }\
\
        /* Alternative targeting with better spacing */\
        .container:has(.fas.fa-apple-alt) {\
            margin-left: 260px !important;\
            margin-right: 15px !important;\
            max-width: calc(100vw - 275px) !important;\
        }\
\
        /* Universal nutrition content optimization */\
        .container:has(h1:contains("WEIGHT LOSS")),\
        .container:has(.fas.fa-apple-alt),\
        .container:has([class*="nutrition"]),\
        .container:has([class*="diet"]) {\
            margin-left: 260px !important;\
            margin-right: 15px !important;\
            max-width: calc(100vw - 275px) !important;\
            padding-left: 15px !important;\
            padding-right: 15px !important;\
        }\
\
        /* Better spacing for all nutrition routes */\
        [data-route*="nutrition"] .container,\
        [data-page="nutrition"] .container,\
        body[class*="nutrition"] .container,\
        .nutrition-page .container,\
        .page-nutrition .container {\
            margin-left: 260px !important;\
            margin-right: 15px !important;\
            max-width: calc(100vw - 275px) !important;\
            padding-left: 15px !important;\
            padding-right: 15px !important;\
        }\
\
        /* Optimize card spacing within nutrition pages */\
        .container[style*="margin-top: 80px"] .card,\
        .container:has(.fas.fa-apple-alt) .card {\
            margin-bottom: 1rem !important;\
        }\
\
        /* Better row spacing */\
        .container[style*="margin-top: 80px"] .row,\
        .container:has(.fas.fa-apple-alt) .row {\
            margin-left: -10px !important;\
            margin-right: -10px !important;\
        }\
\
        .container[style*="margin-top: 80px"] .row > [class*="col"],\
        .container:has(.fas.fa-apple-alt) .row > [class*="col"] {\
            padding-left: 10px !important;\
            padding-right: 10px !important;\
        }\
\
        /* Mobile responsive with optimized spacing */\
        @media (max-width: 768px) {\
            .container[style*="margin-top: 80px"],\
            .container:has(.fas.fa-apple-alt),\
            [data-route*="nutrition"] .container,\
            body[class*="nutrition"] .container {\
                margin-left: 10px !important;\
                margin-right: 10px !important;\
                max-width: calc(100vw - 20px) !important;\
                padding-left: 10px !important;\
                padding-right: 10px !important;\
            }\
        }' resources/views/layouts/app.blade.php

# Clear caches
echo "🧹 Clearing caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "✅ Nutrition page spacing optimized successfully!"
echo ""
echo "🔧 What was optimized:"
echo "   ✅ Reduced left margin from 270px to 260px for more content space"
echo "   ✅ Adjusted max-width calculation for better content area"
echo "   ✅ Optimized padding for better content visibility"
echo "   ✅ Improved card and row spacing within nutrition pages"
echo "   ✅ Enhanced mobile responsive spacing"
echo ""
echo "🌐 Please refresh the nutrition page: https://www.concure.app/nutrition/12"
echo "💡 The content area should now have better spacing and feel more natural"
