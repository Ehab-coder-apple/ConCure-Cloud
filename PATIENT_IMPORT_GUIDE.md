# 📋 Patient Import Guide

## 🎯 **Overview**
ConCure now supports **bulk patient import** from Excel and CSV files, allowing you to quickly migrate existing patient data into the system.

## 🚀 **Getting Started**

### **Access Patient Import:**
1. Go to **Patients** → **Import Patients** button
2. Or directly: `http://your-domain/patients/import`

### **Required Permissions:**
- `patients_create` permission
- Admin, Doctor, or Nurse role

## 📊 **Supported File Formats**
- **Excel**: `.xlsx`, `.xls` (Recommended)
- **CSV**: `.csv`
- **Maximum file size**: 10MB
- **Maximum rows**: Unlimited (processed in batches of 100)

## 📋 **Required Fields**
| Field | Required | Description |
|-------|----------|-------------|
| `first_name` | ✅ Yes | Patient's first name |
| `last_name` | ✅ Yes | Patient's last name |

## 📋 **Optional Fields**
| Field | Format | Example | Description |
|-------|--------|---------|-------------|
| `date_of_birth` | YYYY-MM-DD | 1985-03-15 | Date of birth |
| `gender` | male/female | male | Patient gender |
| `phone` | +country_code | +9647501234567 | Primary phone number |
| `whatsapp_phone` | +country_code | +9647501234567 | WhatsApp number |
| `email` | email@domain.com | patient@email.com | Email address |
| `address` | Text | Baghdad, Iraq | Full address |
| `job` | Text | Engineer | Occupation |
| `education` | Text | Bachelor | Education level |
| `height` | Number (cm) | 175 | Height in centimeters |
| `weight` | Number (kg) | 70 | Weight in kilograms |
| `allergies` | Text | Penicillin, Nuts | Known allergies |
| `is_pregnant` | true/false | true | Pregnancy status |
| `chronic_illnesses` | Text | Diabetes Type 2 | Chronic conditions |
| `surgeries_history` | Text | Appendectomy 2010 | Previous surgeries |
| `diet_history` | Text | Low carb diet | Diet information |
| `notes` | Text | Regular checkups needed | Additional notes |
| `emergency_contact_name` | Text | John Doe | Emergency contact |
| `emergency_contact_phone` | +country_code | +9647509876543 | Emergency phone |
| `is_active` | true/false | true | Patient status (default: true) |

## 🔧 **Import Process**

### **Step 1: Download Template**
1. Click **"Download Excel Template with Sample Data"**
2. Or **"Download Empty Excel Template"**
3. Template includes all fields with proper formatting

### **Step 2: Prepare Your Data**
1. Fill in patient information
2. Follow the format guidelines
3. **Required**: First Name, Last Name
4. **Recommended**: Phone, Date of Birth, Gender

### **Step 3: Upload and Import**
1. Select your prepared file
2. Click **"Import Patients"**
3. Review import results

## ✅ **Data Validation**

### **Automatic Validations:**
- **Required fields**: First name, last name must be provided
- **Date format**: YYYY-MM-DD format validation
- **Email format**: Valid email address format
- **Gender**: Must be 'male' or 'female'
- **Phone numbers**: Accepts international format
- **Duplicates**: Same name + phone combination will be skipped

### **Automatic Calculations:**
- **Patient ID**: Auto-generated unique ID (P123456)
- **BMI**: Calculated if height and weight provided
- **Clinic Assignment**: Automatically assigned to your clinic

## 🔄 **Import Results**

### **Success Messages:**
- **Imported**: Number of successfully imported patients
- **Skipped**: Number of duplicate/invalid patients
- **Errors**: Detailed error messages for failed imports

### **Common Skip Reasons:**
- Empty rows (automatically skipped)
- Missing required fields
- Duplicate patients (same name + phone)
- Invalid date formats
- Invalid email formats

## 📝 **Sample Data**

### **Excel Template Includes:**
```
first_name: Ahmed
last_name: Hassan
date_of_birth: 1985-03-15
gender: male
phone: +9647501234567
whatsapp_phone: +9647501234567
email: ahmed.hassan@email.com
address: Baghdad, Iraq
job: Engineer
education: Bachelor
height: 175
weight: 70
allergies: Penicillin
is_pregnant: false
chronic_illnesses: Diabetes Type 2
surgeries_history: Appendectomy 2010
diet_history: Low carb diet
notes: Regular checkups needed
emergency_contact_name: Fatima Hassan
emergency_contact_phone: +9647509876543
is_active: true
```

## 💡 **Best Practices**

### **✅ Do:**
- Use the provided Excel template
- Test with small batches first (5-10 patients)
- Include country codes in phone numbers
- Use consistent date formats (YYYY-MM-DD)
- Fill required fields completely
- Review data before importing

### **❌ Don't:**
- Mix date formats in the same file
- Leave required fields empty
- Use special characters in names
- Import without testing first
- Ignore validation errors

## 🔧 **Troubleshooting**

### **Common Issues:**

**"Row X: First Name is required"**
- Solution: Fill in the first_name column

**"Row X: Invalid date format"**
- Solution: Use YYYY-MM-DD format (e.g., 1985-03-15)

**"Row X: Patient already exists"**
- Solution: Patient with same name + phone exists, will be skipped

**"Row X: Invalid email format"**
- Solution: Use valid email format (user@domain.com)

### **File Issues:**

**"File too large"**
- Solution: Split into smaller files (under 10MB each)

**"Unsupported file format"**
- Solution: Use .xlsx, .xls, or .csv format

**"Import failed"**
- Solution: Check file format and try again

## 🎯 **Performance**

### **Import Speed:**
- **Small files** (< 100 patients): ~5-10 seconds
- **Medium files** (100-1000 patients): ~30-60 seconds
- **Large files** (1000+ patients): ~2-5 minutes

### **Batch Processing:**
- Processes 100 patients at a time
- Memory efficient for large files
- Progress tracking available

## 🔐 **Security & Privacy**

### **Data Protection:**
- All imports are clinic-specific
- No cross-clinic data access
- Uploaded files are processed and deleted
- All data encrypted in transit and at rest

### **Audit Trail:**
- Import actions are logged
- Created by user tracking
- Timestamp recording
- Error logging for troubleshooting

## 📞 **Support**

### **Need Help?**
- Check the import instructions on the import page
- Review error messages for specific issues
- Test with sample data first
- Contact your system administrator

### **Feature Requests:**
- Additional field support
- Custom validation rules
- Bulk update functionality
- Export/import templates

---

## 🎉 **Success!**
You can now efficiently import existing patient data into ConCure, saving time and ensuring data consistency across your clinic management system!
