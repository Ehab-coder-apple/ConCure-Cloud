<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodGroups = [
            [
                'name' => 'Vegetables',
                'name_translations' => json_encode([
                    'en' => 'Vegetables',
                    'ar' => 'خضروات',
                    'ku' => 'سەوزە'
                ]),
                'description' => 'Fresh and cooked vegetables',
                'description_translations' => json_encode([
                    'en' => 'Fresh and cooked vegetables',
                    'ar' => 'خضروات طازجة ومطبوخة',
                    'ku' => 'سەوزەی تازە و کوڵاو'
                ]),
                'color' => '#4CAF50',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fruits',
                'name_translations' => json_encode([
                    'en' => 'Fruits',
                    'ar' => 'فواكه',
                    'ku' => 'میوە'
                ]),
                'description' => 'Fresh and dried fruits',
                'description_translations' => json_encode([
                    'en' => 'Fresh and dried fruits',
                    'ar' => 'فواكه طازجة ومجففة',
                    'ku' => 'میوەی تازە و وشک'
                ]),
                'color' => '#FF9800',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grains',
                'name_translations' => json_encode([
                    'en' => 'Grains',
                    'ar' => 'حبوب',
                    'ku' => 'دانەوێڵە'
                ]),
                'description' => 'Rice, wheat, oats, and other grains',
                'description_translations' => json_encode([
                    'en' => 'Rice, wheat, oats, and other grains',
                    'ar' => 'أرز، قمح، شوفان، وحبوب أخرى',
                    'ku' => 'برنج، گەنم، جۆ و دانەوێڵەی تر'
                ]),
                'color' => '#8BC34A',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Proteins',
                'name_translations' => json_encode([
                    'en' => 'Proteins',
                    'ar' => 'بروتينات',
                    'ku' => 'پرۆتین'
                ]),
                'description' => 'Meat, fish, eggs, and legumes',
                'description_translations' => json_encode([
                    'en' => 'Meat, fish, eggs, and legumes',
                    'ar' => 'لحوم، أسماك، بيض، وبقوليات',
                    'ku' => 'گۆشت، ماسی، هێلکە و لۆبیا'
                ]),
                'color' => '#F44336',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dairy',
                'name_translations' => json_encode([
                    'en' => 'Dairy',
                    'ar' => 'منتجات الألبان',
                    'ku' => 'شیر و بەرهەمەکانی'
                ]),
                'description' => 'Milk, cheese, yogurt, and dairy products',
                'description_translations' => json_encode([
                    'en' => 'Milk, cheese, yogurt, and dairy products',
                    'ar' => 'حليب، جبن، زبادي، ومنتجات الألبان',
                    'ku' => 'شیر، پەنیر، ماست و بەرهەمی شیر'
                ]),
                'color' => '#2196F3',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fats & Oils',
                'name_translations' => json_encode([
                    'en' => 'Fats & Oils',
                    'ar' => 'دهون وزيوت',
                    'ku' => 'چەوری و ڕۆن'
                ]),
                'description' => 'Cooking oils, butter, nuts, and seeds',
                'description_translations' => json_encode([
                    'en' => 'Cooking oils, butter, nuts, and seeds',
                    'ar' => 'زيوت الطبخ، زبدة، مكسرات، وبذور',
                    'ku' => 'ڕۆنی چێشت، کەرە، گوێز و تۆو'
                ]),
                'color' => '#FFEB3B',
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('food_groups')->insert($foodGroups);
    }
}
