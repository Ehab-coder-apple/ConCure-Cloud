# Custom Medicine Feature - Testing Guide

## Test Cases

### 1. Custom Medicine Entry Tests

#### Test 1.1: Add Custom Medicine When No Search Results
**Steps:**
1. Go to prescription creation page
2. Type a medicine name that doesn't exist in database (e.g., "XYZ Medicine 2024")
3. Wait for search results
4. Verify "No medicines found" message appears
5. Click "Add '[query]' as custom medicine" button
6. Verify medicine name is pre-filled
7. Fill in dosage, frequency, duration
8. Save prescription

**Expected Result:**
- Custom medicine is saved with `is_custom = true`
- Prescription saves successfully
- Medicine appears in prescription view with custom indicator

#### Test 1.2: Add Custom Medicine via "Add Custom Medicine" Button
**Steps:**
1. Go to prescription creation page
2. Type any medicine name in search
3. When dropdown appears, click "Add Custom Medicine" option at bottom
4. Enter custom medicine name manually
5. Fill in dosage, frequency, duration
6. Save prescription

**Expected Result:**
- Can enter custom medicine name
- Prescription saves successfully
- Custom medicine is stored correctly

#### Test 1.3: Mix Custom and Predefined Medicines
**Steps:**
1. Go to prescription creation page
2. Add Medicine 1: Select from database (e.g., "Salbutamol")
3. Click "Add Medicine"
4. Add Medicine 2: Enter custom medicine
5. Fill all required fields for both
6. Save prescription

**Expected Result:**
- Prescription saves with both medicine types
- Database correctly stores one with `is_custom = false` and one with `is_custom = true`
- Both medicines display correctly in prescription view

### 2. Validation Tests

#### Test 2.1: Required Fields Validation
**Steps:**
1. Go to prescription creation page
2. Select/enter a medicine but leave dosage empty
3. Try to save

**Expected Result:**
- Validation error: "Dosage is required"
- Prescription does not save

#### Test 2.2: Empty Custom Medicine Name
**Steps:**
1. Go to prescription creation page
2. Click "Add Custom Medicine"
3. Leave medicine name empty
4. Fill other fields
5. Try to save

**Expected Result:**
- Validation error: "Custom medicine name is required"
- Prescription does not save

#### Test 2.3: Medicine Name Length Limit
**Steps:**
1. Enter custom medicine name longer than 255 characters
2. Try to save

**Expected Result:**
- Validation error: "Medicine name must not exceed 255 characters"

### 3. Edit Prescription Tests

#### Test 3.1: Edit Prescription with Custom Medicine
**Steps:**
1. Create prescription with custom medicine
2. Save prescription
3. Go to edit page for same prescription
4. Verify custom medicine loads correctly with "Custom Entry" badge
5. Modify dosage
6. Save

**Expected Result:**
- Custom medicine loads correctly
- Custom indicator shows
- Changes save successfully

#### Test 3.2: Change from Predefined to Custom
**Steps:**
1. Edit existing prescription with predefined medicine
2. Clear the medicine selection
3. Add custom medicine instead
4. Save

**Expected Result:**
- Medicine changes from predefined to custom
- Database updates correctly

#### Test 3.3: Change from Custom to Predefined
**Steps:**
1. Edit prescription with custom medicine
2. Clear the custom entry
3. Search and select predefined medicine
4. Save

**Expected Result:**
- Medicine changes from custom to predefined
- `is_custom` flag updates to false
- `custom_medicine_name` is cleared

### 4. UI/UX Tests

#### Test 4.1: Custom Medicine Visual Indicator
**Steps:**
1. Add custom medicine
2. Verify "Custom Entry" badge appears
3. Verify visual distinction from predefined medicines

**Expected Result:**
- Blue badge with "Custom Entry" text visible
- Clear visual distinction

#### Test 4.2: Clear Selection Functionality
**Steps:**
1. Select a medicine (predefined or custom)
2. Click X button to clear
3. Verify field resets

**Expected Result:**
- Medicine selection clears
- Form resets to search mode
- All fields clear

#### Test 4.3: Dropdown Behavior
**Steps:**
1. Type medicine name
2. Verify dropdown appears with results
3. Click outside
4. Verify dropdown closes
5. Click back in search field
6. Verify dropdown reopens with results

**Expected Result:**
- Dropdown behaves correctly
- Smooth user experience

### 5. Integration Tests

#### Test 5.1: API Endpoint - Create with Custom Medicine
**Request:**
```bash
POST /api/prescriptions
Content-Type: application/json

{
  "patient_id": 123,
  "doctor_id": 45,
  "medicines": [
    {
      "is_custom": true,
      "custom_medicine_name": "Custom Herbal Supplement",
      "dosage": "2 capsules",
      "frequency": "Twice daily",
      "duration": "30 days",
      "instructions": "Take with meals"
    }
  ],
  "notes": "Test prescription"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Prescription created successfully",
  "data": {
    "id": 356,
    "medicines": [...]
  }
}
```

#### Test 5.2: Database Integrity
**Steps:**
1. Create prescription with custom medicine
2. Query database directly:
```sql
SELECT * FROM prescription_medicines WHERE prescription_id = [id];
```

**Expected Result:**
- `is_custom` = 1 (true)
- `custom_medicine_name` = entered value
- `medicine_id` = NULL
- All other fields populated correctly

### 6. Error Handling Tests

#### Test 6.1: Network Error During Search
**Steps:**
1. Simulate network error
2. Try searching for medicine

**Expected Result:**
- User-friendly error message
- Option to retry or add custom medicine

#### Test 6.2: Invalid Medicine ID
**Steps:**
1. Send request with invalid medicine_id for predefined medicine

**Expected Result:**
- Error: "Medicine with ID X not found"
- Prescription does not save

## Performance Tests

### Test 7.1: Search Response Time
- Type medicine name
- Measure time for results to appear
- Should be < 500ms

### Test 7.2: Save Prescription with Multiple Medicines
- Add 10 medicines (mix of custom and predefined)
- Save prescription
- Should complete < 2 seconds

## Browser Compatibility
- [ ] Chrome
- [ ] Firefox  
- [ ] Safari
- [ ] Edge

## Mobile Responsiveness
- [ ] Test on mobile devices
- [ ] Verify dropdown works on touch screens
- [ ] Verify form is usable on small screens
