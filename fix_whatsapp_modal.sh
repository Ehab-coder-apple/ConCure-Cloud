#!/bin/bash

# Fix WhatsApp Modal Translation Issue
# This script updates the production view file to fix the Blade syntax issue

echo "🔧 Fixing WhatsApp Modal Translation Issue..."

# Navigate to the Laravel application directory
cd ~/public_html || exit 1

# Backup the original file
cp resources/views/nutrition/show.blade.php resources/views/nutrition/show.blade.php.backup
echo "✅ Backup created: show.blade.php.backup"

# Fix line 647: Modal title
sed -i "647s/.*/          <i class=\"fab fa-whatsapp me-2 text-success\"><\/i>{{ __('Send via WhatsApp') }} — {{ __('Choose Food Language') }}/" resources/views/nutrition/show.blade.php

# Fix line 652: Food Language label
sed -i "652s/.*/        <label for=\"whatsappLanguageSelect\" class=\"form-label\">{{ __('Food Language') }}<\/label>/" resources/views/nutrition/show.blade.php

# Fix line 660: Cancel button
sed -i "660s/.*/        <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">{{ __('Cancel') }}<\/button>/" resources/views/nutrition/show.blade.php

# Fix line 663: Send button
sed -i "663s/.*/          <i class=\"fab fa-whatsapp me-1\"><\/i>{{ __('Send') }}/" resources/views/nutrition/show.blade.php

echo "✅ View file updated"

# Clear all Laravel caches
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

echo "✅ All caches cleared"

# Verify the changes
echo ""
echo "📋 Verifying changes (showing lines 645-665):"
sed -n '645,665p' resources/views/nutrition/show.blade.php

echo ""
echo "✅ Fix complete! Please refresh your browser."
echo "📝 Original file backed up as: resources/views/nutrition/show.blade.php.backup"

