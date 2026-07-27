# Custom Medicine Feature - Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                          │
│                     (Prescription Page)                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ User Actions
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   REACT COMPONENTS                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌───────────────────────────────────────────────────────┐     │
│  │           PrescriptionForm.jsx                        │     │
│  │  - Manages overall prescription state                 │     │
│  │  - Handles form submission                            │     │
│  │  - Validates before save                              │     │
│  └───────────────────────────────────────────────────────┘     │
│                         │                                       │
│                         │ Contains multiple                     │
│                         ▼                                       │
│  ┌───────────────────────────────────────────────────────┐     │
│  │     PrescriptionMedicineInput.jsx (x N)               │     │
│  │  - Search medicine input                              │     │
│  │  - Toggle custom/predefined mode                      │     │
│  │  - Display dropdown results                           │     │
│  │  - Handle "No results" → Custom option                │     │
│  │  - Dosage/Frequency/Duration inputs                   │     │
│  └───────────────────────────────────────────────────────┘     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ API Calls (fetch/axios)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API ENDPOINTS                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  GET  /api/medicines/search?q=term                             │
│  ├─ Search predefined medicines                                │
│  └─ Returns: Array of matching medicines                       │
│                                                                 │
│  POST /api/prescriptions                                       │
│  ├─ Create new prescription                                    │
│  ├─ Accepts: { medicines: [...], patient_id, doctor_id }       │
│  └─ Returns: Created prescription with ID                      │
│                                                                 │
│  PUT  /api/prescriptions/:id                                   │
│  ├─ Update existing prescription                               │
│  ├─ Accepts: { medicines: [...], patient_id, doctor_id }       │
│  └─ Returns: Updated prescription                              │
│                                                                 │
│  GET  /api/prescriptions/:id                                   │
│  ├─ Fetch prescription details                                 │
│  └─ Returns: Prescription with all medicines                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Route handlers
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   BUSINESS LOGIC LAYER                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌───────────────────────────────────────────────────────┐     │
│  │        prescriptionController.js                      │     │
│  │  ┌─────────────────────────────────────────────────┐  │     │
│  │  │ savePrescription()                              │  │     │
│  │  │  - Validate request                             │  │     │
│  │  │  - Begin transaction                            │  │     │
│  │  │  - Save prescription                            │  │     │
│  │  │  - Save each medicine (loop)                    │  │     │
│  │  │  - Commit transaction                           │  │     │
│  │  └─────────────────────────────────────────────────┘  │     │
│  │  ┌─────────────────────────────────────────────────┐  │     │
│  │  │ savePrescriptionMedicine()                      │  │     │
│  │  │  - Check is_custom flag                         │  │     │
│  │  │  - If custom: Save with custom_medicine_name    │  │     │
│  │  │  - If predefined: Verify medicine_id exists     │  │     │
│  │  │  - Insert into prescription_medicines table     │  │     │
│  │  └─────────────────────────────────────────────────┘  │     │
│  │  ┌─────────────────────────────────────────────────┐  │     │
│  │  │ searchMedicines()                               │  │     │
│  │  │  - Query medicines table                        │  │     │
│  │  │  - Return matching results                      │  │     │
│  │  └─────────────────────────────────────────────────┘  │     │
│  └───────────────────────────────────────────────────────┘     │
│                                                                 │
│  ┌───────────────────────────────────────────────────────┐     │
│  │        prescriptionValidation.js                      │     │
│  │  - validatePrescription()                             │     │
│  │  - validateMedicine()                                 │     │
│  │  - sanitizeMedicineInput()                            │     │
│  └───────────────────────────────────────────────────────┘     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Database queries
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────┐         ┌──────────────────────┐     │
│  │   prescriptions      │         │   medicines          │     │
│  ├──────────────────────┤         ├──────────────────────┤     │
│  │ id (PK)              │         │ id (PK)              │     │
│  │ patient_id           │         │ name                 │     │
│  │ doctor_id            │         │ strength             │     │
│  │ notes                │         │ form                 │     │
│  │ created_at           │         │ manufacturer         │     │
│  └──────────────────────┘         └──────────────────────┘     │
│           │                                     ▲               │
│           │                                     │               │
│           │        ┌────────────────────────────┘               │
│           │        │                                            │
│           ▼        │ (Optional FK)                              │
│  ┌─────────────────────────────────────────────────────┐       │
│  │        prescription_medicines                       │       │
│  ├─────────────────────────────────────────────────────┤       │
│  │ id (PK)                                             │       │
│  │ prescription_id (FK to prescriptions)               │       │
│  │ medicine_id (NULL if custom) ────────────────────┐  │       │
│  │ is_custom (BOOLEAN) ← NEW COLUMN                 │  │       │
│  │ custom_medicine_name (VARCHAR) ← NEW COLUMN      │  │       │
│  │ dosage                                            │  │       │
│  │ frequency                                         │  │       │
│  │ duration                                          │  │       │
│  │ instructions                                      │  │       │
│  └─────────────────────────────────────────────────────┘       │
│                                                                 │
│  Constraint: (is_custom=FALSE AND medicine_id IS NOT NULL)     │
│           OR (is_custom=TRUE AND custom_medicine_name IS SET)  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### Flow 1: Adding Predefined Medicine

```
User Types "Salbutamol"
        │
        ▼
Frontend: handleSearch()
        │
        ▼
API: GET /api/medicines/search?q=Salbutamol
        │
        ▼
Controller: searchMedicines()
        │
        ▼
Database: SELECT * FROM medicines WHERE name LIKE '%Salbutamol%'
        │
        ▼
Returns: [{ id: 45, name: "Salbutamol 2mg", ... }, ...]
        │
        ▼
Frontend: Display dropdown with results
        │
        ▼
User: Clicks "Salbutamol 2mg"
        │
        ▼
Frontend: handleSelectMedicine()
        │
        ▼
State updated: { medicine_id: 45, is_custom: false, ... }
```

### Flow 2: Adding Custom Medicine (No Results)

```
User Types "Herbal Med XYZ"
        │
        ▼
Frontend: handleSearch()
        │
        ▼
API: GET /api/medicines/search?q=Herbal Med XYZ
        │
        ▼
Database: SELECT * FROM medicines WHERE name LIKE '%Herbal Med XYZ%'
        │
        ▼
Returns: [] (empty array)
        │
        ▼
Frontend: Display "No results found"
        │
        ▼
Frontend: Show button "Add 'Herbal Med XYZ' as custom medicine"
        │
        ▼
User: Clicks button
        │
        ▼
Frontend: handleAddCustomMedicine("Herbal Med XYZ")
        │
        ▼
State updated: { 
  medicine_id: null, 
  is_custom: true, 
  custom_medicine_name: "Herbal Med XYZ",
  ...
}
```

### Flow 3: Saving Prescription with Custom Medicine

```
User: Clicks "Save Prescription"
        │
        ▼
Frontend: validateForm()
        │
        ▼
API: POST /api/prescriptions
Body: {
  patient_id: 123,
  doctor_id: 45,
  medicines: [
    { is_custom: true, custom_medicine_name: "Herbal Med XYZ", ... }
  ]
}
        │
        ▼
Controller: savePrescription()
        │
        ▼
Validation: validatePrescription()
        │
        ▼
Database: BEGIN TRANSACTION
        │
        ▼
Database: INSERT INTO prescriptions (patient_id, doctor_id, ...)
        │
        ▼
Get prescription_id (e.g., 356)
        │
        ▼
For each medicine:
  Controller: savePrescriptionMedicine()
        │
        ▼
  Check: is_custom === true
        │
        ▼
  Database: INSERT INTO prescription_medicines 
            (prescription_id, medicine_id, is_custom, custom_medicine_name, ...)
            VALUES (356, NULL, TRUE, 'Herbal Med XYZ', ...)
        │
        ▼
Database: COMMIT TRANSACTION
        │
        ▼
Returns: { success: true, data: { id: 356, ... } }
        │
        ▼
Frontend: Show success message
```

## State Management

### Component State (PrescriptionMedicineInput)

```javascript
{
  searchQuery: "",              // Current search input
  searchResults: [],            // Results from API
  showDropdown: false,          // Show/hide dropdown
  isCustomMode: false,          // Custom vs predefined mode
  selectedMedicine: null        // Currently selected medicine
}
```

### Medicine Data Structure

```javascript
// Predefined Medicine
{
  medicine_id: 45,
  medicine_name: "Salbutamol 2mg",
  is_custom: false,
  custom_medicine_name: null,
  dosage: "1 tablet",
  frequency: "Twice daily",
  duration: "7 days",
  instructions: "Take after meals"
}

// Custom Medicine
{
  medicine_id: null,
  medicine_name: null,
  is_custom: true,
  custom_medicine_name: "Herbal Supplement XYZ",
  dosage: "2 capsules",
  frequency: "Once daily",
  duration: "30 days",
  instructions: "Take with water"
}
```

## Component Interaction

```
┌─────────────────────────────────────────┐
│      PrescriptionForm                   │
│                                         │
│  State:                                 │
│  - medicines: [...]                     │
│  - notes: ""                            │
│                                         │
│  Methods:                               │
│  - handleMedicineChange(index, data)    │
│  - handleAddMedicine()                  │
│  - handleRemoveMedicine(index)          │
│  - handleSubmit()                       │
│                                         │
└─────────────────────────────────────────┘
         │              │              │
         │              │              │
         ▼              ▼              ▼
    ┌────────┐    ┌────────┐    ┌────────┐
    │Medicine│    │Medicine│    │Medicine│
    │Input #1│    │Input #2│    │Input #3│
    └────────┘    └────────┘    └────────┘
         │              │              │
         └──────────────┴──────────────┘
                    │
                    │ onChange callback
                    ▼
         Updates parent state via
         handleMedicineChange(index, updatedData)
```

This architecture ensures:
✅ Clean separation of concerns
✅ Reusable components
✅ Maintainable code structure
✅ Scalable design
✅ Data integrity
