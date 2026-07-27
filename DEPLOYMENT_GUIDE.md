# Custom Medicine Feature - Deployment Guide

## Pre-Deployment Checklist

### 1. Code Review
- [ ] All code reviewed by senior developer
- [ ] Security review completed
- [ ] Performance testing completed
- [ ] All tests passing
- [ ] Documentation reviewed

### 2. Environment Preparation
- [ ] Development environment tested
- [ ] Staging environment available
- [ ] Production database backup created
- [ ] Rollback plan documented
- [ ] Deployment window scheduled

### 3. Dependencies
- [ ] Node.js version verified (14+)
- [ ] React version verified (16+)
- [ ] Database version verified (MySQL 5.7+ or PostgreSQL 10+)
- [ ] Required npm packages installed
- [ ] lucide-react package available

## Deployment Steps

### Phase 1: Database Migration (30 minutes)

#### Step 1.1: Backup Production Database
```bash
# For MySQL
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# For PostgreSQL
pg_dump -U username database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```
- [ ] Backup completed
- [ ] Backup file verified
- [ ] Backup stored securely

#### Step 1.2: Test Migration on Staging
```bash
# Restore staging database from production backup
mysql -u username -p staging_db < production_backup.sql

# Run migration on staging
mysql -u username -p staging_db < database_migration_custom_medicines.sql

# Verify schema changes
mysql -u username -p staging_db -e "DESCRIBE prescription_medicines;"
```
- [ ] Migration runs without errors on staging
- [ ] New columns exist: is_custom, custom_medicine_name
- [ ] medicine_id is nullable
- [ ] Constraints are in place
- [ ] Existing data is intact

#### Step 1.3: Run Migration on Production
```bash
# Connect to production database
mysql -u username -p production_db < database_migration_custom_medicines.sql

# Verify changes
mysql -u username -p production_db -e "DESCRIBE prescription_medicines;"
mysql -u username -p production_db -e "SHOW CREATE TABLE prescription_medicines;"
```
- [ ] Migration completed successfully
- [ ] Schema changes verified
- [ ] No data loss
- [ ] Constraints working

### Phase 2: Backend Deployment (1 hour)

#### Step 2.1: Prepare Backend Files
```bash
# Create backup of existing files (if any)
cp controllers/prescriptionController.js controllers/prescriptionController.js.backup
cp validators/prescriptionValidation.js validators/prescriptionValidation.js.backup
cp routes/prescriptionRoutes.js routes/prescriptionRoutes.js.backup

# Copy new files
cp package/prescriptionController.js controllers/
cp package/prescriptionValidation.js validators/
cp package/prescriptionRoutes.js routes/
```
- [ ] Files backed up
- [ ] New files copied
- [ ] File permissions correct

#### Step 2.2: Update Application Configuration
```javascript
// In your main app.js or server.js

// Import routes
const prescriptionRoutes = require('./routes/prescriptionRoutes');

// Add routes
app.use('/api', prescriptionRoutes);

// Ensure authentication middleware is configured
// Ensure database connection is properly configured
```
- [ ] Routes imported
- [ ] Routes registered
- [ ] Dependencies configured

#### Step 2.3: Install Dependencies
```bash
# In your backend directory
npm install

# Verify no vulnerabilities
npm audit

# Fix if needed
npm audit fix
```
- [ ] Dependencies installed
- [ ] No critical vulnerabilities
- [ ] Package-lock.json updated

#### Step 2.4: Test Backend on Staging
```bash
# Start staging server
npm run start:staging

# Test endpoints with curl
curl -X GET "http://staging.example.com/api/medicines/search?q=test" \
  -H "Authorization: Bearer TEST_TOKEN"

curl -X POST "http://staging.example.com/api/prescriptions" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TEST_TOKEN" \
  -d '{
    "patient_id": 1,
    "doctor_id": 1,
    "medicines": [{
      "is_custom": true,
      "custom_medicine_name": "Test Medicine",
      "dosage": "1 tablet",
      "frequency": "Daily",
      "duration": "7 days"
    }]
  }'
```
- [ ] Medicine search works
- [ ] Create prescription works
- [ ] Update prescription works
- [ ] Validation works
- [ ] Error handling works

#### Step 2.5: Deploy Backend to Production
```bash
# Using your deployment method (example with PM2)
pm2 stop app
git pull origin main  # or your deployment method
npm install --production
pm2 start app

# Monitor logs
pm2 logs app
```
- [ ] Backend deployed
- [ ] Server running
- [ ] No errors in logs
- [ ] Health check passing

### Phase 3: Frontend Deployment (1 hour)

#### Step 3.1: Install Frontend Dependencies
```bash
# In your frontend directory
npm install lucide-react

# Verify installation
npm list lucide-react
```
- [ ] lucide-react installed
- [ ] Version compatible

#### Step 3.2: Add Components
```bash
# Copy components to your project
cp package/PrescriptionMedicineInput.jsx src/components/
cp package/PrescriptionForm.jsx src/components/

# If not using Tailwind:
cp package/PrescriptionMedicineInput-CSS.jsx src/components/
cp package/custom-medicine-styles.css src/styles/
```
- [ ] Components copied
- [ ] Styles added (if needed)
- [ ] Imports updated

#### Step 3.3: Integrate into Existing Prescription Page
```javascript
// In your prescription page component
import PrescriptionMedicineInput from './components/PrescriptionMedicineInput';

// Replace existing medicine input with new component
// Update state management to handle is_custom flag
// Update form submission to send correct payload
```
- [ ] Component imported
- [ ] Old component replaced
- [ ] State management updated
- [ ] Form submission updated

#### Step 3.4: Build and Test on Staging
```bash
# Build for staging
npm run build:staging

# Deploy to staging server
# (method depends on your setup)

# Test in browser
# - Open staging URL
# - Test medicine search
# - Test custom medicine addition
# - Test form submission
# - Test edit functionality
```
- [ ] Build successful
- [ ] Deployed to staging
- [ ] All features working
- [ ] No console errors
- [ ] Mobile responsive

#### Step 3.5: Deploy Frontend to Production
```bash
# Build for production
NODE_ENV=production npm run build

# Deploy build to production server
# (method depends on your setup - S3, CDN, web server, etc.)

# Examples:
# For S3:
# aws s3 sync build/ s3://your-bucket/
# aws cloudfront create-invalidation --distribution-id XXX --paths "/*"

# For traditional server:
# rsync -avz build/ user@server:/var/www/html/
```
- [ ] Production build created
- [ ] Deployed to production
- [ ] CDN cache cleared (if applicable)
- [ ] Assets loading correctly

### Phase 4: Verification (30 minutes)

#### Step 4.1: Smoke Tests
```bash
# Test critical paths
1. Open prescription page
2. Search for existing medicine → should work
3. Search for non-existent medicine → should show custom option
4. Add custom medicine → should save
5. Edit prescription with custom medicine → should load correctly
6. Create prescription with mixed medicines → should work
```
- [ ] Search works
- [ ] Custom medicine addition works
- [ ] Save works
- [ ] Edit works
- [ ] Mixed prescriptions work

#### Step 4.2: Monitor Production
```bash
# Monitor server logs
tail -f /var/log/app/error.log

# Monitor database
mysql -u user -p -e "SELECT COUNT(*) FROM prescription_medicines WHERE is_custom = TRUE;"

# Check error tracking (Sentry, etc.)
# Check performance monitoring (New Relic, etc.)
```
- [ ] No errors in logs
- [ ] Database writes working
- [ ] No performance degradation
- [ ] Error tracking clean

#### Step 4.3: User Acceptance Testing
- [ ] Test user creates prescription with custom medicine
- [ ] Test user edits prescription with custom medicine
- [ ] Test user creates mixed prescription
- [ ] All workflows complete successfully
- [ ] UI is intuitive
- [ ] No bugs found

### Phase 5: Post-Deployment (Ongoing)

#### Step 5.1: Documentation
- [ ] Update user manual
- [ ] Update API documentation
- [ ] Create training materials
- [ ] Notify support team
- [ ] Update changelog

#### Step 5.2: Monitoring
```bash
# Set up alerts for:
- High error rates on /api/prescriptions endpoints
- Slow database queries on prescription_medicines table
- Failed custom medicine saves
- Unusual patterns in custom medicine creation
```
- [ ] Alerts configured
- [ ] Dashboards updated
- [ ] Team notified of monitoring

#### Step 5.3: Gather Metrics
Track for first week:
- Number of prescriptions created
- Number with custom medicines
- Percentage of custom vs predefined
- User feedback
- Error rates
- Performance metrics

## Rollback Procedure

### If Issues Arise

#### Step 1: Rollback Frontend (5 minutes)
```bash
# Redeploy previous version
git checkout <previous-commit>
npm run build
# Deploy previous build
```

#### Step 2: Rollback Backend (5 minutes)
```bash
# Restore backed up files
cp controllers/prescriptionController.js.backup controllers/prescriptionController.js
cp validators/prescriptionValidation.js.backup validators/prescriptionValidation.js
cp routes/prescriptionRoutes.js.backup routes/prescriptionRoutes.js

# Restart server
pm2 restart app
```

#### Step 3: Rollback Database (if necessary - 15 minutes)
```sql
-- Only if absolutely necessary
ALTER TABLE prescription_medicines DROP CONSTRAINT chk_medicine_entry;
DROP INDEX idx_prescription_medicines_custom ON prescription_medicines;
DROP INDEX idx_prescription_medicines_medicine_id ON prescription_medicines;
ALTER TABLE prescription_medicines DROP COLUMN is_custom;
ALTER TABLE prescription_medicines DROP COLUMN custom_medicine_name;
ALTER TABLE prescription_medicines MODIFY COLUMN medicine_id INT NOT NULL;
```

**Note:** Database rollback will lose custom medicine data. Only do this if critical issues arise.

## Success Metrics

Deployment is successful when:
- ✅ No increase in error rates
- ✅ All smoke tests pass
- ✅ Users can create prescriptions with custom medicines
- ✅ Performance is acceptable (< 2s page load)
- ✅ No data integrity issues
- ✅ Support tickets remain normal
- ✅ Positive user feedback received

## Contact Information

**Deployment Lead:** [Name]  
**Database Admin:** [Name]  
**DevOps Contact:** [Name]  
**Emergency Rollback Authority:** [Name]  

## Timeline Summary

| Phase | Duration | Description |
|-------|----------|-------------|
| Database Migration | 30 min | Backup, test, migrate |
| Backend Deployment | 1 hour | Copy files, test, deploy |
| Frontend Deployment | 1 hour | Build, test, deploy |
| Verification | 30 min | Smoke tests, monitoring |
| **Total** | **3 hours** | **Complete deployment** |

---

**Deployment Date:** _____________  
**Deployed By:** _____________  
**Verified By:** _____________  
**Rollback Plan Reviewed:** ✅
