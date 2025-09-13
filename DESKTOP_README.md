# 🖥️ ConCure Desktop Application

A cross-platform desktop version of the ConCure Clinic Management System built with Electron, providing native desktop experience for Windows and macOS.

## 🌟 Features

### Desktop-Specific Features
- **Native Desktop Experience**: Full-screen application with native window controls
- **Offline Capability**: Works without internet connection (local SQLite database)
- **System Integration**: Native notifications, file dialogs, and system tray
- **Auto-Start PHP Server**: Automatically manages Laravel backend server
- **Cross-Platform**: Runs on Windows, macOS, and Linux
- **Auto-Updates**: Built-in update mechanism (future feature)

### Core ConCure Features
- Complete clinic management system
- Patient records and medical history
- Prescription and lab request management
- Financial tracking and reporting
- Food composition database
- Multi-language support (English, Arabic, Kurdish)
- Role-based access control

## 📋 System Requirements

### Minimum Requirements
- **Operating System**: Windows 10+ / macOS 10.14+ / Ubuntu 18.04+
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 2GB free space
- **PHP**: 8.1 or higher (automatically detected)

### Recommended Requirements
- **RAM**: 8GB or more
- **Storage**: 5GB free space for data and backups
- **Display**: 1920x1080 or higher resolution

## 🚀 Installation

### Option 1: Download Pre-built Application
1. Download the latest release for your platform:
   - **Windows**: `ConCure-Setup-1.0.0.exe`
   - **macOS**: `ConCure-1.0.0.dmg`
   - **Linux**: `ConCure-1.0.0.AppImage`

2. Install and run the application
3. The application will automatically set up the database and start the server

### Option 2: Build from Source

#### Prerequisites
```bash
# Install Node.js (16+ required)
# Install PHP 8.1+
# Install Composer
```

#### Build Steps
```bash
# Clone the repository
git clone https://github.com/your-repo/concure-clinic.git
cd concure-clinic

# Install dependencies
npm install
composer install

# Build the desktop application
npm run build-desktop

# Create distributables
npm run dist          # All platforms
npm run dist-mac      # macOS only
npm run dist-win      # Windows only
```

## 🎮 Usage

### Starting the Application

#### Development Mode
```bash
npm run electron-dev
```

#### Production Mode
```bash
npm run electron
```

### First Time Setup
1. Launch the application
2. The system will automatically:
   - Create the SQLite database
   - Run initial migrations
   - Set up default user accounts
3. Login with default credentials:
   - **Admin**: `admin` / `admin123`
   - **Doctor**: `doctor` / `doctor123`

### Key Features

#### Desktop Menu
- **File**: Database backup/restore, export data
- **Edit**: Standard editing operations
- **View**: Zoom controls, full-screen mode
- **Window**: Window management
- **Help**: Documentation and support

#### Keyboard Shortcuts
- `Ctrl/Cmd + R`: Reload application
- `Ctrl/Cmd + Shift + I`: Open developer tools
- `Ctrl/Cmd + M`: Minimize window
- `Ctrl/Cmd + Q`: Quit application
- `F11`: Toggle full-screen

#### System Tray (Future Feature)
- Quick access to common functions
- Background operation
- Notification management

## 🔧 Configuration

### Environment Configuration
The desktop app uses `.env.production` for production settings:

```env
APP_NAME="ConCure Clinic Management"
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=database/concure.sqlite
CONCURE_COMPANY_NAME="Your Clinic Name"
CONCURE_PRIMARY_COLOR="#008080"
```

### Database Location
- **Windows**: `%APPDATA%/ConCure/database/`
- **macOS**: `~/Library/Application Support/ConCure/database/`
- **Linux**: `~/.config/ConCure/database/`

### Backup and Restore
- **Backup**: File → Backup Database
- **Restore**: File → Restore Database
- **Auto-backup**: Enabled by default (daily)

## 🛠️ Development

### Project Structure
```
electron/
├── main.js              # Main Electron process
├── preload.js           # Preload script for security
├── php-server.js        # PHP server management
├── ipc-handlers.js      # Inter-process communication
└── assets/              # Application icons

scripts/
├── build-desktop.js     # Build preparation script
└── start-desktop.js     # Development startup script
```

### Development Commands
```bash
# Start development server
npm run electron-dev

# Build frontend assets
npm run build

# Test Electron app
npm run electron

# Build for distribution
npm run dist

# Platform-specific builds
npm run dist-mac
npm run dist-win
npm run dist-linux
```

### Debugging
- Enable developer tools in development mode
- Check console for PHP server logs
- Use `console.log()` in renderer process
- Use `console.log()` in main process for Electron debugging

## 📦 Building and Distribution

### Build Configuration
The build process is configured in `package.json` under the `build` section:

```json
{
  "build": {
    "appId": "com.connectpure.concure",
    "productName": "ConCure Clinic Management",
    "directories": {
      "output": "dist-electron"
    }
  }
}
```

### Creating Installers
```bash
# Build all platforms (requires platform-specific tools)
npm run dist

# Windows (requires Windows or Wine)
npm run dist-win

# macOS (requires macOS)
npm run dist-mac

# Linux
npm run dist-linux
```

### Code Signing (Production)
For production releases, configure code signing:

```json
{
  "build": {
    "win": {
      "certificateFile": "path/to/certificate.p12",
      "certificatePassword": "password"
    },
    "mac": {
      "identity": "Developer ID Application: Your Name"
    }
  }
}
```

## 🔒 Security

### Security Features
- **Context Isolation**: Renderer process is isolated from Node.js
- **Preload Scripts**: Secure communication between processes
- **No Node Integration**: Renderer process cannot access Node.js directly
- **Content Security Policy**: Prevents XSS attacks
- **Secure Defaults**: All external links open in default browser

### Data Security
- Local SQLite database with file-level encryption option
- Secure file uploads with type validation
- Role-based access control
- Audit logging for all user actions

## 🐛 Troubleshooting

### Common Issues

#### PHP Not Found
```
Error: PHP not found. Please install PHP to run ConCure.
```
**Solution**: Install PHP 8.1+ and ensure it's in your system PATH.

#### Port Already in Use
```
Error: Port 8003 is already in use
```
**Solution**: The app will automatically find an available port. Check the console for the actual port being used.

#### Database Locked
```
Error: Database is locked
```
**Solution**: Close any other instances of the application and try again.

#### Permission Denied
```
Error: Permission denied accessing database
```
**Solution**: Ensure the application has write permissions to the database directory.

### Getting Help
- Check the console for error messages
- Enable debug mode in development
- Check the logs in the application data directory
- Contact support at support@connectpure.com

## 📄 License

This project is proprietary software developed by Connect Pure. See the [LICENSE](LICENSE) file for details.

## 🤝 Support

- **Email**: support@connectpure.com
- **Documentation**: [GitHub Wiki](https://github.com/your-repo/concure-clinic/wiki)
- **Issues**: [GitHub Issues](https://github.com/your-repo/concure-clinic/issues)

---

**ConCure Desktop** - Bringing clinic management to your desktop
Made with ❤️ by [Connect Pure](https://connectpure.com)
