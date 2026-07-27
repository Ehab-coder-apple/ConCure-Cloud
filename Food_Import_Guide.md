# 🍎 ConCure Food Import Guide - Correct Format

## 🚨 Problem Identified
Your previous Excel file had **column mapping issues** causing:
- Protein values showing calories (e.g., 96g protein for salad!)
- Carbohydrates and fat showing 0 or wrong values
- Unrealistic nutritional data

## ✅ Solution: Correct Column Order

### **Required Column Headers (Exact Order):**
```
name | name_en | name_ar | name_ku_bahdini | name_ku_sorani | food_group | calories | protein | carbohydrates | fat | fiber | sugar | sodium | serving_size | serving_weight | description | description_en | description_ar | description_ku_bahdini | description_ku_sorani
```

### **Column Descriptions:**

#### **Required Columns:**
1. **name** - Food name (any language)
2. **calories** - Calories per serving (number)

#### **Optional but Important:**
3. **protein** - Protein in grams (number)
4. **carbohydrates** - Carbs in grams (number) 
5. **fat** - Fat in grams (number)
6. **serving_size** - Description (e.g., "1 cup", "100g", "1 medium apple")
7. **serving_weight** - Weight in grams (number)

#### **Multilingual Support:**
8. **name_en** - English name
9. **name_ar** - Arabic name
10. **name_ku_bahdini** - Kurdish Bahdini name
11. **name_ku_sorani** - Kurdish Sorani name

#### **Additional Optional:**
12. **food_group** - Category (e.g., "Fruits", "Vegetables", "Proteins")
13. **fiber** - Fiber in grams
14. **sugar** - Sugar in grams  
15. **sodium** - Sodium in mg
16. **description** fields - Food descriptions in different languages

## 📊 Sample Data Format

### **Correct Example:**
```
Apple,Apple,تفاح,سێو,سێو,Fruits,52,0.3,14,0.2,2.4,10.4,1,1 medium apple,182
```

### **What This Means:**
- **Name:** Apple
- **Calories:** 52 (per serving)
- **Protein:** 0.3g
- **Carbohydrates:** 14g  
- **Fat:** 0.2g
- **Serving:** 1 medium apple (182g)

## 🔧 How to Fix Your Current File

### **Step 1: Download Template**
1. Go to Food Database → Import
2. Click "Download Excel Template with Sample Data"
3. Use this as your base structure

### **Step 2: Copy Your Data**
1. Open your current food file
2. **Carefully map each column** to the correct header
3. **Double-check nutritional values** make sense

### **Step 3: Validate Data**
Before importing, verify:
- ✅ Protein values are realistic (0-50g for most foods)
- ✅ Calories match expected ranges
- ✅ Serving sizes are descriptive ("1 cup", not just "100")

## 🚫 Common Mistakes to Avoid

### **Wrong Column Order:**
❌ `name,calories,protein,carbs,fat` (Missing multilingual columns)
✅ `name,name_en,name_ar,name_ku_bahdini,name_ku_sorani,food_group,calories,protein,carbohydrates,fat...`

### **Wrong Data Types:**
❌ Protein: "60" (when it should be calories)
✅ Protein: "2.5" (realistic protein amount)

### **Missing Serving Information:**
❌ Serving Size: "100g" (for everything)
✅ Serving Size: "1 medium apple", "1 cup chopped", "2 tbsp"

## 📋 Import Process

### **Step 1: Clear Existing Data**
1. Go to Food Database
2. Click "Clear All Foods" button
3. Confirm deletion

### **Step 2: Import Corrected File**
1. Click "Import Foods"
2. Upload your corrected Excel file
3. Review import results

### **Step 3: Verify Results**
1. Check a few food items
2. Ensure nutritional values are realistic
3. Test in nutrition planning

## 🎯 Expected Results After Fix

### **Before (Wrong):**
- Green Salad: 96g protein, 134 calories ❌

### **After (Correct):**
- Green Salad: 1.4g protein, 15 calories ✅

## 📞 Need Help?

If you continue having issues:
1. Download the system template first
2. Copy your data column by column
3. Verify each nutritional value makes sense
4. Test with a small batch first

The corrected template file `food_import_template_corrected.csv` is ready for you to use as a reference!
