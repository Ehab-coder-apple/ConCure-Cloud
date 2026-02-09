<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        // Ensure Carbohydrates exists (global default)
        DB::table('food_groups')->updateOrInsert(
            ['name' => 'Carbohydrates', 'clinic_id' => null],
            [
                'name' => 'Carbohydrates',
                'clinic_id' => null,
                'name_translations' => json_encode(['en' => 'Carbohydrates', 'ar' => 'كربوهيدرات', 'ku' => 'کاربوهایدرات']),
                'description' => 'Breads, pasta, rice, and starchy foods',
                'description_translations' => json_encode(['en' => 'Breads, pasta, rice, and starchy foods', 'ar' => 'الخبز والمعكرونة والأرز والأطعمة النشوية', 'ku' => 'نان، پاستە، برنج و خواردنی نشاستەدار']),
                'color' => '#8BC34A',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Deactivate singular 'Protein' duplicate in global defaults (keep plural 'Proteins')
        DB::table('food_groups')
            ->whereNull('clinic_id')
            ->whereRaw('LOWER(name) = ?', ['protein'])
            ->update(['is_active' => false, 'updated_at' => $now]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive down to avoid data loss
    }
};
