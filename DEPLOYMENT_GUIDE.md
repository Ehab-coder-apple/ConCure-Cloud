# 🚀 Auto-Logout Feature - Deployment Guide

## ✅ Code Already Pushed to GitHub

The auto-logout feature has been successfully committed and pushed to GitHub main branch.

**Commit:** `feat: Add auto-logout security feature with configurable session timeout`

---

## 📋 Deployment Options

### Option 1: Automated Deployment Script

1. **Edit the deployment script** with your server details:
   ```bash
   nano deploy.sh
   ```

2. **Update these variables:**
   ```bash
   SERVER_USER="your-username"           # Your SSH username
   SERVER_HOST="your-server-ip"          # Your server IP or domain
   SERVER_PATH="/var/www/concure-cloud"  # Project path on server
   SSH_KEY_PATH="~/.ssh/id_rsa"          # Your SSH key path
   ```

3. **Run the deployment script:**
   ```bash
   ./deploy.sh
   ```

---

### Option 2: Manual Deployment (SSH into Server)

1. **SSH into your production server:**
   ```bash
   ssh your-username@your-server-ip
   ```

2. **Navigate to project directory:**
   ```bash
   cd /var/www/concure-cloud
   # Or wherever your project is located
   ```

3. **Pull the latest changes:**
   ```bash
   git pull origin main
   ```

4. **Run database migration:**
   ```bash
   php artisan migrate --force
   ```
   This adds the `session_lifetime` column to the `settings` table.

5. **Clear all caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

6. **Optimize for production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Restart PHP-FPM** (adjust PHP version as needed):
   ```bash
   sudo systemctl restart php8.2-fpm
   # OR
   sudo systemctl restart php8.1-fpm
   # OR
   sudo systemctl restart php-fpm
   ```

8. **Restart web server:**
   ```bash
   # For Nginx:
   sudo systemctl restart nginx
   
   # For Apache:
   sudo systemctl restart apache2
   ```

---

## 🔍 Post-Deployment Verification

### 1. Check Migration Status
```bash
php artisan migrate:status
```
Look for: `2026_01_08_184121_add_session_lifetime_to_settings_table` - should show "Ran"

### 2. Verify Files Exist
```bash
ls -la public/js/auto-logout.js
ls -la app/Http/Controllers/SessionActivityController.php
ls -la app/Providers/SessionConfigServiceProvider.php
```

### 3. Test in Browser

1. **Login to the application**
2. **Open Browser Console** (F12)
3. **Look for initialization message:**
   ```
   🔐 Auto-logout initialized
   ⏱️ Timeout: 5 minutes
   ⚠️ Warning: 1 minute before logout
   ```

4. **Check Network Tab** for keep-alive requests:
   - Should see POST requests to `/session/keep-alive` every 60 seconds
   - Status should be `200 OK`

5. **Test Auto-Logout:**
   - Go to **Settings > System Settings**
   - Set "Session Duration" to **2 minutes**
   - Stay inactive for 1 minute
   - You should see a warning dialog
   - After 2 minutes total, you should be logged out

### 4. Check Logs
```bash
tail -f storage/logs/laravel.log
```
Look for:
- `Keep-alive request received`
- `Keep-alive successful`

---

## 🐛 Troubleshooting

### Issue: Migration Fails
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# If connection works, try:
php artisan migrate:refresh --path=/database/migrations/2026_01_08_184121_add_session_lifetime_to_settings_table.php
```

### Issue: 419 CSRF Token Mismatch
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Regenerate application key (CAUTION: This will log out all users)
php artisan key:generate
```

### Issue: JavaScript Not Loading
```bash
# Check file permissions
ls -la public/js/auto-logout.js

# Fix permissions if needed
sudo chown -R www-data:www-data public/js
sudo chmod -R 755 public/js

# Clear browser cache and hard refresh (Ctrl+Shift+R)
```

### Issue: Keep-Alive Requests Failing
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check web server error logs
# For Nginx:
tail -f /var/log/nginx/error.log

# For Apache:
tail -f /var/log/apache2/error.log
```

### Issue: Settings Page Not Showing Session Duration
```bash
# Clear config cache
php artisan config:clear

# Re-cache config
php artisan config:cache

# Check if migration ran
php artisan migrate:status | grep session_lifetime
```

---

## 📊 Feature Summary

### What Was Deployed:

✅ **Auto-logout after inactivity** (default: 5 minutes)  
✅ **Warning dialog** (1 minute before logout)  
✅ **Keep-alive pings** (every 60 seconds when active)  
✅ **Admin-configurable timeout** (Settings > System Settings)  
✅ **Activity tracking** (mouse, keyboard, scroll)  
✅ **Page visibility detection** (pauses when tab hidden)  
✅ **Audit logging** for auto-logout events  
✅ **CSRF token handling** for keep-alive requests  

### Files Added/Modified:

**New Files:**
- `app/Http/Controllers/SessionActivityController.php`
- `app/Providers/SessionConfigServiceProvider.php`
- `public/js/auto-logout.js`
- `database/migrations/2026_01_08_184121_add_session_lifetime_to_settings_table.php`

**Modified Files:**
- `resources/views/layouts/app.blade.php`
- `resources/views/settings/index.blade.php`
- `resources/views/auth/login.blade.php`
- `routes/web.php`
- `config/concure.php`
- `app/Http/Controllers/SettingsController.php`

---

## 🎯 Next Steps After Deployment

1. ✅ Test the feature thoroughly
2. ✅ Configure session timeout in Settings
3. ✅ Monitor logs for any issues
4. ✅ Inform users about the new security feature
5. ✅ Update user documentation if needed

---

## 📞 Support

If you encounter any issues during deployment, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Web server logs
3. Browser console for JavaScript errors
4. Network tab for failed requests

---

**Deployment Date:** January 9, 2026  
**Feature:** Auto-Logout Security Feature  
**Version:** 1.0.0

