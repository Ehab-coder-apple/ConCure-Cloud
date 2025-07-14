<?php

namespace App\Imports;

use App\Models\Food;
use App\Models\FoodGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class FoodsImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        
        foreach ($rows as $row) {
            try {
                // Validate row data
                $validator = Validator::make($row->toArray(), [
                    'name' => 'required|string|max:255',
                    'name_en' => 'nullable|string|max:255',
                    'name_ar' => 'nullable|string|max:255',
                    'name_ku_bahdini' => 'nullable|string|max:255',
                    'name_ku_sorani' => 'nullable|string|max:255',
                    'name_ku' => 'nullable|string|max:255', // Legacy support
                    'food_group' => 'nullable|string|max:255',
                    'calories' => 'required|numeric|min:0|max:9999',
                    'protein' => 'nullable|numeric|min:0|max:999',
                    'carbohydrates' => 'nullable|numeric|min:0|max:999',
                    'fat' => 'nullable|numeric|min:0|max:999',
                    'fiber' => 'nullable|numeric|min:0|max:999',
                    'sugar' => 'nullable|numeric|min:0|max:999',
                    'sodium' => 'nullable|numeric|min:0|max:9999',
                    'serving_size' => 'nullable|max:255',
                    'serving_weight' => 'nullable|numeric|min:0|max:9999',
                    'description' => 'nullable|string|max:1000',
                    'description_en' => 'nullable|string|max:1000',
                    'description_ar' => 'nullable|string|max:1000',
                    'description_ku_bahdini' => 'nullable|string|max:1000',
                    'description_ku_sorani' => 'nullable|string|max:1000',
                    'description_ku' => 'nullable|string|max:1000', // Legacy support
                ]);

                if ($validator->fails()) {
                    $this->errors[] = "Row with name '{$row['name']}': " . implode(', ', $validator->errors()->all());
                    $this->skippedCount++;
                    continue;
                }

                // Find or create food group first - always ensure we have a food group
                $foodGroupName = !empty($row['food_group']) ? trim($row['food_group']) : 'General';

                // Try to find existing food group first
                $foodGroup = FoodGroup::where('name', $foodGroupName)->first();

                if (!$foodGroup) {
                    // Create new food group with proper translations
                    try {
                        $foodGroup = FoodGroup::create([
                            'name' => $foodGroupName,
                            'name_translations' => [
                                'en' => $foodGroupName,
                                'ar' => $foodGroupName,
                                'ku' => $foodGroupName
                            ],
                            'description' => 'Auto-created from food import',
                            'is_active' => true,
                            'sort_order' => 999
                        ]);
                    } catch (\Exception $e) {
                        // If food group creation fails, use default "General" group
                        $foodGroup = FoodGroup::where('name', 'General')->first();
                        if (!$foodGroup) {
                            // Create General group as fallback
                            $foodGroup = FoodGroup::create([
                                'name' => 'General',
                                'name_translations' => [
                                    'en' => 'General',
                                    'ar' => 'عام',
                                    'ku' => 'گشتی'
                                ],
                                'description' => 'General food items',
                                'is_active' => true,
                                'sort_order' => 1
                            ]);
                        }
                    }
                }

                $foodGroupId = $foodGroup->id;

                // Check if food already exists (check by name and food_group_id due to unique constraint)
                $foodName = trim($row['name']);
                $existingFood = Food::where('name', $foodName)
                    ->where('food_group_id', $foodGroupId)
                    ->first();

                if ($existingFood) {
                    $this->skippedCount++;
                    continue;
                }

                // Additional check for similar foods with different calories to avoid confusion
                $similarFood = Food::where('name', $foodName)
                    ->where('food_group_id', '!=', $foodGroupId)
                    ->first();

                if ($similarFood) {
                    // Food exists in different group, still create but note it
                    // This is allowed by our constraint but worth noting
                }

                // Prepare name translations
                $nameTranslations = [];
                if (!empty($row['name_en'])) {
                    $nameTranslations['en'] = trim($row['name_en']);
                }
                if (!empty($row['name_ar'])) {
                    $nameTranslations['ar'] = trim($row['name_ar']);
                }
                if (!empty($row['name_ku_bahdini'])) {
                    $nameTranslations['ku_bahdini'] = trim($row['name_ku_bahdini']);
                }
                if (!empty($row['name_ku_sorani'])) {
                    $nameTranslations['ku_sorani'] = trim($row['name_ku_sorani']);
                }
                // Legacy support for old 'ku' column
                if (!empty($row['name_ku']) && empty($nameTranslations['ku_bahdini']) && empty($nameTranslations['ku_sorani'])) {
                    $nameTranslations['ku_bahdini'] = trim($row['name_ku']);
                    $nameTranslations['ku_sorani'] = trim($row['name_ku']);
                }

                // Prepare description translations
                $descriptionTranslations = [];
                if (!empty($row['description_en'])) {
                    $descriptionTranslations['en'] = trim($row['description_en']);
                }
                if (!empty($row['description_ar'])) {
                    $descriptionTranslations['ar'] = trim($row['description_ar']);
                }
                if (!empty($row['description_ku_bahdini'])) {
                    $descriptionTranslations['ku_bahdini'] = trim($row['description_ku_bahdini']);
                }
                if (!empty($row['description_ku_sorani'])) {
                    $descriptionTranslations['ku_sorani'] = trim($row['description_ku_sorani']);
                }
                // Legacy support for old 'ku' column
                if (!empty($row['description_ku']) && empty($descriptionTranslations['ku_bahdini']) && empty($descriptionTranslations['ku_sorani'])) {
                    $descriptionTranslations['ku_bahdini'] = trim($row['description_ku']);
                    $descriptionTranslations['ku_sorani'] = trim($row['description_ku']);
                }

                // Create the food with proper data handling and error catching
                try {
                    Food::create([
                        'name' => trim($row['name']),
                        'name_translations' => !empty($nameTranslations) ? $nameTranslations : ['en' => trim($row['name'])],
                        'food_group_id' => $foodGroupId,
                        'calories' => (float)($row['calories'] ?? 0),
                        'protein' => (float)($row['protein'] ?? 0),
                        'carbohydrates' => (float)($row['carbohydrates'] ?? 0),
                        'fat' => (float)($row['fat'] ?? 0),
                        'fiber' => (float)($row['fiber'] ?? 0),
                        'sugar' => (float)($row['sugar'] ?? 0),
                        'sodium' => (float)($row['sodium'] ?? 0),
                        'potassium' => 0,
                        'calcium' => 0,
                        'iron' => 0,
                        'vitamin_c' => 0,
                        'vitamin_a' => 0,
                        'serving_size' => $this->parseServingSize($row['serving_size'] ?? '100g'),
                        'serving_weight' => $this->parseServingWeight($row['serving_size'] ?? '100g', $row['serving_weight'] ?? null),
                        'description' => $row['description'] ?? null,
                        'description_translations' => !empty($descriptionTranslations) ? $descriptionTranslations : null,
                        'is_custom' => true,
                        'clinic_id' => $user->clinic_id ?? null,
                        'created_by' => $user->id,
                        'is_active' => true,
                    ]);

                    $this->importedCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle database constraint violations
                    if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                        $this->errors[] = "Row with name '{$row['name']}': Duplicate entry (food already exists)";
                    } elseif (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                        $this->errors[] = "Row with name '{$row['name']}': Database constraint violation - " . $e->getMessage();
                    } else {
                        $this->errors[] = "Row with name '{$row['name']}': Database error - " . $e->getMessage();
                    }
                    $this->skippedCount++;
                    continue;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Row with name '{$row['name']}': " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Parse serving size to ensure it's properly formatted
     */
    private function parseServingSize($servingSize): string
    {
        if (empty($servingSize)) {
            return '100g';
        }

        $servingSize = trim($servingSize);

        // If it's just a number, assume grams
        if (is_numeric($servingSize)) {
            return $servingSize . 'g';
        }

        // Common serving size formats
        $commonSizes = [
            'piece' => 'piece',
            'pieces' => 'pieces',
            'slice' => 'slice',
            'slices' => 'slices',
            'cup' => 'cup',
            'cups' => 'cups',
            'tbsp' => 'tbsp',
            'tablespoon' => 'tbsp',
            'tablespoons' => 'tbsp',
            'tsp' => 'tsp',
            'teaspoon' => 'tsp',
            'teaspoons' => 'tsp',
            'spoon' => 'spoon',
            'spoons' => 'spoons',
            'bowl' => 'bowl',
            'bowls' => 'bowls',
            'glass' => 'glass',
            'glasses' => 'glasses',
            'handful' => 'handful',
            'medium' => 'medium',
            'large' => 'large',
            'small' => 'small'
        ];

        // Check if serving size contains common units
        $lowerSize = strtolower($servingSize);
        foreach ($commonSizes as $key => $value) {
            if (strpos($lowerSize, $key) !== false) {
                return $servingSize; // Return as-is if it contains recognized units
            }
        }

        // If it contains 'g', 'ml', 'oz', etc., return as-is
        if (preg_match('/\d+\s*(g|gram|grams|ml|milliliter|milliliters|oz|ounce|ounces|lb|pound|pounds|kg|kilogram|kilograms)/', $lowerSize)) {
            return $servingSize;
        }

        // Default fallback
        return $servingSize ?: '100g';
    }

    /**
     * Parse serving weight from serving size or explicit weight
     */
    private function parseServingWeight($servingSize, $explicitWeight): float
    {
        // If explicit weight is provided, use it
        if (!empty($explicitWeight) && is_numeric($explicitWeight)) {
            return (float)$explicitWeight;
        }

        if (empty($servingSize)) {
            return 100.0;
        }

        $servingSize = trim(strtolower($servingSize));

        // Extract numeric value from serving size
        if (preg_match('/(\d+(?:\.\d+)?)\s*(g|gram|grams)/', $servingSize, $matches)) {
            return (float)$matches[1];
        }

        // Convert common measurements to grams (approximate)
        $conversions = [
            'cup' => 240,
            'tbsp' => 15,
            'tablespoon' => 15,
            'tsp' => 5,
            'teaspoon' => 5,
            'spoon' => 15, // Assume tablespoon
            'piece' => 100, // Default piece weight
            'slice' => 30,  // Default slice weight
            'bowl' => 200,  // Default bowl weight
            'glass' => 250, // Default glass weight
            'handful' => 30, // Default handful weight
            'medium' => 150, // Medium size
            'large' => 200,  // Large size
            'small' => 100   // Small size
        ];

        // Check for quantity + unit (e.g., "2 cups", "1 piece")
        if (preg_match('/(\d+(?:\.\d+)?)\s*(\w+)/', $servingSize, $matches)) {
            $quantity = (float)$matches[1];
            $unit = $matches[2];

            if (isset($conversions[$unit])) {
                return $quantity * $conversions[$unit];
            }
        }

        // Check for just unit (e.g., "cup", "piece")
        foreach ($conversions as $unit => $weight) {
            if (strpos($servingSize, $unit) !== false) {
                return $weight;
            }
        }

        // If it's just a number, assume grams
        if (preg_match('/(\d+(?:\.\d+)?)/', $servingSize, $matches)) {
            return (float)$matches[1];
        }

        // Default fallback
        return 100.0;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get the expected column headers for the import template
     */
    public static function getExpectedHeaders(): array
    {
        return [
            'name' => 'Food Name (Required)',
            'name_en' => 'Name in English (Optional)',
            'name_ar' => 'Name in Arabic (Optional)',
            'name_ku_bahdini' => 'Name in Kurdish Bahdini (Optional)',
            'name_ku_sorani' => 'Name in Kurdish Sorani (Optional)',
            'food_group' => 'Food Group (Optional)',
            'calories' => 'Calories (Required)',
            'protein' => 'Protein (g) (Optional)',
            'carbohydrates' => 'Carbohydrates (g) (Optional)',
            'fat' => 'Fat (g) (Optional)',
            'fiber' => 'Fiber (g) (Optional)',
            'sugar' => 'Sugar (g) (Optional)',
            'sodium' => 'Sodium (mg) (Optional)',
            'serving_size' => 'Serving Size (e.g., 100g, 1 cup, 2 pieces, 1 tbsp, 1 slice)',
            'serving_weight' => 'Serving Weight in grams (Optional - auto-calculated if not provided)',
            'description' => 'Description (Optional)',
            'description_en' => 'Description in English (Optional)',
            'description_ar' => 'Description in Arabic (Optional)',
            'description_ku_bahdini' => 'Description in Kurdish Bahdini (Optional)',
            'description_ku_sorani' => 'Description in Kurdish Sorani (Optional)',
        ];
    }

    /**
     * Get sample data for the import template
     */
    public static function getSampleData(): array
    {
        return [
            [
                'name' => 'Chicken Breast (Skinless)',
                'name_en' => 'Chicken Breast (Skinless)',
                'name_ar' => 'صدر دجاج (بدون جلد)',
                'name_ku_bahdini' => 'سنگی مریشک (بێ پێست)',
                'name_ku_sorani' => 'سنگی مریشک (بێ پێست)',
                'food_group' => 'Proteins',
                'calories' => 165,
                'protein' => 31,
                'carbohydrates' => 0,
                'fat' => 3.6,
                'fiber' => 0,
                'sugar' => 0,
                'sodium' => 74,
                'serving_size' => '100g',
                'serving_weight' => 100,
                'description' => 'Lean protein source, boneless and skinless',
                'description_en' => 'Lean protein source, boneless and skinless',
                'description_ar' => 'مصدر بروتين قليل الدهون، بدون عظم وبدون جلد',
                'description_ku_bahdini' => 'سەرچاوەی پرۆتینی کەم چەوری، بێ ئێسک و بێ پێست',
                'description_ku_sorani' => 'سەرچاوەی پرۆتینی کەم چەوری، بێ ئێسک و بێ پێست'
            ],
            [
                'name' => 'Brown Rice (Cooked)',
                'name_en' => 'Brown Rice (Cooked)',
                'name_ar' => 'أرز بني (مطبوخ)',
                'name_ku_bahdini' => 'برنج قاوەیی (کوڵاو)',
                'name_ku_sorani' => 'برنج قاوەیی (کوڵاو)',
                'food_group' => 'Grains',
                'calories' => 111,
                'protein' => 2.6,
                'carbohydrates' => 23,
                'fat' => 0.9,
                'fiber' => 1.8,
                'sugar' => 0.4,
                'sodium' => 5,
                'serving_size' => '1 cup',
                'serving_weight' => 240,
                'description' => 'Whole grain rice, cooked',
                'description_en' => 'Whole grain rice, cooked',
                'description_ar' => 'أرز حبوب كاملة، مطبوخ',
                'description_ku_bahdini' => 'برنجی دانەی تەواو، کوڵاو',
                'description_ku_sorani' => 'برنجی دانەی تەواو، کوڵاو'
            ],
            [
                'name' => 'Broccoli (Raw)',
                'name_en' => 'Broccoli (Raw)',
                'name_ar' => 'بروكلي (نيء)',
                'name_ku_bahdini' => 'بروکۆلی (خاو)',
                'name_ku_sorani' => 'بروکۆلی (خاو)',
                'food_group' => 'Vegetables',
                'calories' => 34,
                'protein' => 2.8,
                'carbohydrates' => 7,
                'fat' => 0.4,
                'fiber' => 2.6,
                'sugar' => 1.5,
                'sodium' => 33,
                'serving_size' => '1 cup chopped',
                'serving_weight' => 91,
                'description' => 'Fresh broccoli florets',
                'description_en' => 'Fresh broccoli florets',
                'description_ar' => 'زهيرات البروكلي الطازجة',
                'description_ku_bahdini' => 'گوڵی بروکۆلی تازە',
                'description_ku_sorani' => 'گوڵی بروکۆلی تازە'
            ],
            [
                'name' => 'Banana (Medium)',
                'name_en' => 'Banana (Medium)',
                'name_ar' => 'موز (متوسط)',
                'name_ku_bahdini' => 'مۆز (ناوەند)',
                'name_ku_sorani' => 'مۆز (ناوەند)',
                'food_group' => 'Fruits',
                'calories' => 89,
                'protein' => 1.1,
                'carbohydrates' => 23,
                'fat' => 0.3,
                'fiber' => 2.6,
                'sugar' => 12,
                'sodium' => 1,
                'serving_size' => '1 medium piece',
                'serving_weight' => 118,
                'description' => 'Fresh medium-sized banana',
                'description_en' => 'Fresh medium-sized banana',
                'description_ar' => 'موز طازج متوسط الحجم',
                'description_ku_bahdini' => 'مۆزی تازەی قەبارەی ناوەند',
                'description_ku_sorani' => 'مۆزی تازەی قەبارەی ناوەند'
            ],
            [
                'name' => 'Olive Oil',
                'name_en' => 'Olive Oil',
                'name_ar' => 'زيت الزيتون',
                'name_ku_bahdini' => 'ڕۆنی زەیتوون',
                'name_ku_sorani' => 'ڕۆنی زەیتوون',
                'food_group' => 'Fats & Oils',
                'calories' => 119,
                'protein' => 0,
                'carbohydrates' => 0,
                'fat' => 13.5,
                'fiber' => 0,
                'sugar' => 0,
                'sodium' => 0,
                'serving_size' => '1 tbsp',
                'serving_weight' => 14,
                'description' => 'Extra virgin olive oil',
                'description_en' => 'Extra virgin olive oil',
                'description_ar' => 'زيت زيتون بكر ممتاز',
                'description_ku_bahdini' => 'ڕۆنی زەیتوونی پاک',
                'description_ku_sorani' => 'ڕۆنی زەیتوونی پاک'
            ]
        ];
    }
}
