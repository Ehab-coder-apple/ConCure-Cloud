<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo clinic ID
        $clinicId = DB::table('clinics')->where('name', 'Demo Clinic')->first()->id;

        $labTests = [
            [
                'name' => 'Complete Blood Count (CBC)',
                'code' => 'CBC',
                'description' => 'Measures different components of blood',
                'category' => 'Blood',
                'normal_range_min' => null,
                'normal_range_max' => null,
                'unit' => null,
                'is_frequent' => true,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Blood Glucose',
                'code' => 'GLU',
                'description' => 'Measures blood sugar levels',
                'category' => 'Blood',
                'normal_range_min' => 70,
                'normal_range_max' => 100,
                'unit' => 'mg/dL',
                'is_frequent' => true,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cholesterol Total',
                'code' => 'CHOL',
                'description' => 'Measures total cholesterol in blood',
                'category' => 'Blood',
                'normal_range_min' => 0,
                'normal_range_max' => 200,
                'unit' => 'mg/dL',
                'is_frequent' => true,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hemoglobin A1C',
                'code' => 'HBA1C',
                'description' => 'Measures average blood sugar over 2-3 months',
                'category' => 'Blood',
                'normal_range_min' => 4,
                'normal_range_max' => 5.6,
                'unit' => '%',
                'is_frequent' => true,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thyroid Stimulating Hormone (TSH)',
                'code' => 'TSH',
                'description' => 'Measures thyroid function',
                'category' => 'Blood',
                'normal_range_min' => 0.4,
                'normal_range_max' => 4.0,
                'unit' => 'mIU/L',
                'is_frequent' => true,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Urinalysis',
                'code' => 'UA',
                'description' => 'Comprehensive urine analysis',
                'category' => 'Urine',
                'normal_range_min' => null,
                'normal_range_max' => null,
                'unit' => null,
                'is_frequent' => true,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liver Function Panel',
                'code' => 'LFT',
                'description' => 'Tests liver enzymes and function',
                'category' => 'Blood',
                'normal_range_min' => null,
                'normal_range_max' => null,
                'unit' => null,
                'is_frequent' => false,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kidney Function Panel',
                'code' => 'KFT',
                'description' => 'Tests kidney function markers',
                'category' => 'Blood',
                'normal_range_min' => null,
                'normal_range_max' => null,
                'unit' => null,
                'is_frequent' => false,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vitamin D',
                'code' => 'VITD',
                'description' => 'Measures vitamin D levels',
                'category' => 'Blood',
                'normal_range_min' => 30,
                'normal_range_max' => 100,
                'unit' => 'ng/mL',
                'is_frequent' => false,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'C-Reactive Protein (CRP)',
                'code' => 'CRP',
                'description' => 'Measures inflammation marker',
                'category' => 'Blood',
                'normal_range_min' => 0,
                'normal_range_max' => 3,
                'unit' => 'mg/L',
                'is_frequent' => false,
                'clinic_id' => $clinicId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('lab_tests')->insert($labTests);
    }
}
