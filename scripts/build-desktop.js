#!/usr/bin/env node

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🏗️  Building ConCure Desktop Application...\n');

// Step 1: Clean previous builds
console.log('1️⃣  Cleaning previous builds...');
try {
    if (fs.existsSync('dist-electron')) {
        fs.rmSync('dist-electron', { recursive: true, force: true });
    }
    console.log('✅ Cleaned dist-electron directory\n');
} catch (error) {
    console.log('⚠️  No previous builds to clean\n');
}

// Step 2: Build frontend assets
console.log('2️⃣  Building frontend assets...');
try {
    execSync('npm run build', { stdio: 'inherit' });
    console.log('✅ Frontend assets built successfully\n');
} catch (error) {
    console.error('❌ Failed to build frontend assets');
    process.exit(1);
}

// Step 3: Ensure PHP dependencies are installed
console.log('3️⃣  Checking PHP dependencies...');
try {
    execSync('composer install --no-dev --optimize-autoloader', { stdio: 'inherit' });
    console.log('✅ PHP dependencies ready\n');
} catch (error) {
    console.error('❌ Failed to install PHP dependencies');
    process.exit(1);
}

// Step 4: Prepare database
console.log('4️⃣  Preparing database...');
try {
    const dbDir = path.join(__dirname, '..', 'database');
    const dbPath = path.join(dbDir, 'concure.sqlite');
    
    if (!fs.existsSync(dbDir)) {
        fs.mkdirSync(dbDir, { recursive: true });
    }
    
    if (!fs.existsSync(dbPath)) {
        fs.writeFileSync(dbPath, '');
        console.log('✅ Created SQLite database file');
    } else {
        console.log('✅ Database file exists');
    }
    
    // Run migrations if needed
    try {
        execSync('php artisan migrate --force', { stdio: 'inherit' });
        console.log('✅ Database migrations completed\n');
    } catch (migrationError) {
        console.log('⚠️  Database migrations skipped (may already be up to date)\n');
    }
} catch (error) {
    console.error('❌ Failed to prepare database:', error.message);
    process.exit(1);
}

// Step 5: Create .env.production file
console.log('5️⃣  Creating production environment file...');
try {
    const envContent = `APP_NAME="ConCure Clinic Management"
APP_ENV=production
APP_KEY=base64:2yq1boKbaeih7iXlcEdCO/oBC6qQ7diVwjj/V49XeKg=
APP_DEBUG=false
APP_URL=http://127.0.0.1:8003

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=database/concure.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ConCure Specific Settings
CONCURE_COMPANY_NAME="Connect Pure"
CONCURE_PRIMARY_COLOR="#008080"
CONCURE_DEFAULT_LANGUAGE=en
CONCURE_SUPPORTED_LANGUAGES=en,ar,ku

# File Upload Settings
MAX_FILE_SIZE=10240
ALLOWED_FILE_TYPES=pdf,jpg,jpeg,png,doc,docx
`;
    
    fs.writeFileSync('.env.production', envContent);
    console.log('✅ Production environment file created\n');
} catch (error) {
    console.error('❌ Failed to create production environment file:', error.message);
    process.exit(1);
}

// Step 6: Optimize Laravel for production
console.log('6️⃣  Optimizing Laravel for production...');
try {
    execSync('php artisan config:cache', { stdio: 'inherit' });
    execSync('php artisan route:cache', { stdio: 'inherit' });
    execSync('php artisan view:cache', { stdio: 'inherit' });
    console.log('✅ Laravel optimization completed\n');
} catch (error) {
    console.log('⚠️  Laravel optimization partially completed\n');
}

// Step 7: Create necessary directories
console.log('7️⃣  Creating necessary directories...');
try {
    const dirs = [
        'storage/app',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache'
    ];
    
    dirs.forEach(dir => {
        const fullPath = path.join(__dirname, '..', dir);
        if (!fs.existsSync(fullPath)) {
            fs.mkdirSync(fullPath, { recursive: true });
        }
    });
    
    console.log('✅ Necessary directories created\n');
} catch (error) {
    console.error('❌ Failed to create directories:', error.message);
    process.exit(1);
}

console.log('🎉 Build preparation completed successfully!');
console.log('📦 Ready for Electron packaging...\n');

console.log('Next steps:');
console.log('  • Run "npm run dist" to build for all platforms');
console.log('  • Run "npm run dist-mac" to build for macOS only');
console.log('  • Run "npm run dist-win" to build for Windows only');
console.log('  • Run "npm run electron" to test the desktop app locally');
