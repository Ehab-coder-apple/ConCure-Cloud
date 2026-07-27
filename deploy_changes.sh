#!/bin/bash

# Script to review, commit, push changes and clear Laravel cache
# Location: Concure Cloud project

cd "/Users/ehabkhorshed/Desktop/Documents/augment-projects/TOTAL CONCURE/Concure Cloud"

echo "=========================================="
echo "Step 1: Review Changes"
echo "=========================================="
git status

echo ""
echo "=========================================="
echo "Step 2: View Detailed Changes"
echo "=========================================="
echo "Would you like to see the diff? (This will show what changed)"
git diff app/Http/Controllers/Master/UserController.php
git diff resources/views/master/users/create.blade.php
git diff resources/views/master/users/edit.blade.php

echo ""
echo "=========================================="
echo "Step 3: Stage Changes"
echo "=========================================="
git add app/Http/Controllers/Master/UserController.php
git add resources/views/master/users/create.blade.php
git add resources/views/master/users/edit.blade.php

echo ""
echo "=========================================="
echo "Step 4: Commit Changes"
echo "=========================================="
git commit -m "Add scientific degree and educational institution fields to Master User management

- Added validation and handling in Master/UserController for scientific_degree and educational_institution
- Added form fields to Master users create view with autocomplete datalist
- Added form fields to Master users edit view with pre-populated values
- Both fields are optional and support custom input
- Matches functionality already present in regular user management"

echo ""
echo "=========================================="
echo "Step 5: Push to GitHub Main Branch"
echo "=========================================="
git push origin main

echo ""
echo "=========================================="
echo "Step 6: Clear Laravel Cache"
echo "=========================================="
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "=========================================="
echo "Step 7: Optimize Application"
echo "=========================================="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=========================================="
echo "✅ All Done!"
echo "=========================================="
echo "Changes have been:"
echo "  ✓ Committed to git"
echo "  ✓ Pushed to GitHub main branch"
echo "  ✓ Laravel cache cleared"
echo "  ✓ Application optimized"
echo ""

