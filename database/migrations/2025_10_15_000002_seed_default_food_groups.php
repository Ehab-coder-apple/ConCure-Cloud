<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $groups = [
            [
                'name' => 'Vegetables',
                'name_translations' => json_encode(['en'=>'Vegetables','ar'=>'خضروات','ku'=>'سەوزە']),
                'description' => 'Fresh and cooked vegetables',
                'description_translations' => json_encode(['en'=>'Fresh and cooked vegetables','ar'=>'خضروات طازجة ومطبوخة','ku'=>'سەوزەی تازە و کوڵاو']),
                'color' => '#4CAF50', 'sort_order' => 1,
            ],
            [
                'name' => 'Fruits',
                'name_translations' => json_encode(['en'=>'Fruits','ar'=>'فواكه','ku'=>'میوە']),
                'description' => 'Fresh and dried fruits',
                'description_translations' => json_encode(['en'=>'Fresh and dried fruits','ar'=>'فواكه طازجة ومجففة','ku'=>'میوەی تازە و وشک']),
                'color' => '#FF9800', 'sort_order' => 2,
            ],
            [
                'name' => 'Grains',
                'name_translations' => json_encode(['en'=>'Grains','ar'=>'حبوب','ku'=>'دانەوێڵە']),
                'description' => 'Rice, wheat, oats, and other grains',
                'description_translations' => json_encode(['en'=>'Rice, wheat, oats, and other grains','ar'=>'أرز، قمح، شوفان، وحبوب أخرى','ku'=>'برنج، گەنم، جۆ و دانەوێڵەی تر']),
                'color' => '#8BC34A', 'sort_order' => 3,
            ],
            [
                'name' => 'Carbohydrates',
                'name_translations' => json_encode(['en'=>'Carbohydrates','ar'=>'كربوهيدرات','ku'=>'کاربوهایدرات']),
                'description' => 'Breads, pasta, rice, and starchy foods',
                'description_translations' => json_encode(['en'=>'Breads, pasta, rice, and starchy foods','ar'=>'الخبز والمعكرونة والأرز والأطعمة النشوية','ku'=>'نان، پاستە، برنج و خواردنی نشاستەدار']),
                'color' => '#8BC34A', 'sort_order' => 3,
            ],

            [
                'name' => 'Proteins',
                'name_translations' => json_encode(['en'=>'Proteins','ar'=>'بروتينات','ku'=>'پرۆتین']),
                'description' => 'Meat, fish, eggs, and legumes',
                'description_translations' => json_encode(['en'=>'Meat, fish, eggs, and legumes','ar'=>'لحوم، أسماك، بيض، وبقوليات','ku'=>'گۆشت، ماسی، هێلکە و لۆبیا']),
                'color' => '#F44336', 'sort_order' => 4,
            ],
            [
                'name' => 'Dairy',
                'name_translations' => json_encode(['en'=>'Dairy','ar'=>'منتجات الألبان','ku'=>'شیر و بەرهەمەکانی']),
                'description' => 'Milk, cheese, yogurt, and dairy products',
                'description_translations' => json_encode(['en'=>'Milk, cheese, yogurt, and dairy products','ar'=>'حليب، جبن، زبادي، ومنتجات الألبان','ku'=>'شیر، پەنیر، ماست و بەرهەمی شیر']),
                'color' => '#2196F3', 'sort_order' => 5,
            ],
            [
                'name' => 'Fats & Oils',
                'name_translations' => json_encode(['en'=>'Fats & Oils','ar'=>'دهون وزيوت','ku'=>'چەوری و ڕۆن']),
                'description' => 'Cooking oils, butter, nuts, and seeds',
                'description_translations' => json_encode(['en'=>'Cooking oils, butter, nuts, and seeds','ar'=>'زيوت الطبخ، زبدة، مكسرات، وبذور','ku'=>'ڕۆنی چێشت، کەرە، گوێز و تۆو']),
                'color' => '#FFEB3B', 'sort_order' => 6,
            ],
            [
                'name' => 'Sweets',
                'name_translations' => json_encode(['en'=>'Sweets','ar'=>'حلويات','ku'=>'شیرینی']),
                'description' => 'Sugary foods and desserts',
                'description_translations' => json_encode(['en'=>'Sugary foods and desserts','ar'=>'أطعمة سكرية وحلويات','ku'=>'خواردنی شیرین و شیرینی']),
                'color' => '#E91E63', 'sort_order' => 7,
            ],
            [
                'name' => 'Beverages',
                'name_translations' => json_encode(['en'=>'Beverages','ar'=>'مشروبات','ku'=>'خواردنەوە']),
                'description' => 'Drinks including water, juice, tea, coffee',
                'description_translations' => json_encode(['en'=>'Drinks including water, juice, tea, coffee','ar'=>'مشروبات مثل الماء والعصير والشاي والقهوة','ku'=>'خواردنەوەکان وەک ئاو، عەصیر، چای و قاوی']),
                'color' => '#00BCD4', 'sort_order' => 8,
            ],
        ];

        foreach ($groups as $g) {
            DB::table('food_groups')->updateOrInsert(
                ['name' => $g['name'], 'clinic_id' => null],
                array_merge($g, ['clinic_id' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        // Do not remove groups on down; keep existing data safe.
    }
};

