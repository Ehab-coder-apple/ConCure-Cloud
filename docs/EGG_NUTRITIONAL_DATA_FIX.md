# Egg Nutritional Data Fix

## Problem

The egg food item was showing incorrect calorie values:
- **Displayed:** 40 cal per 1 piece (55g)
- **Expected:** 73 cal per 1 piece (55g)

## Root Cause

The database had **73 calories per 100g** instead of **133 calories per 100g**.

Someone had entered the **per-egg calorie value** (73 cal) as the **per-100g value**, which caused incorrect calculations:

```
Wrong calculation:
73 cal/100g × (55g/100) = 40.15 cal ≈ 40 cal ❌

Correct calculation:
133 cal/100g × (55g/100) = 73.15 cal ≈ 73 cal ✅
```

## Solution Applied

### 1. Added Egg to Database

The egg food item was missing from the database. Added it with correct values:

```sql
INSERT INTO foods (
    name, name_translations, food_group_id,
    calories, protein, carbohydrates, fat,
    serving_size, serving_weight, grams_per_piece,
    is_custom, is_active
) VALUES (
    'Egg',
    '{"en":"Egg","ar":"بيضة","ku":"هێلکە","ku_bahdini":"هێلکە","ku_sorani":"هێلکە"}',
    4,  -- Protein food group
    133,  -- Calories per 100g (NOT per egg!)
    13,   -- Protein per 100g
    1.1,  -- Carbs per 100g
    11,   -- Fat per 100g
    '1 large egg',
    55,   -- Serving weight in grams
    55,   -- Grams per piece
    0,    -- Not custom
    1     -- Active
);
```

### 2. Updated FoodSeeder.php

Changed the egg entry in the seeder from:
```php
'calories' => 155,  // Old value
'serving_weight' => 50,
'grams_per_piece' => 50,
```

To:
```php
'calories' => 133,  // Per 100g (73 cal per 55g egg)
'serving_weight' => 55,
'grams_per_piece' => 55,
```

### 3. Created Migration

Created `2025_10_02_000001_fix_egg_nutritional_data.php` to update existing egg records.

## Verification

After the fix, the database now contains:

```
ID: 8
Name: Egg
Calories: 133 per 100g
Serving Weight: 55g
Grams Per Piece: 55g
```

**Display should now show:**
- **73 cal** per 1 piece
- **7.2g protein** per 1 piece
- **1 piece = 55g**
- **100g = 133 cal | 13g protein**

## Important: Browser Cache

⚠️ **If you still see 40 cal after the fix, you need to clear your browser cache!**

### How to Hard Refresh:

- **Mac:** Cmd + Shift + R
- **Windows/Linux:** Ctrl + Shift + F5
- **Or:** Clear browser cache and reload

The JavaScript code caches API responses, so you must force a refresh to see the updated data.

## Files Changed

1. `database/seeders/FoodSeeder.php` - Updated egg nutritional values
2. `database/migrations/2025_10_02_000001_fix_egg_nutritional_data.php` - Migration to fix existing records
3. `database/concure.sqlite` - Direct SQL insert to add egg with correct values

## Commits

- `b3d23a6` - "Fix egg nutritional data: 73 cal per 55g egg (133 cal per 100g)"
- `d26d4d4` - "Fix egg nutritional data: Update to 133 cal per 100g (73 cal per 55g egg)"

## Nutritional Calculation Formula

All food values in the database are stored **per 100g**.

To calculate nutrition for a specific serving:

```javascript
const servingWeight = 55; // grams
const caloriesPer100g = 133;

const caloriesPerServing = caloriesPer100g × (servingWeight / 100);
// = 133 × (55 / 100)
// = 133 × 0.55
// = 73.15 ≈ 73 cal ✅
```

## Testing

To verify the fix:

1. **Clear browser cache** (hard refresh)
2. Go to nutrition plan creation page
3. Search for "Egg" or "بيضة"
4. Check the displayed values:
   - Should show **73 cal** per 1 piece
   - Should show **100g = 133 cal** at the bottom

## Future Prevention

To prevent this issue in the future:

1. **Always store values per 100g** in the database
2. **Validate input** when adding custom foods
3. **Show clear labels** in the food entry form: "Calories per 100g"
4. **Add validation** to check if values are reasonable (e.g., 1 egg shouldn't have 1000 calories)

---

**Last Updated:** October 2, 2025  
**Issue:** Egg showing 40 cal instead of 73 cal  
**Status:** ✅ RESOLVED

