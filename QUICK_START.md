# 🚀 ConCure Quick Start Guide

## **Method 1: Homebrew Installation (Recommended)**

Open Terminal and copy-paste these commands one by one:

### Step 1: Install Homebrew
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```
*You'll need to enter your password when prompted*

### Step 2: Add Homebrew to PATH
```bash
echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
source ~/.zprofile
```

### Step 3: Install PHP and Composer
```bash
brew install php composer
```

### Step 4: Verify Installation
```bash
php --version
composer --version
```

### Step 5: Navigate to ConCure
```bash
cd /Users/ehabkhorshed/Documents/augment-projects/Concure
```

### Step 6: Run ConCure Setup
```bash
./run-concure.sh
```

### Step 7: Open Browser
Go to: http://localhost:8000

---

## **Method 2: XAMPP Installation (GUI Alternative)**

### Step 1: Download XAMPP
- Go to: https://www.apachefriends.org/download.html
- Download XAMPP for macOS
- Install the .dmg file

### Step 2: Add PHP to PATH
```bash
echo 'export PATH="/Applications/XAMPP/xamppfiles/bin:$PATH"' >> ~/.zprofile
source ~/.zprofile
```

### Step 3: Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 4: Navigate and Run
```bash
cd /Users/ehabkhorshed/Documents/augment-projects/Concure
./run-concure.sh
```

---

## **Method 3: Docker (If Docker is Available)**

### Step 1: Check if Docker is Installed
```bash
docker --version
```

### Step 2: If Docker is Available, Run ConCure
```bash
cd /Users/ehabkhorshed/Documents/augment-projects/Concure
docker-compose up --build
```

### Step 3: Open Browser
Go to: http://localhost:8000

---

## **🔑 Login Credentials**

Once ConCure is running, use these credentials:

| Username | Password | Role |
|----------|----------|------|
| `program_owner` | `ConCure2024!` | Full Access |
| `admin` | `admin123` | Administrator |
| `doctor` | `doctor123` | Doctor |

---

## **🆘 Troubleshooting**

### If you get "Permission Denied":
```bash
chmod +x run-concure.sh
```

### If database errors occur:
```bash
rm database/concure.sqlite
touch database/concure.sqlite
chmod 664 database/concure.sqlite
```

### If port 8000 is busy:
```bash
php artisan serve --port=8080
```
Then go to: http://localhost:8080

---

## **✅ What You'll See**

- **Login Page**: Beautiful teal-themed interface
- **Dashboard**: Modern medical management interface
- **Patient Management**: Complete patient records
- **Prescriptions**: Digital prescription system
- **Finance**: Invoice and expense management
- **Food Database**: Nutrition information
- **Multi-language**: English, Arabic, Kurdish support

---

## **🎯 Quick Commands Summary**

```bash
# Install Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Setup PATH
echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile && source ~/.zprofile

# Install PHP & Composer
brew install php composer

# Go to ConCure
cd /Users/ehabkhorshed/Documents/augment-projects/Concure

# Run ConCure
./run-concure.sh
```

**That's it! ConCure will be running at http://localhost:8000** 🎉
