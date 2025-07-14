@echo off
setlocal enabledelayedexpansion

REM ConCure Clinic Management System Installation Script for Windows
REM This script will set up the complete ConCure system on Windows

title ConCure Installation

echo.
echo ========================================================
echo   ConCure Clinic Management System - Installation
echo ========================================================
echo.
echo This script will install and configure ConCure for you.
echo.

REM Step 1: Check system requirements
echo [Step 1] Checking System Requirements...
echo.

REM Check PHP
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP is not installed or not in PATH.
    echo Please install PHP 8.1+ and add it to your PATH.
    echo Visit: https://www.php.net/downloads
    pause
    exit /b 1
) else (
    echo [OK] PHP is installed
    php -r "echo 'PHP Version: ' . PHP_VERSION . PHP_EOL;"
)

REM Check Composer
composer --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Composer is not installed or not in PATH.
    echo Please install Composer and add it to your PATH.
    echo Visit: https://getcomposer.org/download/
    pause
    exit /b 1
) else (
    echo [OK] Composer is installed
    composer --version
)

REM Check Node.js (optional)
node --version >nul 2>&1
if errorlevel 1 (
    echo [WARNING] Node.js is not installed. Frontend assets won't be compiled.
    echo You can install Node.js later from: https://nodejs.org/
) else (
    echo [OK] Node.js is installed
    node --version
)

echo.
pause

REM Step 2: Install PHP dependencies
echo [Step 2] Installing PHP Dependencies...
echo.

if not exist "composer.json" (
    echo [ERROR] composer.json not found. Are you in the correct directory?
    pause
    exit /b 1
)

echo Installing PHP dependencies...
composer install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo [ERROR] Failed to install PHP dependencies.
    pause
    exit /b 1
)
echo [OK] PHP dependencies installed

echo.

REM Step 3: Install additional packages
echo [Step 3] Installing Additional Packages...
echo.

echo Installing PDF generation package...
composer require barryvdh/laravel-dompdf --no-interaction
if errorlevel 1 (
    echo [ERROR] Failed to install PDF package.
    pause
    exit /b 1
)

echo Installing image processing package...
composer require intervention/image --no-interaction
if errorlevel 1 (
    echo [ERROR] Failed to install image package.
    pause
    exit /b 1
)

echo [OK] Additional packages installed

echo.

REM Step 4: Environment setup
echo [Step 4] Environment Configuration...
echo.

if not exist ".env" (
    echo Creating .env file from .env.example...
    copy ".env.example" ".env" >nul
    echo [OK] .env file created
) else (
    echo [WARNING] .env file already exists. Skipping...
)

REM Generate application key
echo Generating application key...
php artisan key:generate --force
if errorlevel 1 (
    echo [ERROR] Failed to generate application key.
    pause
    exit /b 1
)
echo [OK] Application key generated

echo.

REM Step 5: Database setup
echo [Step 5] Database Setup...
echo.

REM Create database directory
if not exist "database" (
    mkdir database
    echo [OK] Database directory created
)

REM Create SQLite database file
set DB_FILE=database\concure.sqlite
if not exist "%DB_FILE%" (
    type nul > "%DB_FILE%"
    echo [OK] SQLite database file created
) else (
    echo [WARNING] Database file already exists
)

REM Update .env with correct database path
set CURRENT_DIR=%CD%
set DB_PATH=%CURRENT_DIR%\%DB_FILE%
set DB_PATH=!DB_PATH:\=/!

REM Update .env file with correct database path
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=!DB_PATH!' | Set-Content .env"
echo [OK] Database path updated in .env

echo.

REM Step 6: Run ConCure setup
echo [Step 6] ConCure System Setup...
echo.

echo Running ConCure setup command...
php artisan concure:setup
if errorlevel 1 (
    echo [ERROR] ConCure setup failed.
    pause
    exit /b 1
)

echo.

REM Step 7: Frontend assets (if Node.js is available)
node --version >nul 2>&1
if not errorlevel 1 (
    echo [Step 7] Frontend Assets...
    echo.
    
    echo Installing Node.js dependencies...
    npm install
    if errorlevel 1 (
        echo [WARNING] Failed to install Node.js dependencies.
    ) else (
        echo Building frontend assets...
        npm run build
        if errorlevel 1 (
            echo [WARNING] Failed to build frontend assets.
        ) else (
            echo [OK] Frontend assets compiled
        )
    )
    echo.
)

REM Step 8: Final checks
echo [Step 8] Final System Check...
echo.

REM Test database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection: OK';" >nul 2>&1
if errorlevel 1 (
    echo [WARNING] Database connection test failed
) else (
    echo [OK] Database connection test passed
)

REM Check if storage link exists
if exist "public\storage" (
    echo [OK] Storage link exists
) else (
    echo [WARNING] Storage link missing (this is normal for fresh installations)
)

echo.

REM Installation complete
echo ========================================================
echo   🎉 Installation Complete!
echo ========================================================
echo.
echo ConCure Clinic Management System has been successfully installed!
echo.
echo 📋 Next Steps:
echo   1. Start the development server:
echo      php artisan serve
echo.
echo   2. Open your browser and visit:
echo      http://localhost:8000
echo.
echo   3. Login with these default credentials:
echo.
echo 👤 Default Login Credentials:
echo   • Program Owner: program_owner / ConCure2024!
echo   • Admin:         admin / admin123
echo   • Doctor:        doctor / doctor123
echo   • Assistant:     assistant / assistant123
echo   • Nurse:         nurse / nurse123
echo   • Accountant:    accountant / accountant123
echo.
echo ⚠️  Important: Change default passwords after first login!
echo.
echo 🌟 Features Available:
echo   • Patient Management with Medical Records
echo   • Prescription ^& Lab Request System
echo   • Diet Planning with Nutrition Database
echo   • Financial Management (Invoices ^& Expenses)
echo   • Advertisement Management
echo   • Multi-language Support (English, Arabic, Kurdish)
echo   • Role-based Access Control
echo   • Audit Logging ^& Activity Monitoring
echo.
echo 🚀 Ready to manage your clinic efficiently!
echo.

pause
