<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed vaccines (idempotent via updateOrInsert on code)
        $vaccines = [
            ['code' => 'BCG',         'global_name' => 'Bacillus Calmette-Guérin (BCG)',       'description' => 'Tuberculosis vaccine'],
            ['code' => 'OPV',         'global_name' => 'Oral Polio Vaccine (OPV)',              'description' => 'Poliomyelitis vaccine (oral)'],
            ['code' => 'HepB',        'global_name' => 'Hepatitis B (HepB)',                    'description' => 'Hepatitis B vaccine'],
            ['code' => 'Pentavalent', 'global_name' => 'Pentavalent (DPT-HepB-Hib)',           'description' => 'Diphtheria, Pertussis, Tetanus, Hepatitis B, Haemophilus influenzae type b'],
            ['code' => 'Rotavirus',   'global_name' => 'Rotavirus',                             'description' => 'Rotavirus gastroenteritis vaccine'],
            ['code' => 'Measles',     'global_name' => 'Measles',                               'description' => 'Measles vaccine (single antigen)'],
            ['code' => 'MMR',         'global_name' => 'Measles, Mumps, Rubella (MMR)',         'description' => 'Combined measles, mumps, and rubella vaccine'],
            ['code' => 'DTP',         'global_name' => 'Diphtheria, Tetanus, Pertussis (DTP)',  'description' => 'DTP booster vaccine'],
        ];

        foreach ($vaccines as $v) {
            DB::table('vaccines')->updateOrInsert(
                ['code' => $v['code']],
                array_merge($v, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Add Arabic translations
        $arabicNames = [
            'BCG'         => 'لقاح السل (بي سي جي)',
            'OPV'         => 'لقاح شلل الأطفال الفموي',
            'HepB'        => 'لقاح التهاب الكبد الوبائي ب',
            'Pentavalent' => 'اللقاح الخماسي',
            'Rotavirus'   => 'لقاح الروتا',
            'Measles'     => 'لقاح الحصبة',
            'MMR'         => 'لقاح الحصبة والنكاف والحصبة الألمانية',
            'DTP'         => 'لقاح الخناق والكزاز والسعال الديكي',
        ];

        foreach ($arabicNames as $code => $arName) {
            $vaccineId = DB::table('vaccines')->where('code', $code)->value('id');
            if ($vaccineId) {
                DB::table('vaccine_translations')->updateOrInsert(
                    ['vaccine_id' => $vaccineId, 'language_code' => 'ar'],
                    ['name' => $arName, 'description' => null, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 3. Create Iraq EPI 2025 schedule
        $iraqId = DB::table('countries')->where('iso_code', 'IQ')->value('id');
        if (!$iraqId) {
            return; // Country not seeded yet — skip
        }

        // Avoid duplicate schedule
        $existingSchedule = DB::table('vaccination_schedules')
            ->where('country_id', $iraqId)
            ->where('name', 'Iraq EPI 2025')
            ->first();

        if ($existingSchedule) {
            return; // Already seeded
        }

        $scheduleId = DB::table('vaccination_schedules')->insertGetId([
            'country_id'     => $iraqId,
            'name'           => 'Iraq EPI 2025',
            'version'        => '1.0',
            'is_default'     => true,
            'effective_from' => '2025-01-01',
            'is_active'      => true,
            'notes'          => 'Iraqi Expanded Programme on Immunization — 2025 national schedule',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Unset any other default for Iraq
        DB::table('vaccination_schedules')
            ->where('country_id', $iraqId)
            ->where('id', '!=', $scheduleId)
            ->update(['is_default' => false]);

        // 4. Insert schedule items
        // Booster mapping: "booster1" → 4, "booster2" → 5 (sequential after regular doses)
        $items = [
            // Birth (0 days)
            ['code' => 'BCG',         'dose' => 1, 'age_value' => 0, 'age_unit' => 'days',   'grace' => 7],
            ['code' => 'OPV',         'dose' => 0, 'age_value' => 0, 'age_unit' => 'days',   'grace' => 7],
            ['code' => 'HepB',        'dose' => 1, 'age_value' => 0, 'age_unit' => 'days',   'grace' => 7],
            // 2 months
            ['code' => 'Pentavalent', 'dose' => 1, 'age_value' => 2, 'age_unit' => 'months', 'grace' => 14],
            ['code' => 'OPV',         'dose' => 1, 'age_value' => 2, 'age_unit' => 'months', 'grace' => 14],
            ['code' => 'Rotavirus',   'dose' => 1, 'age_value' => 2, 'age_unit' => 'months', 'grace' => 14],
            // 4 months
            ['code' => 'Pentavalent', 'dose' => 2, 'age_value' => 4, 'age_unit' => 'months', 'grace' => 14],
            ['code' => 'OPV',         'dose' => 2, 'age_value' => 4, 'age_unit' => 'months', 'grace' => 14],
            ['code' => 'Rotavirus',   'dose' => 2, 'age_value' => 4, 'age_unit' => 'months', 'grace' => 14],
            // 6 months
            ['code' => 'Pentavalent', 'dose' => 3, 'age_value' => 6, 'age_unit' => 'months', 'grace' => 14],
            ['code' => 'OPV',         'dose' => 3, 'age_value' => 6, 'age_unit' => 'months', 'grace' => 14],
            ['code' => 'Rotavirus',   'dose' => 3, 'age_value' => 6, 'age_unit' => 'months', 'grace' => 14],
            // 9 months
            ['code' => 'Measles',     'dose' => 1, 'age_value' => 9, 'age_unit' => 'months', 'grace' => 30],
            // 15 months
            ['code' => 'MMR',         'dose' => 1, 'age_value' => 15, 'age_unit' => 'months', 'grace' => 30],
            // 18 months (boosters)
            ['code' => 'DTP',         'dose' => 4, 'age_value' => 18, 'age_unit' => 'months', 'grace' => 30],
            ['code' => 'OPV',         'dose' => 4, 'age_value' => 18, 'age_unit' => 'months', 'grace' => 30],
            // 5 years (boosters)
            ['code' => 'DTP',         'dose' => 5, 'age_value' => 5, 'age_unit' => 'years',  'grace' => 60],
            ['code' => 'MMR',         'dose' => 2, 'age_value' => 5, 'age_unit' => 'years',  'grace' => 60],
        ];

        foreach ($items as $sort => $item) {
            $vaccineId = DB::table('vaccines')->where('code', $item['code'])->value('id');
            if (!$vaccineId) continue;

            DB::table('schedule_items')->insert([
                'schedule_id'          => $scheduleId,
                'vaccine_id'           => $vaccineId,
                'dose_number'          => $item['dose'],
                'recommended_age_value' => $item['age_value'],
                'recommended_age_unit'  => $item['age_unit'],
                'min_age_value'        => null,
                'max_age_value'        => null,
                'grace_period_days'    => $item['grace'],
                'is_mandatory'         => true,
                'sort_order'           => $sort,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }

    public function down(): void
    {
        $iraqId = DB::table('countries')->where('iso_code', 'IQ')->value('id');
        if ($iraqId) {
            $scheduleId = DB::table('vaccination_schedules')
                ->where('country_id', $iraqId)
                ->where('name', 'Iraq EPI 2025')
                ->value('id');

            if ($scheduleId) {
                DB::table('schedule_items')->where('schedule_id', $scheduleId)->delete();
                DB::table('vaccination_schedules')->where('id', $scheduleId)->delete();
            }
        }

        // Don't delete vaccines — they may be used by other schedules
    }
};

