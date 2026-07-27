<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed new vaccines unique to UAE schedule (idempotent)
        $newVaccines = [
            ['code' => 'Hexavalent', 'global_name' => 'Hexavalent (DPT-HepB-Hib-IPV)', 'description' => 'Diphtheria, Pertussis, Tetanus, Hepatitis B, Haemophilus influenzae type b, Inactivated Polio'],
            ['code' => 'PCV',        'global_name' => 'Pneumococcal Conjugate Vaccine (PCV)', 'description' => 'Pneumococcal disease vaccine'],
            ['code' => 'Varicella',   'global_name' => 'Varicella (Chickenpox)',          'description' => 'Varicella (chickenpox) vaccine'],
        ];

        foreach ($newVaccines as $v) {
            DB::table('vaccines')->updateOrInsert(
                ['code' => $v['code']],
                array_merge($v, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // Arabic translations for new vaccines
        $arabicNames = [
            'Hexavalent' => 'اللقاح السداسي',
            'PCV'        => 'لقاح المكورات الرئوية',
            'Varicella'  => 'لقاح الجدري المائي',
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

        // 2. Find UAE country
        $uaeId = DB::table('countries')->where('iso_code', 'AE')->value('id');
        if (!$uaeId) {
            return;
        }

        // Avoid duplicate schedule
        $existing = DB::table('vaccination_schedules')
            ->where('country_id', $uaeId)
            ->where('name', 'UAE National Schedule 2025')
            ->first();

        if ($existing) {
            return;
        }

        // 3. Create UAE schedule
        $scheduleId = DB::table('vaccination_schedules')->insertGetId([
            'country_id'     => $uaeId,
            'name'           => 'UAE National Schedule 2025',
            'version'        => '1.0',
            'is_default'     => true,
            'effective_from' => '2025-01-01',
            'is_active'      => true,
            'notes'          => 'United Arab Emirates National Immunization Programme — 2025 schedule',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('vaccination_schedules')
            ->where('country_id', $uaeId)
            ->where('id', '!=', $scheduleId)
            ->update(['is_default' => false]);

        // 4. Insert schedule items
        // Booster mapping: "booster1" → 4, "booster2" → 5
        $items = [
            // Birth
            ['code' => 'BCG',        'dose' => 1, 'age_value' => 0,  'age_unit' => 'days',   'grace' => 7],
            ['code' => 'HepB',       'dose' => 1, 'age_value' => 0,  'age_unit' => 'days',   'grace' => 7],
            // 2 months
            ['code' => 'Hexavalent', 'dose' => 1, 'age_value' => 2,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'PCV',        'dose' => 1, 'age_value' => 2,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'Rotavirus',  'dose' => 1, 'age_value' => 2,  'age_unit' => 'months', 'grace' => 14],
            // 4 months
            ['code' => 'Hexavalent', 'dose' => 2, 'age_value' => 4,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'PCV',        'dose' => 2, 'age_value' => 4,  'age_unit' => 'months', 'grace' => 14],
            ['code' => 'Rotavirus',  'dose' => 2, 'age_value' => 4,  'age_unit' => 'months', 'grace' => 14],
            // 6 months
            ['code' => 'Hexavalent', 'dose' => 3, 'age_value' => 6,  'age_unit' => 'months', 'grace' => 14],
            // 12 months
            ['code' => 'MMR',        'dose' => 1, 'age_value' => 12, 'age_unit' => 'months', 'grace' => 30],
            ['code' => 'Varicella',  'dose' => 1, 'age_value' => 12, 'age_unit' => 'months', 'grace' => 30],
            // 18 months
            ['code' => 'MMR',        'dose' => 2, 'age_value' => 18, 'age_unit' => 'months', 'grace' => 30],
            ['code' => 'DTP',        'dose' => 4, 'age_value' => 18, 'age_unit' => 'months', 'grace' => 30],
            // 5 years
            ['code' => 'DTP',        'dose' => 5, 'age_value' => 5,  'age_unit' => 'years',  'grace' => 60],
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
        $uaeId = DB::table('countries')->where('iso_code', 'AE')->value('id');
        if ($uaeId) {
            $scheduleId = DB::table('vaccination_schedules')
                ->where('country_id', $uaeId)
                ->where('name', 'UAE National Schedule 2025')
                ->value('id');

            if ($scheduleId) {
                DB::table('schedule_items')->where('schedule_id', $scheduleId)->delete();
                DB::table('vaccination_schedules')->where('id', $scheduleId)->delete();
            }
        }
    }
};

