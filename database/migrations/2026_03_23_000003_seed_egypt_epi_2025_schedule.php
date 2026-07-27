<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vaccines already seeded by Iraq EPI migration — no need to re-seed.

        // 1. Find Egypt country
        $egyptId = DB::table('countries')->where('iso_code', 'EG')->value('id');
        if (!$egyptId) {
            return; // Country not seeded yet — skip
        }

        // Avoid duplicate schedule
        $existingSchedule = DB::table('vaccination_schedules')
            ->where('country_id', $egyptId)
            ->where('name', 'Egypt EPI 2025')
            ->first();

        if ($existingSchedule) {
            return; // Already seeded
        }

        // 2. Create Egypt EPI 2025 schedule
        $scheduleId = DB::table('vaccination_schedules')->insertGetId([
            'country_id'     => $egyptId,
            'name'           => 'Egypt EPI 2025',
            'version'        => '1.0',
            'is_default'     => true,
            'effective_from' => '2025-01-01',
            'is_active'      => true,
            'notes'          => 'Egyptian Expanded Programme on Immunization — 2025 national schedule',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Unset any other default for Egypt
        DB::table('vaccination_schedules')
            ->where('country_id', $egyptId)
            ->where('id', '!=', $scheduleId)
            ->update(['is_default' => false]);

        // 3. Insert schedule items
        // Booster mapping: "booster1" → 4, "booster2" → 5
        $items = [
            // Birth (0 days)
            ['code' => 'BCG',         'dose' => 1, 'age_value' => 0,  'age_unit' => 'days',   'grace' => 7],
            ['code' => 'OPV',         'dose' => 0, 'age_value' => 0,  'age_unit' => 'days',   'grace' => 7],
            ['code' => 'HepB',        'dose' => 1, 'age_value' => 0,  'age_unit' => 'days',   'grace' => 7],
            // 2 months
            ['code' => 'Pentavalent', 'dose' => 1, 'age_value' => 2,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'OPV',         'dose' => 1, 'age_value' => 2,  'age_unit' => 'months', 'grace' => 14],
            // 4 months
            ['code' => 'Pentavalent', 'dose' => 2, 'age_value' => 4,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'OPV',         'dose' => 2, 'age_value' => 4,  'age_unit' => 'months', 'grace' => 14],
            // 6 months
            ['code' => 'Pentavalent', 'dose' => 3, 'age_value' => 6,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'OPV',         'dose' => 3, 'age_value' => 6,  'age_unit' => 'months', 'grace' => 14],
            // 9 months
            ['code' => 'Measles',     'dose' => 1, 'age_value' => 9,  'age_unit' => 'months', 'grace' => 30],
            // 12 months
            ['code' => 'MMR',         'dose' => 1, 'age_value' => 12, 'age_unit' => 'months', 'grace' => 30],
            // 18 months (boosters)
            ['code' => 'DTP',         'dose' => 4, 'age_value' => 18, 'age_unit' => 'months', 'grace' => 30],
            ['code' => 'OPV',         'dose' => 4, 'age_value' => 18, 'age_unit' => 'months', 'grace' => 30],
            // 5 years (booster)
            ['code' => 'DTP',         'dose' => 5, 'age_value' => 5,  'age_unit' => 'years',  'grace' => 60],
        ];

        foreach ($items as $sort => $item) {
            $vaccineId = DB::table('vaccines')->where('code', $item['code'])->value('id');
            if (!$vaccineId) continue;

            DB::table('schedule_items')->insert([
                'schedule_id'           => $scheduleId,
                'vaccine_id'            => $vaccineId,
                'dose_number'           => $item['dose'],
                'recommended_age_value' => $item['age_value'],
                'recommended_age_unit'  => $item['age_unit'],
                'min_age_value'         => null,
                'max_age_value'         => null,
                'grace_period_days'     => $item['grace'],
                'is_mandatory'          => true,
                'sort_order'            => $sort,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }

    public function down(): void
    {
        $egyptId = DB::table('countries')->where('iso_code', 'EG')->value('id');
        if ($egyptId) {
            $scheduleId = DB::table('vaccination_schedules')
                ->where('country_id', $egyptId)
                ->where('name', 'Egypt EPI 2025')
                ->value('id');

            if ($scheduleId) {
                DB::table('schedule_items')->where('schedule_id', $scheduleId)->delete();
                DB::table('vaccination_schedules')->where('id', $scheduleId)->delete();
            }
        }
    }
};

