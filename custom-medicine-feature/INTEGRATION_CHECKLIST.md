# Custom Medicine Feature - Integration Checklist

## Pre-Implementation Checklist

- [ ] Review all implementation files provided
- [ ] Backup database before running migrations
- [ ] Ensure development environment is set up
- [ ] Verify dependencies are installed (React, Express, MySQL/PostgreSQL)

## Database Setup

### Step 1: Run Database Migration
```bash
# Review the migration file first
cat database_migration_custom_medicines.sql

# Execute migration
mysql -u [username] -p [database_name] < database_migration_custom_medicines.sql

# Or for PostgreSQL
psql -U [username] -d [database_name] -f database_migration_custom_medicines.sql
```

- [ ] Migration executed successfully
- [ ] Verify new columns exist: `is_custom`, `custom_medicine_name`
- [ ] Verify `medicine_id` is now nullable
- [ ] Check constraints are in place

### Step 2: Verify Database Changes
```sql
-- Check table structure
DESCRIBE prescription_medicines;

-- Test inserting custom medicine
INSERT INTO prescription_medicines 
  (prescription_id, medicine_id, is_custom, custom_medicine_name, dosage, frequency, duration)
VALUES 
  (1, NULL, TRUE, 'Test Custom Medicine', '1 tablet', 'Daily', '7 days');

-- Verify it works
SELECT * FROM prescription_medicines WHERE is_custom = TRUE;

-- Clean up test data
DELETE FROM prescription_medicines WHERE custom_medicine_name = 'Test Custom Medicine';
```

- [ ] Can insert custom medicines
- [ ] Constraints work correctly
- [ ] Existing prescriptions still load correctly

## Backend Integration

### Step 1: Add Controller
- [ ] Copy `prescriptionController.js` to your controllers directory
- [ ] Update database connection imports to match your setup
- [ ] Adjust table/column names if different in your schema
- [ ] Test endpoints with Postman/curl

### Step 2: Add Validation
- [ ] Copy `prescriptionValidation.js` to your validators directory
- [ ] Import into controller
- [ ] Test validation with various payloads

### Step 3: Update Routes
- [ ] Copy `prescriptionRoutes.js` to your routes directory
- [ ] Update authentication middleware path
- [ ] Add routes to main app.js/server.js:
```javascript
const prescriptionRoutes = require('./routes/prescriptionRoutes');
app.use('/api', prescriptionRoutes);
```
- [ ] Test all endpoints

### Step 4: Test Backend Endpoints

**Test 1: Medicine Search**
```bash
curl -X GET "http://localhost:3000/api/medicines/search?q=salbu" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
Expected: List of matching medicines

**Test 2: Create Prescription with Custom Medicine**
```bash
curl -X POST "http://localhost:3000/api/prescriptions" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "patient_id": 1,
    "doctor_id": 1,
    "medicines": [{
      "is_custom": true,
      "custom_medicine_name": "Custom Test Med",
      "dosage": "1 tablet",
      "frequency": "Twice daily",
      "duration": "7 days",
      "instructions": "Take with food"
    }]
  }'
```
Expected: Success response with prescription ID

- [ ] Medicine search works
- [ ] Create prescription with custom medicine works
- [ ] Create prescription with predefined medicine works
- [ ] Update prescription works
- [ ] Get prescription returns correct data

## Frontend Integration

### Step 1: Add Components
- [ ] Copy `PrescriptionMedicineInput.jsx` to your components directory
- [ ] Copy `PrescriptionForm.jsx` to your components directory
- [ ] Install required dependencies:
```bash
npm install lucide-react
# or
yarn add lucide-react
```

### Step 2: Update Existing Prescription Page
- [ ] Replace existing medicine input with `PrescriptionMedicineInput` component
- [ ] Update state management to handle `is_custom` flag
- [ ] Update form submission to send correct payload format
- [ ] Test in development mode

### Step 3: Style Integration
- [ ] Ensure Tailwind CSS is configured (or adapt classes to your CSS framework)
- [ ] Import custom styles if needed
- [ ] Verify responsive design on mobile/tablet

### Step 4: Test Frontend Functionality
- [ ] Medicine search displays results
- [ ] Dropdown shows "Add Custom Medicine" option
- [ ] No results shows custom medicine prompt
- [ ] Can add custom medicine
- [ ] Custom badge displays correctly
- [ ] Can mix custom and predefined medicines
- [ ] Form validation works
- [ ] Can save prescription
- [ ] Can edit prescription with custom medicines
- [ ] Can delete medicine entries

## Integration Testing

### End-to-End Tests
- [ ] User can create prescription with custom medicine
- [ ] User can create prescription with predefined medicine
- [ ] User can mix both types in one prescription
- [ ] Edit prescription preserves custom medicines
- [ ] Prescription displays correctly in view mode
- [ ] Print/PDF includes custom medicines

### Cross-Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Mobile Testing
- [ ] iOS Safari
- [ ] Android Chrome
- [ ] Tablet view

## Security Checklist

- [ ] Input sanitization implemented
- [ ] SQL injection prevention (using parameterized queries)
- [ ] XSS protection (escaping user input)
- [ ] Authentication required for all endpoints
- [ ] Authorization checks (user can only access own prescriptions)
- [ ] Rate limiting on search endpoint
- [ ] Maximum length validation on custom medicine names

## Performance Optimization

- [ ] Database indexes on `is_custom` column
- [ ] Database indexes on `medicine_id` column
- [ ] Medicine search debounced on frontend (300ms delay)
- [ ] Lazy loading for prescription lists
- [ ] Pagination for large result sets

## Documentation

- [ ] Update API documentation with new endpoints
- [ ] Add custom medicine feature to user manual
- [ ] Create training materials for staff
- [ ] Document database schema changes
- [ ] Add comments to code

## Deployment

### Pre-Deployment
- [ ] Code reviewed by team
- [ ] All tests passing
- [ ] Staging environment tested
- [ ] Database migration tested on staging
- [ ] Rollback plan prepared

### Deployment Steps
1. [ ] Backup production database
2. [ ] Run database migration on production
3. [ ] Deploy backend code
4. [ ] Deploy frontend code
5. [ ] Verify deployment
6. [ ] Monitor for errors

### Post-Deployment
- [ ] Verify custom medicine creation works in production
- [ ] Monitor error logs
- [ ] Check database performance
- [ ] Gather user feedback

## Rollback Plan

If issues arise:
```sql
-- Rollback database changes
ALTER TABLE prescription_medicines DROP CONSTRAINT chk_medicine_entry;
DROP INDEX idx_prescription_medicines_custom ON prescription_medicines;
DROP INDEX idx_prescription_medicines_medicine_id ON prescription_medicines;
ALTER TABLE prescription_medicines DROP COLUMN is_custom;
ALTER TABLE prescription_medicines DROP COLUMN custom_medicine_name;
ALTER TABLE prescription_medicines MODIFY COLUMN medicine_id INT NOT NULL;
```

- [ ] Rollback script tested
- [ ] Previous version tagged in version control
- [ ] Team knows rollback procedure

## Support

- [ ] Monitor support tickets for issues
- [ ] Prepare FAQ for common questions
- [ ] Train support team on new feature

## Success Metrics

Track these metrics after deployment:
- [ ] Number of custom medicines created per day
- [ ] Percentage of prescriptions with custom medicines
- [ ] User satisfaction with new feature
- [ ] Error rate for custom medicine creation
- [ ] Average time to create prescription (before vs after)
