<?php

namespace Database\Seeders;

use App\Models\DentalProcedure;
use Illuminate\Database\Seeder;

class DentalProcedureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $procedures = [
            // Diagnostic
            ['name' => 'Comprehensive Oral Examination', 'code' => 'D0150', 'category' => 'diagnostic', 'default_cost' => 75.00, 'estimated_duration_minutes' => 30, 'is_frequent' => true],
            ['name' => 'Periodic Oral Evaluation', 'code' => 'D0120', 'category' => 'diagnostic', 'default_cost' => 50.00, 'estimated_duration_minutes' => 20, 'is_frequent' => true],
            ['name' => 'Intraoral Periapical Radiograph', 'code' => 'D0220', 'category' => 'diagnostic', 'default_cost' => 25.00, 'estimated_duration_minutes' => 10, 'is_frequent' => true],
            ['name' => 'Panoramic Radiograph', 'code' => 'D0330', 'category' => 'diagnostic', 'default_cost' => 100.00, 'estimated_duration_minutes' => 15, 'is_frequent' => true],

            // Preventive
            ['name' => 'Prophylaxis (Cleaning) - Adult', 'code' => 'D1110', 'category' => 'preventive', 'default_cost' => 90.00, 'estimated_duration_minutes' => 45, 'is_frequent' => true],
            ['name' => 'Prophylaxis (Cleaning) - Child', 'code' => 'D1120', 'category' => 'preventive', 'default_cost' => 70.00, 'estimated_duration_minutes' => 30, 'is_frequent' => true],
            ['name' => 'Fluoride Treatment', 'code' => 'D1206', 'category' => 'preventive', 'default_cost' => 35.00, 'estimated_duration_minutes' => 15, 'is_frequent' => true],
            ['name' => 'Sealant - Per Tooth', 'code' => 'D1351', 'category' => 'preventive', 'default_cost' => 45.00, 'estimated_duration_minutes' => 20, 'is_frequent' => true],

            // Restorative
            ['name' => 'Amalgam Filling - One Surface', 'code' => 'D2140', 'category' => 'restorative', 'default_cost' => 150.00, 'estimated_duration_minutes' => 30, 'is_frequent' => true],
            ['name' => 'Amalgam Filling - Two Surfaces', 'code' => 'D2150', 'category' => 'restorative', 'default_cost' => 180.00, 'estimated_duration_minutes' => 40, 'is_frequent' => true],
            ['name' => 'Composite Filling - One Surface', 'code' => 'D2330', 'category' => 'restorative', 'default_cost' => 175.00, 'estimated_duration_minutes' => 35, 'is_frequent' => true],
            ['name' => 'Composite Filling - Two Surfaces', 'code' => 'D2331', 'category' => 'restorative', 'default_cost' => 210.00, 'estimated_duration_minutes' => 45, 'is_frequent' => true],
            ['name' => 'Crown - Porcelain Fused to Metal', 'code' => 'D2750', 'category' => 'restorative', 'default_cost' => 1200.00, 'estimated_duration_minutes' => 90, 'is_frequent' => true],
            ['name' => 'Crown - Full Porcelain/Ceramic', 'code' => 'D2740', 'category' => 'restorative', 'default_cost' => 1400.00, 'estimated_duration_minutes' => 90, 'is_frequent' => true],

            // Endodontics
            ['name' => 'Root Canal - Anterior Tooth', 'code' => 'D3310', 'category' => 'endodontics', 'default_cost' => 800.00, 'estimated_duration_minutes' => 90, 'requires_anesthesia' => true, 'is_frequent' => true],
            ['name' => 'Root Canal - Premolar', 'code' => 'D3320', 'category' => 'endodontics', 'default_cost' => 950.00, 'estimated_duration_minutes' => 120, 'requires_anesthesia' => true, 'is_frequent' => true],
            ['name' => 'Root Canal - Molar', 'code' => 'D3330', 'category' => 'endodontics', 'default_cost' => 1200.00, 'estimated_duration_minutes' => 150, 'requires_anesthesia' => true, 'is_frequent' => true],

            // Periodontics
            ['name' => 'Scaling and Root Planing - Per Quadrant', 'code' => 'D4341', 'category' => 'periodontics', 'default_cost' => 200.00, 'estimated_duration_minutes' => 60, 'is_frequent' => true],
            ['name' => 'Periodontal Maintenance', 'code' => 'D4910', 'category' => 'periodontics', 'default_cost' => 120.00, 'estimated_duration_minutes' => 45, 'is_frequent' => true],
            ['name' => 'Gingivectomy - Per Quadrant', 'code' => 'D4210', 'category' => 'periodontics', 'default_cost' => 500.00, 'estimated_duration_minutes' => 90, 'requires_anesthesia' => true],

            // Prosthodontics
            ['name' => 'Complete Denture - Upper', 'code' => 'D5110', 'category' => 'prosthodontics', 'default_cost' => 1500.00, 'estimated_duration_minutes' => 180],
            ['name' => 'Complete Denture - Lower', 'code' => 'D5120', 'category' => 'prosthodontics', 'default_cost' => 1500.00, 'estimated_duration_minutes' => 180],
            ['name' => 'Partial Denture - Resin Base', 'code' => 'D5211', 'category' => 'prosthodontics', 'default_cost' => 1200.00, 'estimated_duration_minutes' => 150],
            ['name' => 'Bridge - Pontic (Per Unit)', 'code' => 'D6242', 'category' => 'prosthodontics', 'default_cost' => 1100.00, 'estimated_duration_minutes' => 120],

            // Oral Surgery
            ['name' => 'Simple Extraction', 'code' => 'D7140', 'category' => 'oral_surgery', 'default_cost' => 150.00, 'estimated_duration_minutes' => 30, 'requires_anesthesia' => true, 'is_frequent' => true],
            ['name' => 'Surgical Extraction', 'code' => 'D7210', 'category' => 'oral_surgery', 'default_cost' => 300.00, 'estimated_duration_minutes' => 60, 'requires_anesthesia' => true, 'is_frequent' => true],
            ['name' => 'Wisdom Tooth Extraction', 'code' => 'D7240', 'category' => 'oral_surgery', 'default_cost' => 400.00, 'estimated_duration_minutes' => 90, 'requires_anesthesia' => true, 'is_frequent' => true],

            // Orthodontics
            ['name' => 'Orthodontic Consultation', 'code' => 'D8660', 'category' => 'orthodontics', 'default_cost' => 100.00, 'estimated_duration_minutes' => 45],
            ['name' => 'Comprehensive Orthodontic Treatment', 'code' => 'D8080', 'category' => 'orthodontics', 'default_cost' => 5000.00, 'estimated_duration_minutes' => 30],
            ['name' => 'Retainer - Fixed', 'code' => 'D8680', 'category' => 'orthodontics', 'default_cost' => 400.00, 'estimated_duration_minutes' => 45],

            // Implants
            ['name' => 'Dental Implant - Endosteal', 'code' => 'D6010', 'category' => 'implants', 'default_cost' => 2000.00, 'estimated_duration_minutes' => 120, 'requires_anesthesia' => true],
            ['name' => 'Implant Crown - Porcelain', 'code' => 'D6058', 'category' => 'implants', 'default_cost' => 1500.00, 'estimated_duration_minutes' => 90],
            ['name' => 'Implant Abutment', 'code' => 'D6056', 'category' => 'implants', 'default_cost' => 800.00, 'estimated_duration_minutes' => 60],

            // Cosmetic
            ['name' => 'Teeth Whitening - In Office', 'code' => 'D9972', 'category' => 'cosmetic', 'default_cost' => 500.00, 'estimated_duration_minutes' => 90],
            ['name' => 'Teeth Whitening - Take Home Kit', 'code' => 'D9973', 'category' => 'cosmetic', 'default_cost' => 300.00, 'estimated_duration_minutes' => 30],
            ['name' => 'Veneer - Porcelain (Per Tooth)', 'code' => 'D2962', 'category' => 'cosmetic', 'default_cost' => 1200.00, 'estimated_duration_minutes' => 120],

            // Emergency
            ['name' => 'Emergency Dental Exam', 'code' => 'D0140', 'category' => 'emergency', 'default_cost' => 100.00, 'estimated_duration_minutes' => 20, 'is_frequent' => true],
            ['name' => 'Palliative Treatment (Emergency)', 'code' => 'D9110', 'category' => 'emergency', 'default_cost' => 75.00, 'estimated_duration_minutes' => 15, 'is_frequent' => true],
        ];

        foreach ($procedures as $procedure) {
            DentalProcedure::create(array_merge($procedure, [
                'clinic_id' => null, // Global procedures
                'currency' => 'USD',
                'is_active' => true,
            ]));
        }
    }
}

