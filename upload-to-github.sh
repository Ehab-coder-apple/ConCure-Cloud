#!/bin/bash

# ConCure GitHub Upload Script
echo "🚀 Starting ConCure GitHub upload..."

# Fix git ownership issue
echo "🔧 Fixing git ownership..."
git config --global --add safe.directory /home/1495414.cloudwaysapps.com/wuwwdrhjxy/public_html

# Remove existing git and reinitialize
echo "🔄 Reinitializing git repository..."
rm -rf .git
git init

# Set git user
echo "👤 Setting git user..."
git config user.email "ehab@concure.com"
git config user.name "Ehab Khorshed"

# Create .gitignore
echo "📝 Creating .gitignore..."
cat > .gitignore << 'EOF'
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
/database/*.sqlite
/database/*.sqlite-journal
/storage/logs/*.log
/bootstrap/cache/*.php
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/storage/app/public/uploads/*
/storage/app/public/logos/*
/storage/app/public/medicines/*
/storage/app/public/foods/*
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
Thumbs.db
*.tmp
*.temp
*.swp
*.swo
*~
EOF

# Create README.md
echo "📋 Creating README.md..."
cat > README.md << 'EOF'
# ConCure - Clinic Management System

A comprehensive multi-tenant SaaS clinic management system built with Laravel.

## 🌟 Features

### 🏥 **Multi-Tenant Architecture**
- Master dashboard for program owners
- Individual clinic dashboards  
- Role-based access control
- Subscription management

### 👥 **Patient Management**
- Patient registration and profiles
- Medical history tracking
- Appointment scheduling
- WhatsApp integration

### 💊 **Medical Services**
- Prescription management with medicine inventory
- Nutrition planning (7-day meal plans)
- Lab request management
- Print-friendly documents

### 🌍 **Multilingual Support**
- English, Arabic, Kurdish languages
- RTL text direction support
- Multilingual food database

### 📱 **Modern Interface**
- PWA-ready responsive design
- Teal color theme
- Sidebar navigation
- Mobile-optimized

## 🚀 **Live Demo**

- **Application**: https://www.concure.app/
- **Master Control**: https://www.concure.app/master-control

## 🛠️ **Technology Stack**

- **Backend**: Laravel 10
- **Frontend**: Blade Templates, Alpine.js
- **Database**: MySQL
- **Styling**: Tailwind CSS
- **Hosting**: Cloudways

## 📄 **License**

This project is proprietary software. All rights reserved.

## 👨‍💻 **Developer**

Developed by Ehab Khorshed
EOF

# Add files to git
echo "➕ Adding files to git..."
git add .

# Create commit
echo "💾 Creating commit..."
git commit -m "Initial commit: ConCure Clinic Management System

- Multi-tenant SaaS architecture with master dashboard
- Clinic management with role-based access control
- Patient management and medical records
- Prescription management with medicine inventory
- Nutrition planning system with 7-day meal plans
- Appointment scheduling and management
- Lab request management system
- Multilingual support (English/Arabic/Kurdish)
- PWA-ready responsive design with teal theme
- MySQL database compatibility fixes applied"

# Add remote
echo "🔗 Adding GitHub remote..."
git remote add origin https://github.com/Ehab-coder-apple/ConCure.git

# Set main branch
echo "🌿 Setting main branch..."
git branch -M main

# Push to GitHub
echo "🚀 Pushing to GitHub..."
echo "⚠️  You will be prompted for GitHub credentials:"
echo "   Username: Ehab-coder-apple"
echo "   Password: Use your Personal Access Token (not GitHub password)"
echo ""
git push -u origin main

echo "✅ Upload complete!"
echo "🌐 Visit: https://github.com/Ehab-coder-apple/ConCure"
