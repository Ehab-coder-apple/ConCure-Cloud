#!/bin/bash

# Security Fix Deployment Script
# Removes backdoor login routes and restores audit logging
# Date: January 30, 2026

echo "=========================================="
echo "Security Fix: Login Audit Logging"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Please run this script from the Laravel root directory.${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Backing up current routes file...${NC}"
cp routes/web.php routes/web.php.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✓ Backup created${NC}"
echo ""

echo -e "${YELLOW}Step 2: Clearing route cache...${NC}"
php artisan route:clear
echo -e "${GREEN}✓ Route cache cleared${NC}"
echo ""

echo -e "${YELLOW}Step 3: Clearing application cache...${NC}"
php artisan cache:clear
php artisan config:clear
echo -e "${GREEN}✓ Application cache cleared${NC}"
echo ""

echo -e "${YELLOW}Step 4: Rebuilding route cache...${NC}"
php artisan route:cache
echo -e "${GREEN}✓ Route cache rebuilt${NC}"
echo ""

echo -e "${YELLOW}Step 5: Verifying backdoor routes are removed...${NC}"
BACKDOOR_ROUTES=$(php artisan route:list | grep -E "login-as|dev/login" | wc -l)

if [ "$BACKDOOR_ROUTES" -eq 0 ]; then
    echo -e "${GREEN}✓ All backdoor routes successfully removed${NC}"
else
    echo -e "${RED}✗ Warning: Found $BACKDOOR_ROUTES backdoor routes still active${NC}"
    echo "Please review the routes manually:"
    php artisan route:list | grep -E "login-as|dev/login"
fi
echo ""

echo -e "${YELLOW}Step 6: Testing login route...${NC}"
LOGIN_ROUTE=$(php artisan route:list | grep "POST.*login" | grep -v "master" | wc -l)

if [ "$LOGIN_ROUTE" -gt 0 ]; then
    echo -e "${GREEN}✓ Normal login route is active${NC}"
else
    echo -e "${RED}✗ Warning: Normal login route not found${NC}"
fi
echo ""

echo "=========================================="
echo -e "${GREEN}Deployment Complete!${NC}"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Test login functionality via /login"
echo "2. Verify audit logs are being created"
echo "3. Check that last_login_at is being updated"
echo ""
echo "To verify audit logging, run:"
echo "  php artisan tinker"
echo "  > App\\Models\\AuditLog::where('action', 'login')->orderBy('performed_at', 'desc')->first();"
echo ""
echo "Backup file location:"
ls -lh routes/web.php.backup.* 2>/dev/null | tail -1
echo ""

