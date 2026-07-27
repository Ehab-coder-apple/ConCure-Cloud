<?php

namespace Database\Seeders;

use App\Models\ToothCanal;
use Illuminate\Database\Seeder;

class CanalSeeder extends Seeder
{
    /**
     * Seed standard tooth canal definitions (FDI notation).
     */
    public function run(): void
    {
        // Clear existing data
        ToothCanal::truncate();

        // Canal definitions by tooth type
        $definitions = [
            // Upper Incisors (11,12,21,22) - Single canal
            'upper_incisor' => ['teeth' => ['11','12','21','22'], 'type' => 'incisor', 'arch' => 'upper', 'canals' => [
                ['name' => 'Single', 'code' => 'S', 'order' => 1, 'common' => true],
            ]],
            // Lower Incisors (31,32,41,42) - Single or two canals
            'lower_incisor' => ['teeth' => ['31','32','41','42'], 'type' => 'incisor', 'arch' => 'lower', 'canals' => [
                ['name' => 'Lingual', 'code' => 'L', 'order' => 1, 'common' => true],
                ['name' => 'Buccal', 'code' => 'B', 'order' => 2, 'common' => false],
            ]],
            // Upper Canines (13,23) - Single canal
            'upper_canine' => ['teeth' => ['13','23'], 'type' => 'canine', 'arch' => 'upper', 'canals' => [
                ['name' => 'Single', 'code' => 'S', 'order' => 1, 'common' => true],
            ]],
            // Lower Canines (33,43) - Single canal
            'lower_canine' => ['teeth' => ['33','43'], 'type' => 'canine', 'arch' => 'lower', 'canals' => [
                ['name' => 'Single', 'code' => 'S', 'order' => 1, 'common' => true],
                ['name' => 'Lingual', 'code' => 'L', 'order' => 2, 'common' => false],
            ]],
            // Upper Premolars (14,15,24,25) - Usually 2 canals
            'upper_premolar' => ['teeth' => ['14','15','24','25'], 'type' => 'premolar', 'arch' => 'upper', 'canals' => [
                ['name' => 'Buccal', 'code' => 'B', 'order' => 1, 'common' => true],
                ['name' => 'Palatal', 'code' => 'P', 'order' => 2, 'common' => true],
            ]],
            // Lower Premolars (34,35,44,45) - Usually 1 canal
            'lower_premolar' => ['teeth' => ['34','35','44','45'], 'type' => 'premolar', 'arch' => 'lower', 'canals' => [
                ['name' => 'Single', 'code' => 'S', 'order' => 1, 'common' => true],
                ['name' => 'Lingual', 'code' => 'L', 'order' => 2, 'common' => false],
            ]],
            // Upper Molars (16,17,18,26,27,28) - 3 or 4 canals
            'upper_molar' => ['teeth' => ['16','17','18','26','27','28'], 'type' => 'molar', 'arch' => 'upper', 'canals' => [
                ['name' => 'Mesiobuccal (MB1)', 'code' => 'MB1', 'order' => 1, 'common' => true],
                ['name' => 'Mesiobuccal 2 (MB2)', 'code' => 'MB2', 'order' => 2, 'common' => false],
                ['name' => 'Distobuccal (DB)', 'code' => 'DB', 'order' => 3, 'common' => true],
                ['name' => 'Palatal (P)', 'code' => 'P', 'order' => 4, 'common' => true],
            ]],
            // Lower Molars (36,37,38,46,47,48) - 3 or 4 canals
            'lower_molar' => ['teeth' => ['36','37','38','46','47','48'], 'type' => 'molar', 'arch' => 'lower', 'canals' => [
                ['name' => 'Mesiolingual (ML)', 'code' => 'ML', 'order' => 1, 'common' => true],
                ['name' => 'Mesiobuccal (MB)', 'code' => 'MB', 'order' => 2, 'common' => true],
                ['name' => 'Distal (D)', 'code' => 'D', 'order' => 3, 'common' => true],
                ['name' => 'Distolingual (DL)', 'code' => 'DL', 'order' => 4, 'common' => false],
            ]],
            // Primary Upper Molars (54,55,64,65)
            'primary_upper_molar' => ['teeth' => ['54','55','64','65'], 'type' => 'molar', 'arch' => 'upper', 'canals' => [
                ['name' => 'Mesiobuccal (MB)', 'code' => 'MB', 'order' => 1, 'common' => true],
                ['name' => 'Distobuccal (DB)', 'code' => 'DB', 'order' => 2, 'common' => true],
                ['name' => 'Palatal (P)', 'code' => 'P', 'order' => 3, 'common' => true],
            ]],
            // Primary Lower Molars (74,75,84,85)
            'primary_lower_molar' => ['teeth' => ['74','75','84','85'], 'type' => 'molar', 'arch' => 'lower', 'canals' => [
                ['name' => 'Mesial (M)', 'code' => 'M', 'order' => 1, 'common' => true],
                ['name' => 'Distal (D)', 'code' => 'D', 'order' => 2, 'common' => true],
            ]],
            // Primary Incisors (51,52,61,62,71,72,81,82) - Single canal
            'primary_incisor' => ['teeth' => ['51','52','61','62','71','72','81','82'], 'type' => 'incisor', 'arch' => 'upper', 'canals' => [
                ['name' => 'Single', 'code' => 'S', 'order' => 1, 'common' => true],
            ]],
            // Primary Canines (53,63,73,83) - Single canal
            'primary_canine' => ['teeth' => ['53','63','73','83'], 'type' => 'canine', 'arch' => 'upper', 'canals' => [
                ['name' => 'Single', 'code' => 'S', 'order' => 1, 'common' => true],
            ]],
        ];

        $records = [];
        $now = now();

        foreach ($definitions as $def) {
            foreach ($def['teeth'] as $tooth) {
                // Determine actual arch from tooth number
                $quadrant = (int) substr($tooth, 0, 1);
                $arch = ($quadrant <= 2 || ($quadrant >= 5 && $quadrant <= 6)) ? 'upper' : 'lower';

                foreach ($def['canals'] as $canal) {
                    $records[] = [
                        'tooth_number' => $tooth,
                        'canal_name' => $canal['name'],
                        'canal_code' => $canal['code'],
                        'tooth_type' => $def['type'],
                        'arch' => $arch,
                        'display_order' => $canal['order'],
                        'is_common' => $canal['common'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Bulk insert
        foreach (array_chunk($records, 100) as $chunk) {
            ToothCanal::insert($chunk);
        }

        $this->command->info("Seeded " . count($records) . " tooth canal definitions.");
    }
}

