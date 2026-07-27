#!/bin/bash

###############################################################################
# ConCure Cloud - Auto-Logout Feature Deployment Script
# This script deploys the auto-logout feature to the production server
###############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration - UPDATE THESE VALUES
SERVER_USER="your-username"           # SSH username
SERVER_HOST="your-server-ip"          # Server IP or hostname
SERVER_PATH="/var/www/concure-cloud"  # Project path on server
SSH_KEY_PATH="~/.ssh/id_rsa"          # SSH key path (optional)

###############################################################################
# Functions
###############################################################################

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

###############################################################################
# Pre-deployment checks
###############################################################################

print_header "Pre-Deployment Checks"

# Check if git is clean
if [[ -n $(git status -s) ]]; then
    print_warning "You have uncommitted changes. Please commit or stash them first."
    exit 1
fi

print_success "Local repository is clean"

# Check if we're on main branch
CURRENT_BRANCH=$(git branch --show-current)
if [[ "$CURRENT_BRANCH" != "main" ]]; then
    print_warning "You're not on the main branch. Current branch: $CURRENT_BRANCH"
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

print_success "Branch check passed"

###############################################################################
# Deploy to server
###############################################################################

print_header "Deploying to Production Server"

# SSH command builder
if [[ -f "$SSH_KEY_PATH" ]]; then
    SSH_CMD="ssh -i $SSH_KEY_PATH $SERVER_USER@$SERVER_HOST"
else
    SSH_CMD="ssh $SERVER_USER@$SERVER_HOST"
fi

print_info "Connecting to $SERVER_HOST..."

# Execute deployment commands on server
$SSH_CMD << 'ENDSSH'
    # Navigate to project directory
    cd /var/www/concure-cloud || exit 1
    
    echo "📦 Pulling latest changes from GitHub..."
    git pull origin main
    
    if [ $? -ne 0 ]; then
        echo "❌ Git pull failed!"
        exit 1
    fi
    
    echo "✅ Code updated successfully"
    
    echo "🗄️  Running database migrations..."
    php artisan migrate --force
    
    if [ $? -ne 0 ]; then
        echo "❌ Migration failed!"
        exit 1
    fi
    
    echo "✅ Migrations completed"
    
    echo "🧹 Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    echo "⚡ Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "✅ Cache optimization completed"
    
    echo "🔄 Restarting services..."
    
    # Restart PHP-FPM (adjust version as needed)
    if command -v systemctl &> /dev/null; then
        sudo systemctl restart php8.2-fpm 2>/dev/null || sudo systemctl restart php8.1-fpm 2>/dev/null || sudo systemctl restart php-fpm 2>/dev/null
        echo "✅ PHP-FPM restarted"
    fi
    
    # Restart web server
    if systemctl is-active --quiet nginx; then
        sudo systemctl restart nginx
        echo "✅ Nginx restarted"
    elif systemctl is-active --quiet apache2; then
        sudo systemctl restart apache2
        echo "✅ Apache restarted"
    fi
    
    echo "✅ Deployment completed successfully!"
    echo ""
    echo "📋 Post-Deployment Checklist:"
    echo "  1. Test auto-logout feature"
    echo "  2. Check Settings > System Settings for Session Duration"
    echo "  3. Verify keep-alive requests in browser console"
    echo "  4. Check logs: tail -f storage/logs/laravel.log"
    
ENDSSH

if [ $? -eq 0 ]; then
    print_success "Deployment completed successfully!"
else
    print_error "Deployment failed! Please check the error messages above."
    exit 1
fi

print_header "Deployment Summary"
print_info "Server: $SERVER_HOST"
print_info "Path: $SERVER_PATH"
print_info "Branch: $CURRENT_BRANCH"
print_success "All done! 🎉"

