<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RadiologyTestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tests = [
            // Group A: X-Ray Tests
            [
                'name' => 'Chest X-ray',
                'code' => 'CXR',
                'description' => 'Standard chest radiograph to evaluate lungs, heart, and chest structures',
                'category' => 'x_ray',
                'body_part' => 'chest',
                'preparation_instructions' => 'Remove jewelry and metal objects from chest area',
                'estimated_duration_minutes' => 15,
                'is_frequent' => true,
            ],
            [
                'name' => 'Abdominal X-ray',
                'code' => 'AXR',
                'description' => 'Plain abdominal radiograph to evaluate abdominal organs and bowel',
                'category' => 'x_ray',
                'body_part' => 'abdomen',
                'preparation_instructions' => 'No specific preparation required',
                'estimated_duration_minutes' => 15,
                'is_frequent' => true,
            ],
            [
                'name' => 'Bone X-ray',
                'code' => 'BXR',
                'description' => 'Radiograph of bones to evaluate fractures, arthritis, or bone abnormalities',
                'category' => 'x_ray',
                'body_part' => 'upper_extremity',
                'preparation_instructions' => 'Remove jewelry and metal objects from the area',
                'estimated_duration_minutes' => 15,
                'is_frequent' => true,
            ],
            [
                'name' => 'Dental X-ray',
                'code' => 'DXR',
                'description' => 'Dental radiograph to evaluate teeth, jaw, and oral structures',
                'category' => 'x_ray',
                'body_part' => 'head',
                'preparation_instructions' => 'Remove dentures, jewelry, and metal objects from head area',
                'estimated_duration_minutes' => 10,
                'is_frequent' => true,
            ],

            // Group B: CT (Computed Tomography)
            [
                'name' => 'CT Head / Brain',
                'code' => 'CTH',
                'description' => 'Computed tomography of the head and brain to evaluate neurological conditions',
                'category' => 'ct_scan',
                'body_part' => 'head',
                'preparation_instructions' => 'Remove metal objects from head area. Fast 4 hours if contrast required',
                'estimated_duration_minutes' => 30,
                'is_frequent' => true,
            ],
            [
                'name' => 'CT Chest',
                'code' => 'CTC',
                'description' => 'Computed tomography of the chest to evaluate lungs and mediastinal structures',
                'category' => 'ct_scan',
                'body_part' => 'chest',
                'preparation_instructions' => 'Fast for 4 hours if contrast is required',
                'estimated_duration_minutes' => 45,
                'is_frequent' => true,
            ],
            [
                'name' => 'CT Abdomen & Pelvis',
                'code' => 'CTAP',
                'description' => 'Computed tomography of abdomen and pelvis to evaluate internal organs',
                'category' => 'ct_scan',
                'body_part' => 'abdomen',
                'preparation_instructions' => 'Fast for 6 hours, drink oral contrast solution as instructed',
                'estimated_duration_minutes' => 60,
                'requires_contrast' => true,
                'requires_fasting' => true,
                'is_frequent' => true,
            ],
            [
                'name' => 'CT Angiography',
                'code' => 'CTA',
                'description' => 'CT angiography to evaluate blood vessels and vascular conditions',
                'category' => 'ct_scan',
                'body_part' => 'other',
                'preparation_instructions' => 'Fast for 4 hours, IV contrast required, check kidney function',
                'estimated_duration_minutes' => 45,
                'requires_contrast' => true,
                'is_frequent' => false,
            ],

            // Group C: MRI (Magnetic Resonance Imaging)
            [
                'name' => 'MRI Brain',
                'code' => 'MRIB',
                'description' => 'Magnetic resonance imaging of the brain for detailed neurological evaluation',
                'category' => 'mri',
                'body_part' => 'head',
                'preparation_instructions' => 'Remove all metal objects, inform about pacemakers, implants, or claustrophobia',
                'estimated_duration_minutes' => 60,
                'is_frequent' => true,
            ],
            [
                'name' => 'MRI Spine',
                'code' => 'MRIS',
                'description' => 'Magnetic resonance imaging of the spine to evaluate spinal cord and vertebrae',
                'category' => 'mri',
                'body_part' => 'spine',
                'preparation_instructions' => 'Remove all metal objects, inform about pacemakers, implants, or claustrophobia',
                'estimated_duration_minutes' => 75,
                'is_frequent' => true,
            ],
            [
                'name' => 'MRI Knee',
                'code' => 'MRIK',
                'description' => 'Magnetic resonance imaging of the knee joint for detailed soft tissue evaluation',
                'category' => 'mri',
                'body_part' => 'lower_extremity',
                'preparation_instructions' => 'Remove all metal objects, inform about pacemakers or implants',
                'estimated_duration_minutes' => 45,
                'is_frequent' => true,
            ],
            [
                'name' => 'MR Angiography (MRA)',
                'code' => 'MRA',
                'description' => 'Magnetic resonance angiography to evaluate blood vessels without radiation',
                'category' => 'mri',
                'body_part' => 'other',
                'preparation_instructions' => 'Remove all metal objects, inform about pacemakers or implants',
                'estimated_duration_minutes' => 60,
                'is_frequent' => false,
            ],

            // Group D: Ultrasound
            [
                'name' => 'Abdominal Ultrasound',
                'code' => 'USG-ABD',
                'description' => 'Ultrasound examination of abdominal organs including liver, gallbladder, kidneys',
                'category' => 'ultrasound',
                'body_part' => 'abdomen',
                'preparation_instructions' => 'Fast for 8 hours, drink water 1 hour before exam',
                'estimated_duration_minutes' => 30,
                'requires_fasting' => true,
                'is_frequent' => true,
            ],
            [
                'name' => 'Pelvic Ultrasound',
                'code' => 'USG-PEL',
                'description' => 'Ultrasound examination of pelvic organs including uterus, ovaries, bladder',
                'category' => 'ultrasound',
                'body_part' => 'pelvis',
                'preparation_instructions' => 'Full bladder required - drink 32oz water 1 hour before exam',
                'estimated_duration_minutes' => 30,
                'is_frequent' => true,
            ],
            [
                'name' => 'Obstetric Ultrasound',
                'code' => 'USG-OBS',
                'description' => 'Ultrasound examination during pregnancy to monitor fetal development',
                'category' => 'ultrasound',
                'body_part' => 'pelvis',
                'preparation_instructions' => 'Full bladder may be required for early pregnancy scans',
                'estimated_duration_minutes' => 30,
                'is_frequent' => true,
            ],
            [
                'name' => 'Doppler Ultrasound',
                'code' => 'USG-DOP',
                'description' => 'Doppler ultrasound to evaluate blood flow in arteries and veins',
                'category' => 'ultrasound',
                'body_part' => 'other',
                'preparation_instructions' => 'No specific preparation required',
                'estimated_duration_minutes' => 45,
                'is_frequent' => false,
            ],

            // Group E: Nuclear Medicine
            [
                'name' => 'PET Scan (Whole Body)',
                'code' => 'PET-WB',
                'description' => 'Positron emission tomography for cancer detection and metabolic evaluation',
                'category' => 'nuclear_medicine',
                'body_part' => 'whole_body',
                'preparation_instructions' => 'Fast for 6 hours, avoid strenuous exercise 24 hours before, diabetic instructions provided',
                'estimated_duration_minutes' => 180,
                'requires_fasting' => true,
                'is_frequent' => false,
            ],
            [
                'name' => 'Bone Scan',
                'code' => 'BONE-SCAN',
                'description' => 'Nuclear medicine bone scan to detect bone abnormalities, fractures, or metastases',
                'category' => 'nuclear_medicine',
                'body_part' => 'whole_body',
                'preparation_instructions' => 'Injection 2-3 hours before scan, drink plenty of fluids, empty bladder before scan',
                'estimated_duration_minutes' => 120,
                'is_frequent' => false,
            ],
            [
                'name' => 'Thyroid Scan',
                'code' => 'THY-SCAN',
                'description' => 'Nuclear medicine thyroid scan to evaluate thyroid function and structure',
                'category' => 'nuclear_medicine',
                'body_part' => 'neck',
                'preparation_instructions' => 'Stop thyroid medications as instructed, avoid iodine-containing foods/medications',
                'estimated_duration_minutes' => 60,
                'is_frequent' => false,
            ],

            // Group F: Interventional Radiology (IR)
            [
                'name' => 'Angiography',
                'code' => 'ANGIO',
                'description' => 'Invasive imaging procedure to visualize blood vessels using contrast',
                'category' => 'angiography',
                'body_part' => 'other',
                'preparation_instructions' => 'Fast for 8 hours, stop blood thinners as instructed, check kidney function',
                'estimated_duration_minutes' => 90,
                'requires_contrast' => true,
                'requires_fasting' => true,
                'is_frequent' => false,
            ],
            [
                'name' => 'Biopsy under Imaging Guidance',
                'code' => 'BIOPSY-IG',
                'description' => 'Image-guided tissue biopsy for diagnostic purposes',
                'category' => 'other',
                'body_part' => 'other',
                'preparation_instructions' => 'Fast for 6 hours, stop blood thinners as instructed, arrange transportation',
                'estimated_duration_minutes' => 60,
                'requires_fasting' => true,
                'is_frequent' => false,
            ],
            [
                'name' => 'Stent Placement',
                'code' => 'STENT',
                'description' => 'Minimally invasive procedure to place stents in blood vessels',
                'category' => 'angiography',
                'body_part' => 'other',
                'preparation_instructions' => 'Fast for 8 hours, stop blood thinners as instructed, pre-procedure medications',
                'estimated_duration_minutes' => 120,
                'requires_contrast' => true,
                'requires_fasting' => true,
                'is_frequent' => false,
            ],
        ];

        foreach ($tests as $test) {
            DB::table('radiology_tests')->insert(array_merge($test, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
