#!/bin/bash

# Restore and Apply Gentle Fix
echo "🔄 Restoring original layout and applying gentle fix..."

# Find the most recent backup and restore it
echo "📋 Restoring from backup..."
LATEST_BACKUP=$(ls -t resources/views/layouts/app.blade.php.*.backup.* 2>/dev/null | head -n1)
if [ -n "$LATEST_BACKUP" ]; then
    cp "$LATEST_BACKUP" resources/views/layouts/app.blade.php
    echo "✅ Restored from: $LATEST_BACKUP"
else
    echo "❌ No backup found, using git to restore..."
    git checkout HEAD~3 -- resources/views/layouts/app.blade.php
fi

# Now apply a very gentle, targeted fix
echo "🔧 Applying gentle sidebar fix..."

# Create a minimal CSS fix that only targets the specific issue
cat > /tmp/gentle_fix.css << 'EOF'
/* Gentle Sidebar Fix - Only Essential Changes */
.sidebar {
    position: fixed;
    width: 250px;
    height: 100vh;
    top: 0;
    left: 0;
    z-index: 1000;
}

.main-content {
    margin-left: 250px;
    padding-left: 20px;
}

.topbar {
    margin-left: 250px;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding-left: 15px;
    }
    .topbar {
        margin-left: 0;
    }
}
EOF

# Add the gentle fix to the existing styles
sed -i '/<\/style>/i\
\
        /* Gentle Sidebar Fix - Only Essential Changes */\
        .sidebar {\
            position: fixed;\
            width: 250px;\
            height: 100vh;\
            top: 0;\
            left: 0;\
            z-index: 1000;\
        }\
\
        .main-content {\
            margin-left: 250px;\
            padding-left: 20px;\
        }\
\
        .topbar {\
            margin-left: 250px;\
        }\
\
        @media (max-width: 768px) {\
            .main-content {\
                margin-left: 0;\
                padding-left: 15px;\
            }\
            .topbar {\
                margin-left: 0;\
            }\
        }' resources/views/layouts/app.blade.php

# Clean up
rm -f /tmp/gentle_fix.css

# Clear caches
echo "🧹 Clearing caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "✅ Gentle fix applied successfully!"
echo "🔧 Changes made:"
echo "   - Restored original layout from backup"
echo "   - Applied minimal sidebar positioning fix"
echo "   - Fixed sidebar width to 250px"
echo "   - Added proper margin to main content"
echo "   - Included mobile responsive handling"
echo ""
echo "🌐 Please refresh your browser now"
echo "💡 This should fix the sidebar without breaking the layout"
