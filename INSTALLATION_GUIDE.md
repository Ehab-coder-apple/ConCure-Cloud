# 🚀 ConCure Desktop Installation Guide

This guide will help you install and run the ConCure Clinic Management System as a desktop application on Windows and macOS.

## 📋 Prerequisites

### Required Software
- **Node.js** 16.0 or higher ([Download](https://nodejs.org/))
- **PHP** 8.1 or higher ([Download](https://www.php.net/downloads))
- **Composer** ([Download](https://getcomposer.org/download/))

### System Requirements
- **Windows**: Windows 10 or higher
- **macOS**: macOS 10.14 (Mojave) or higher
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 2GB free space

## 🛠️ Installation Steps

### Step 1: Verify Prerequisites
Open a terminal/command prompt and verify installations:

```bash
# Check Node.js
node --version
# Should show v16.0.0 or higher

# Check PHP
php --version
# Should show PHP 8.1.0 or higher

# Check Composer
composer --version
# Should show Composer version
```

### Step 2: Install Dependencies

#### Install Node.js Dependencies
```bash
# Navigate to the ConCure directory
cd "Concure Cloud"

# Install Node.js packages
npm install

# If npm install fails due to network issues, try:
npm install --registry https://registry.npmjs.org/
# or
yarn install
```

#### Install PHP Dependencies
```bash
# Install PHP packages
composer install --no-dev --optimize-autoloader
```

### Step 3: Setup Database
```bash
# Create database and run migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed --force
```

### Step 4: Test the Application
```bash
# Run the test script
node test-desktop.js

# If all tests pass, start the desktop app
npm run electron
```

## 🎯 Quick Start Commands

### Development Mode
```bash
# Start with hot reload
npm run electron-dev
```

### Production Mode
```bash
# Start the desktop app
npm run electron
```

### Build for Distribution
```bash
# Build for all platforms
npm run dist

# Build for specific platforms
npm run dist-mac      # macOS only
npm run dist-win      # Windows only
npm run dist-linux    # Linux only
```

## 🔧 Troubleshooting

### Common Issues

#### 1. "PHP not found" Error
**Problem**: PHP is not in your system PATH
**Solution**:
- **Windows**: Add PHP to your PATH environment variable
- **macOS**: Install PHP via Homebrew: `brew install php`

#### 2. "npm install" Fails
**Problem**: Network connectivity or proxy issues
**Solution**:
```bash
# Try different registry
npm install --registry https://registry.npmjs.org/

# Or use yarn
npm install -g yarn
yarn install

# Or install manually
npm install electron --save-dev
npm install electron-builder --save-dev
```

#### 3. "Permission Denied" on macOS
**Problem**: macOS security restrictions
**Solution**:
```bash
# Give execute permissions
chmod +x launch-desktop.js
chmod +x scripts/*.js

# If app won't open, go to System Preferences > Security & Privacy
# and allow the app to run
```

#### 4. Database Connection Error
**Problem**: SQLite database issues
**Solution**:
```bash
# Recreate database
rm database/concure.sqlite
php artisan migrate --force
```

#### 5. Port Already in Use
**Problem**: Port 8003 is occupied
**Solution**: The app will automatically find an available port. Check the console for the actual port being used.

### Advanced Troubleshooting

#### Enable Debug Mode
```bash
# Set environment variable for detailed logging
export NODE_ENV=development
npm run electron
```

#### Check Logs
- **Windows**: `%APPDATA%/ConCure/logs/`
- **macOS**: `~/Library/Logs/ConCure/`
- **Linux**: `~/.config/ConCure/logs/`

#### Reset Application Data
```bash
# Backup your data first!
# Then remove application data:

# Windows
rmdir /s "%APPDATA%\ConCure"

# macOS
rm -rf ~/Library/Application\ Support/ConCure

# Linux
rm -rf ~/.config/ConCure
```

## 📱 Alternative Launch Methods

If npm dependencies fail to install, you can still test the app:

### Method 1: Direct Electron Launch
```bash
# If you have Electron globally installed
npx electron .
```

### Method 2: Use the Launcher Script
```bash
# Use the generated launcher
node launch-desktop.js
```

### Method 3: Manual PHP Server + Browser
```bash
# Start PHP server manually
php artisan serve --port=8003

# Then open http://localhost:8003 in your browser
```

## 🎨 Customization

### Change App Icon
1. Replace files in `electron/assets/`:
   - `icon.png` (512x512 PNG)
   - `icon.ico` (Windows icon)
   - `icon.icns` (macOS icon)

### Modify App Settings
Edit `package.json` build section:
```json
{
  "build": {
    "appId": "com.yourcompany.concure",
    "productName": "Your Clinic Name",
    "directories": {
      "output": "dist-electron"
    }
  }
}
```

### Database Configuration
Edit `.env` file:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/concure.sqlite
APP_URL=http://127.0.0.1:8003
```

## 🔒 Security Notes

- The desktop app runs locally and doesn't require internet connection
- Database is stored locally on your computer
- All data remains on your device
- Regular backups are recommended (File → Backup Database)

## 📞 Support

### Getting Help
- **Documentation**: See `DESKTOP_README.md`
- **Issues**: Check console for error messages
- **Email**: support@connectpure.com

### Reporting Bugs
When reporting issues, please include:
1. Operating system and version
2. Node.js and PHP versions
3. Error messages from console
4. Steps to reproduce the issue

## 🎉 Success!

Once installed, you'll have:
- ✅ Native desktop application
- ✅ System tray integration
- ✅ Offline functionality
- ✅ Local database
- ✅ Cross-platform compatibility

The ConCure Desktop App provides a complete clinic management solution that runs entirely on your computer, ensuring data privacy and offline access.

---

**Need help?** Contact us at support@connectpure.com or visit our documentation at [GitHub](https://github.com/your-repo/concure-clinic)
