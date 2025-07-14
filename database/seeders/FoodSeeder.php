<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foods = [
            // Vegetables (food_group_id = 1)
            [
                'name' => 'Broccoli',
                'name_translations' => json_encode([
                    'en' => 'Broccoli',
                    'ar' => 'بروكلي',
                    'ku_bahdini' => 'بروکۆلی',
                    'ku_sorani' => 'بروکۆلی'
                ]),
                'food_group_id' => 1,
                'calories' => 34,
                'protein' => 2.8,
                'carbohydrates' => 7,
                'fat' => 0.4,
                'fiber' => 2.6,
                'sugar' => 1.5,
                'sodium' => 33,
                'potassium' => 316,
                'calcium' => 47,
                'iron' => 0.7,
                'vitamin_c' => 89.2,
                'vitamin_a' => 623,
                'serving_size' => '1 cup chopped',
                'serving_weight' => 91,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spinach',
                'name_translations' => json_encode([
                    'en' => 'Spinach',
                    'ar' => 'سبانخ',
                    'ku_bahdini' => 'ئیسپەناخ',
                    'ku_sorani' => 'ئیسپەناخ'
                ]),
                'food_group_id' => 1,
                'calories' => 23,
                'protein' => 2.9,
                'carbohydrates' => 3.6,
                'fat' => 0.4,
                'fiber' => 2.2,
                'sugar' => 0.4,
                'sodium' => 79,
                'potassium' => 558,
                'calcium' => 99,
                'iron' => 2.7,
                'vitamin_c' => 28.1,
                'vitamin_a' => 9377,
                'serving_size' => '1 cup raw',
                'serving_weight' => 30,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Fruits (food_group_id = 2)
            [
                'name' => 'Apple',
                'name_translations' => json_encode([
                    'en' => 'Apple',
                    'ar' => 'تفاح',
                    'ku_bahdini' => 'سێو',
                    'ku_sorani' => 'سێو'
                ]),
                'food_group_id' => 2,
                'calories' => 52,
                'protein' => 0.3,
                'carbohydrates' => 14,
                'fat' => 0.2,
                'fiber' => 2.4,
                'sugar' => 10.4,
                'sodium' => 1,
                'potassium' => 107,
                'calcium' => 6,
                'iron' => 0.1,
                'vitamin_c' => 4.6,
                'vitamin_a' => 54,
                'serving_size' => '1 medium',
                'serving_weight' => 182,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Banana',
                'name_translations' => json_encode([
                    'en' => 'Banana',
                    'ar' => 'موز',
                    'ku_bahdini' => 'مۆز',
                    'ku_sorani' => 'مۆز'
                ]),
                'food_group_id' => 2,
                'calories' => 89,
                'protein' => 1.1,
                'carbohydrates' => 23,
                'fat' => 0.3,
                'fiber' => 2.6,
                'sugar' => 12.2,
                'sodium' => 1,
                'potassium' => 358,
                'calcium' => 5,
                'iron' => 0.3,
                'vitamin_c' => 8.7,
                'vitamin_a' => 64,
                'serving_size' => '1 medium',
                'serving_weight' => 118,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Grains (food_group_id = 3)
            [
                'name' => 'Brown Rice',
                'name_translations' => json_encode([
                    'en' => 'Brown Rice',
                    'ar' => 'أرز بني',
                    'ku_bahdini' => 'برنجی قاوەیی',
                    'ku_sorani' => 'برنجی قاوەیی'
                ]),
                'food_group_id' => 3,
                'calories' => 111,
                'protein' => 2.6,
                'carbohydrates' => 23,
                'fat' => 0.9,
                'fiber' => 1.8,
                'sugar' => 0.4,
                'sodium' => 5,
                'potassium' => 43,
                'calcium' => 10,
                'iron' => 0.4,
                'vitamin_c' => 0,
                'vitamin_a' => 0,
                'serving_size' => '1/2 cup cooked',
                'serving_weight' => 98,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Proteins (food_group_id = 4)
            [
                'name' => 'Chicken Breast',
                'name_translations' => json_encode([
                    'en' => 'Chicken Breast',
                    'ar' => 'صدر دجاج',
                    'ku' => 'سنگی مریشک'
                ]),
                'food_group_id' => 4,
                'calories' => 165,
                'protein' => 31,
                'carbohydrates' => 0,
                'fat' => 3.6,
                'fiber' => 0,
                'sugar' => 0,
                'sodium' => 74,
                'potassium' => 256,
                'calcium' => 15,
                'iron' => 0.9,
                'vitamin_c' => 0,
                'vitamin_a' => 21,
                'serving_size' => '3.5 oz',
                'serving_weight' => 100,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Dairy (food_group_id = 5)
            [
                'name' => 'Greek Yogurt',
                'name_translations' => json_encode([
                    'en' => 'Greek Yogurt',
                    'ar' => 'زبادي يوناني',
                    'ku_bahdini' => 'ماستی یۆنانی',
                    'ku_sorani' => 'ماستی یۆنانی'
                ]),
                'food_group_id' => 5,
                'calories' => 59,
                'protein' => 10,
                'carbohydrates' => 3.6,
                'fat' => 0.4,
                'fiber' => 0,
                'sugar' => 3.6,
                'sodium' => 36,
                'potassium' => 141,
                'calcium' => 110,
                'iron' => 0.1,
                'vitamin_c' => 0,
                'vitamin_a' => 27,
                'serving_size' => '1/2 cup',
                'serving_weight' => 100,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('foods')->insert($foods);
    }
}
