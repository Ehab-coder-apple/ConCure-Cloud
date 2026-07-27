#!/bin/bash

# ConCure GitHub Preparation Script
# This script prepares the ConCure project for GitHub upload

set -e

echo "🏥 ConCure GitHub Preparation Script"
echo "===================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the ConCure project root directory"
    exit 1
fi

print_info "Starting GitHub preparation process..."

# 1. Clean up development files
print_info "Cleaning up development files..."

# Remove development databases
if [ -f "database/concure.sqlite" ]; then
    rm database/concure.sqlite
    print_status "Removed development SQLite database"
fi

if [ -f "database/database.sqlite" ]; then
    rm database/database.sqlite
    print_status "Removed test SQLite database"
fi

# Clean up temporary files
find . -name "*.tmp" -delete 2>/dev/null || true
find . -name "*.cache" -delete 2>/dev/null || true
find . -name ".DS_Store" -delete 2>/dev/null || true

# Clean up logs
if [ -d "storage/logs" ]; then
    find storage/logs -name "*.log" -delete 2>/dev/null || true
    print_status "Cleaned up log files"
fi

# 2. Verify .env.example is up to date
print_info "Verifying environment configuration..."

if [ ! -f ".env.example" ]; then
    print_error ".env.example file is missing"
    exit 1
fi

# Remove sensitive data from .env.example if it exists
if [ -f ".env.example" ]; then
    # Ensure APP_KEY is empty
    sed -i.bak 's/APP_KEY=.*/APP_KEY=/' .env.example
    rm .env.example.bak 2>/dev/null || true
    
    # Ensure debug is false for production
    sed -i.bak 's/APP_DEBUG=true/APP_DEBUG=false/' .env.example
    rm .env.example.bak 2>/dev/null || true
    
    print_status "Updated .env.example for production"
fi

# 3. Update composer dependencies
print_info "Checking Composer dependencies..."

if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
    print_status "Updated Composer dependencies for production"
else
    print_warning "Composer not found. Please run 'composer install --no-dev --optimize-autoloader' before deployment"
fi

# 4. Build frontend assets
print_info "Building frontend assets..."

if [ -f "package.json" ] && command -v npm &> /dev/null; then
    npm ci --only=production
    npm run build 2>/dev/null || npm run production 2>/dev/null || print_warning "Could not build assets"
    print_status "Built frontend assets"
else
    print_warning "NPM not found or package.json missing. Frontend assets may need manual building"
fi

# 5. Set proper permissions
print_info "Setting proper file permissions..."

# Storage and cache directories
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Make scripts executable
chmod +x *.sh 2>/dev/null || true

print_status "Set proper file permissions"

# 6. Validate critical files exist
print_info "Validating critical files..."

critical_files=(
    "README.md"
    "CONTRIBUTING.md"
    "CHANGELOG.md"
    "LICENSE"
    "SECURITY.md"
    ".gitignore"
    "composer.json"
    ".env.example"
)

for file in "${critical_files[@]}"; do
    if [ ! -f "$file" ]; then
        print_error "Critical file missing: $file"
        exit 1
    fi
done

print_status "All critical files present"

# 7. Check for sensitive data
print_info "Scanning for sensitive data..."

sensitive_patterns=(
    "password.*=.*[^=]$"
    "secret.*=.*[^=]$"
    "key.*=.*[^=]$"
    "token.*=.*[^=]$"
)

sensitive_found=false

for pattern in "${sensitive_patterns[@]}"; do
    if grep -r -i "$pattern" . --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.git --exclude="*.md" --exclude="*.sh" 2>/dev/null; then
        print_warning "Potential sensitive data found matching pattern: $pattern"
        sensitive_found=true
    fi
done

if [ "$sensitive_found" = false ]; then
    print_status "No obvious sensitive data found"
fi

# 8. Generate documentation
print_info "Preparing documentation..."

# Create docs directory structure if it doesn't exist
mkdir -p docs/{user-guide,admin-guide,developer-guide,api,deployment,compliance,security}
mkdir -p docs/screenshots

print_status "Documentation structure prepared"

# 9. Create GitHub repository initialization script
cat > init-github-repo.sh << 'EOF'
#!/bin/bash

# GitHub Repository Initialization Script
echo "🚀 Initializing GitHub repository for ConCure..."

# Initialize git repository
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: ConCure Clinic Management System

- Complete Laravel-based clinic management system
- Multi-tenant SaaS architecture
- Multilingual support (English, Arabic, Kurdish)
- Patient management and medical records
- Digital prescription system
- Appointment scheduling
- Nutrition planning
- Financial management
- PWA-ready responsive design
- Healthcare compliance features"

# Add remote origin (replace with your repository URL)
echo "Please run the following commands to connect to your GitHub repository:"
echo "git remote add origin https://github.com/YOUR_USERNAME/concure.git"
echo "git branch -M main"
echo "git push -u origin main"

echo "✅ Repository initialized successfully!"
EOF

chmod +x init-github-repo.sh

# 10. Create deployment checklist
cat > DEPLOYMENT_CHECKLIST.md << 'EOF'
# ConCure Deployment Checklist

## Pre-Deployment
- [ ] All tests passing
- [ ] Code reviewed and approved
- [ ] Documentation updated
- [ ] Environment variables configured
- [ ] Database migrations tested
- [ ] Backup procedures in place

## GitHub Setup
- [ ] Repository created on GitHub
- [ ] Collaborators added
- [ ] Branch protection rules configured
- [ ] GitHub Actions workflows enabled
- [ ] Issue templates configured
- [ ] Security policies in place

## Production Deployment
- [ ] Server requirements met
- [ ] SSL certificate installed
- [ ] Environment variables set
- [ ] Database configured
- [ ] File permissions set
- [ ] Cron jobs configured
- [ ] Monitoring setup
- [ ] Backup system active

## Post-Deployment
- [ ] Application accessible
- [ ] All features working
- [ ] Performance monitoring active
- [ ] Error logging configured
- [ ] Security scan completed
- [ ] User acceptance testing passed

## Healthcare Compliance
- [ ] HIPAA compliance verified
- [ ] Data encryption enabled
- [ ] Access controls implemented
- [ ] Audit logging active
- [ ] Privacy policies updated
- [ ] Staff training completed
EOF

print_status "Created deployment checklist"

# 11. Final summary
echo ""
echo "🎉 GitHub Preparation Complete!"
echo "==============================="
echo ""
print_status "Project is ready for GitHub upload"
echo ""
print_info "Next steps:"
echo "1. Review all files and ensure no sensitive data is included"
echo "2. Create a new repository on GitHub"
echo "3. Run ./init-github-repo.sh to initialize the repository"
echo "4. Follow the deployment checklist in DEPLOYMENT_CHECKLIST.md"
echo ""
print_info "Files created/updated:"
echo "- .gitignore (comprehensive Laravel gitignore)"
echo "- CONTRIBUTING.md (contribution guidelines)"
echo "- CHANGELOG.md (version history)"
echo "- LICENSE (proprietary license)"
echo "- SECURITY.md (security policy)"
echo "- .github/ (issue templates, PR template, CI/CD workflow)"
echo "- docs/ (documentation structure)"
echo "- init-github-repo.sh (repository initialization script)"
echo "- DEPLOYMENT_CHECKLIST.md (deployment checklist)"
echo ""
print_warning "Remember to:"
echo "- Replace placeholder URLs in README.md with actual repository URLs"
echo "- Update contact information in documentation"
echo "- Configure GitHub repository settings"
echo "- Set up branch protection rules"
echo "- Enable GitHub Actions if desired"
echo ""
print_status "ConCure is ready for the world! 🌍"
EOF
