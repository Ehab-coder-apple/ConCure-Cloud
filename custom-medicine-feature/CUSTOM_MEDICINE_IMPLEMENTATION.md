# Custom Medicine Feature Implementation Guide

## Overview
This guide provides a complete implementation for adding custom medicines directly from the prescription page.

## Architecture Changes

### 1. Database Schema Updates

Add a new field to the prescription_medicines table to distinguish between predefined and custom medicines:

```sql
ALTER TABLE prescription_medicines ADD COLUMN is_custom BOOLEAN DEFAULT FALSE;
ALTER TABLE prescription_medicines ADD COLUMN custom_medicine_name VARCHAR(255);
ALTER TABLE prescription_medicines MODIFY COLUMN medicine_id INT NULL;
```

### 2. Backend API Changes

#### A. Update Prescription Save Endpoint
Modify the prescription save/update endpoint to handle custom medicines:

**Endpoint**: `POST/PUT /api/prescriptions/{id}`

**Request Payload Example**:
```json
{
  "patient_id": 123,
  "medicines": [
    {
      "medicine_id": 45,
      "is_custom": false,
      "dosage": "1 tablet",
      "frequency": "Twice daily",
      "duration": "7 days",
      "instructions": "Take after meals"
    },
    {
      "medicine_id": null,
      "is_custom": true,
      "custom_medicine_name": "Herbal Supplement XYZ",
      "dosage": "2 capsules",
      "frequency": "Once daily",
      "duration": "30 days",
      "instructions": "Take with water"
    }
  ]
}
```

#### B. Validation Logic
- If `is_custom` is false, `medicine_id` must be valid and exist in medicines table
- If `is_custom` is true, `custom_medicine_name` is required and `medicine_id` can be null

### 3. Frontend Components

The implementation consists of these key components:

#### A. Medicine Search Component with Custom Option
- Shows dropdown with search results
- Displays "No results found - Add as custom medicine" when search yields no results
- Shows "Add Custom Medicine" button/option at the bottom of dropdown

#### B. Custom Medicine Input Mode
- Toggles to free-text input for medicine name
- Maintains same form for dosage, frequency, duration, and instructions
- Visual indicator that this is a custom entry

## Implementation Files

See the following files for complete implementation:
- `PrescriptionMedicineInput.jsx` - React component
- `prescriptionController.js` - Backend controller
- `prescriptionModel.js` - Database model
- `prescriptionValidation.js` - Validation logic

## User Flow

1. User types medicine name in search field
2. If medicine exists in database: Select from dropdown as usual
3. If no results found: 
   - Show "No medicines found for '[query]'"
   - Display button "Add '[query]' as custom medicine"
   - On click, switch to custom mode with query pre-filled
4. User can also click "Add Custom Medicine" option in dropdown
5. In custom mode: User enters medicine name manually
6. Complete dosage, frequency, duration as normal
7. Save prescription - custom entry is marked with `is_custom: true`

## Testing Checklist

- [ ] Can add custom medicine when search returns no results
- [ ] Can add custom medicine via "Add Custom" button
- [ ] Can save prescription with mix of custom and predefined medicines
- [ ] Can edit prescription with custom medicines
- [ ] Custom medicine name displays correctly in prescription view
- [ ] Validation prevents saving empty custom medicine names
- [ ] UI indicates which medicines are custom vs. predefined
