# Custom Medicine Feature - Usage Examples

## Example 1: Adding a Custom Herbal Medicine

### Scenario
Dr. Smith wants to prescribe a herbal supplement that's not in the hospital database.

### Steps
1. Navigate to Create Prescription page
2. Type "Ginger Root Extract" in medicine search
3. No results found
4. Click "Add 'Ginger Root Extract' as custom medicine"
5. Name auto-fills: "Ginger Root Extract"
6. Enter:
   - Dosage: "500mg"
   - Frequency: "Three times daily"
   - Duration: "14 days"
   - Instructions: "Take with meals"
7. Click "Save Prescription"

### Result
```json
{
  "prescription_id": 123,
  "medicines": [
    {
      "medicine_id": null,
      "is_custom": true,
      "custom_medicine_name": "Ginger Root Extract",
      "dosage": "500mg",
      "frequency": "Three times daily",
      "duration": "14 days",
      "instructions": "Take with meals"
    }
  ]
}
```

## Example 2: Mixed Prescription (Custom + Predefined)

### Scenario
Dr. Johnson prescribes both a standard medicine and a custom compound.

### Steps
1. **Medicine 1** (Predefined):
   - Search "Metformin"
   - Select "Metformin 500mg Tablet"
   - Dosage: "1 tablet"
   - Frequency: "Twice daily"
   - Duration: "30 days"

2. Click "+ Add Medicine"

3. **Medicine 2** (Custom):
   - Type "Custom Compound ABC-123"
   - No results → Click "Add as custom medicine"
   - Dosage: "5ml"
   - Frequency: "Once daily"
   - Duration: "15 days"

4. Save Prescription

### Result
```json
{
  "prescription_id": 124,
  "medicines": [
    {
      "medicine_id": 45,
      "is_custom": false,
      "medicine_name": "Metformin 500mg Tablet",
      "dosage": "1 tablet",
      "frequency": "Twice daily",
      "duration": "30 days"
    },
    {
      "medicine_id": null,
      "is_custom": true,
      "custom_medicine_name": "Custom Compound ABC-123",
      "dosage": "5ml",
      "frequency": "Once daily",
      "duration": "15 days"
    }
  ]
}
```

## Example 3: Editing Prescription with Custom Medicine

### Scenario
Need to change dosage of custom medicine in existing prescription.

### Steps
1. Navigate to prescription #123
2. Click "Edit"
3. Custom medicine loads showing:
   - Name: "Ginger Root Extract" with [Custom Entry] badge
   - Current dosage: "500mg"
4. Change dosage to "1000mg"
5. Click "Update Prescription"

### Database Update
```sql
UPDATE prescription_medicines 
SET dosage = '1000mg'
WHERE prescription_id = 123 
  AND is_custom = TRUE 
  AND custom_medicine_name = 'Ginger Root Extract';
```

## Example 4: Using "Add Custom Medicine" from Dropdown

### Scenario
User knows they want to add custom medicine without searching.

### Steps
1. Click on medicine search field
2. Type any character to show dropdown
3. At bottom of dropdown, click "+ Add Custom Medicine"
4. Enter custom medicine details manually
5. Complete prescription

## Example 5: API Integration

### Creating Prescription via API

**Request:**
```bash
curl -X POST http://localhost:3000/api/prescriptions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "patient_id": 456,
    "doctor_id": 789,
    "medicines": [
      {
        "is_custom": true,
        "custom_medicine_name": "Ayurvedic Medicine Mix",
        "dosage": "2 teaspoons",
        "frequency": "Twice daily",
        "duration": "21 days",
        "instructions": "Mix with warm water"
      }
    ],
    "notes": "Patient prefers natural remedies"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Prescription created successfully",
  "data": {
    "id": 125,
    "patient_id": 456,
    "doctor_id": 789,
    "medicines": [
      {
        "id": 789,
        "medicine_id": null,
        "is_custom": true,
        "custom_medicine_name": "Ayurvedic Medicine Mix",
        "medicine_name": "Ayurvedic Medicine Mix",
        "dosage": "2 teaspoons",
        "frequency": "Twice daily",
        "duration": "21 days",
        "instructions": "Mix with warm water"
      }
    ],
    "notes": "Patient prefers natural remedies",
    "created_at": "2026-07-27T10:30:00Z"
  }
}
```

## Example 6: Validation Error Handling

### Scenario 1: Empty Custom Medicine Name
```javascript
// Attempting to save with empty custom name
{
  "is_custom": true,
  "custom_medicine_name": "",
  "dosage": "1 tablet"
}

// Response
{
  "success": false,
  "errors": [
    {
      "field": "medicines[0].custom_medicine_name",
      "message": "Custom medicine name is required"
    }
  ]
}
```

### Scenario 2: Invalid Medicine ID for Predefined
```javascript
// Attempting to save with non-existent medicine_id
{
  "is_custom": false,
  "medicine_id": 99999,
  "dosage": "1 tablet"
}

// Response
{
  "success": false,
  "message": "Medicine with ID 99999 not found"
}
```

## Example 7: Searching and Switching to Custom

### User Journey
```
1. User types: "Ibu"
   → Shows: Ibuprofen 200mg, Ibuprofen 400mg

2. User types: "Ibupr"
   → Shows: Ibuprofen 200mg, Ibuprofen 400mg

3. User types: "Ibuprom"
   → Shows: "No medicines found for 'Ibuprom'"
   → Button: "Add 'Ibuprom' as custom medicine"

4. User clicks button
   → Switches to custom mode
   → "Ibuprom" pre-filled
   → Shows [Custom Entry] badge

5. User completes: "Ibuprom Special Formula"
   → Adds dosage, frequency, duration
   → Saves successfully
```

## Example 8: Complex Prescription with Multiple Custom Medicines

### Scenario
Specialist prescribes multiple custom compounds.

```json
{
  "prescription_id": 126,
  "patient_id": 789,
  "medicines": [
    {
      "is_custom": true,
      "custom_medicine_name": "Vitamin D3 (High Dose)",
      "dosage": "10,000 IU",
      "frequency": "Once weekly",
      "duration": "12 weeks"
    },
    {
      "is_custom": true,
      "custom_medicine_name": "Magnesium Glycinate Compound",
      "dosage": "400mg",
      "frequency": "Daily before bed",
      "duration": "12 weeks"
    },
    {
      "is_custom": false,
      "medicine_id": 234,
      "medicine_name": "Omega-3 Fish Oil 1000mg",
      "dosage": "2 capsules",
      "frequency": "Daily with meal",
      "duration": "12 weeks"
    }
  ],
  "notes": "Nutritional supplementation protocol for vitamin deficiency"
}
```

## Example 9: Mobile User Experience

### On Mobile Device (< 768px)
```
┌─────────────────────────────┐
│ Medicine Name               │
│ ┌─────────────────────────┐ │
│ │ Search...             ✕ │ │
│ └─────────────────────────┘ │
│                             │
│ Dosage                      │
│ ┌─────────────────────────┐ │
│ │ 1 tablet                │ │
│ └─────────────────────────┘ │
│                             │
│ Frequency                   │
│ ┌─────────────────────────┐ │
│ │ Twice daily             │ │
│ └─────────────────────────┘ │
│                             │
│ Duration                    │
│ ┌─────────────────────────┐ │
│ │ 7 days                  │ │
│ └─────────────────────────┘ │
└─────────────────────────────┘
```
Fields stack vertically for easier touch input.

## Example 10: Prescription Print View

### Display Format
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PRESCRIPTION #125
Date: July 27, 2026
Doctor: Dr. Sarah Johnson
Patient: John Doe (ID: 456)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

MEDICATIONS:

1. Metformin 500mg Tablet
   Dosage: 1 tablet
   Frequency: Twice daily
   Duration: 30 days
   Instructions: Take with meals

2. Ayurvedic Medicine Mix ⭐ CUSTOM
   Dosage: 2 teaspoons
   Frequency: Twice daily
   Duration: 21 days
   Instructions: Mix with warm water

NOTES:
Patient prefers natural remedies

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Doctor's Signature: _____________________
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```
Custom medicines marked with ⭐ CUSTOM indicator.

## Summary

These examples demonstrate:
✅ Adding custom medicines when search yields no results
✅ Using "Add Custom Medicine" button from dropdown
✅ Mixing custom and predefined medicines
✅ Editing prescriptions with custom medicines
✅ API integration
✅ Validation and error handling
✅ Mobile responsiveness
✅ Print/display formatting
