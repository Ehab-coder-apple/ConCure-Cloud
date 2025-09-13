<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists
        if (!Schema::hasColumn('patient_checkups', 'custom_vital_signs')) {
            Schema::table('patient_checkups', function (Blueprint $table) {
                // Add JSON field for custom vital signs
                $table->json('custom_vital_signs')->nullable()->after('blood_sugar');
            });
        }

        // Create custom vital signs configuration table if it doesn't exist
        if (!Schema::hasTable('custom_vital_signs_config')) {
            Schema::create('custom_vital_signs_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('name'); // e.g., "Oxygen Saturation", "Peak Flow"
            $table->string('unit')->nullable(); // e.g., "%", "L/min"
            $table->string('type')->default('number'); // number, text, select
            $table->json('options')->nullable(); // For select type
            $table->decimal('min_value', 8, 2)->nullable();
            $table->decimal('max_value', 8, 2)->nullable();
            $table->string('normal_range')->nullable(); // e.g., "95-100%"
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->index(['clinic_id', 'is_active']);
            });
        }

        // Insert some default custom vital signs
        $clinics = DB::table('clinics')->get();
        
        foreach ($clinics as $clinic) {
            $defaultSigns = [
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Oxygen Saturation',
                    'unit' => '%',
                    'type' => 'number',
                    'options' => null,
                    'min_value' => 70,
                    'max_value' => 100,
                    'normal_range' => '95-100%',
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Peak Flow',
                    'unit' => 'L/min',
                    'type' => 'number',
                    'options' => null,
                    'min_value' => 50,
                    'max_value' => 800,
                    'normal_range' => '400-700 L/min',
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Pain Level',
                    'unit' => '/10',
                    'type' => 'select',
                    'options' => json_encode([
                        '0' => 'No Pain (0)',
                        '1' => 'Mild (1)',
                        '2' => 'Mild (2)',
                        '3' => 'Moderate (3)',
                        '4' => 'Moderate (4)',
                        '5' => 'Moderate (5)',
                        '6' => 'Severe (6)',
                        '7' => 'Severe (7)',
                        '8' => 'Very Severe (8)',
                        '9' => 'Very Severe (9)',
                        '10' => 'Worst Possible (10)',
                    ]),
                    'min_value' => 0,
                    'max_value' => 10,
                    'normal_range' => '0-2/10',
                    'is_active' => true,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Mobility Status',
                    'unit' => '',
                    'type' => 'select',
                    'options' => json_encode([
                        'independent' => 'Independent',
                        'assisted' => 'Assisted',
                        'wheelchair' => 'Wheelchair',
                        'bedbound' => 'Bedbound',
                    ]),
                    'min_value' => null,
                    'max_value' => null,
                    'normal_range' => 'Independent',
                    'is_active' => true,
                    'sort_order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Mental Status',
                    'unit' => '',
                    'type' => 'select',
                    'options' => json_encode([
                        'alert' => 'Alert & Oriented',
                        'confused' => 'Confused',
                        'drowsy' => 'Drowsy',
                        'unconscious' => 'Unconscious',
                    ]),
                    'min_value' => null,
                    'max_value' => null,
                    'normal_range' => 'Alert & Oriented',
                    'is_active' => true,
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($defaultSigns as $sign) {
                DB::table('custom_vital_signs_config')->insert($sign);
            }
        }

        // Log the migration
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration',
            'model_type' => 'PatientCheckup',
            'model_id' => null,
            'description' => 'Added custom vital signs support to patient checkups',
            'changes' => json_encode([
                'migration' => 'add_custom_vital_signs_to_checkups',
                'added_fields' => ['custom_vital_signs'],
                'created_tables' => ['custom_vital_signs_config'],
                'default_signs_added' => 5
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Added custom vital signs support to patient checkups\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_checkups', function (Blueprint $table) {
            $table->dropColumn('custom_vital_signs');
        });

        Schema::dropIfExists('custom_vital_signs_config');

        // Log the rollback
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration_rollback',
            'model_type' => 'PatientCheckup',
            'model_id' => null,
            'description' => 'Removed custom vital signs support from patient checkups',
            'changes' => json_encode([
                'migration' => 'add_custom_vital_signs_to_checkups_rollback',
                'removed_fields' => ['custom_vital_signs'],
                'dropped_tables' => ['custom_vital_signs_config']
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration Rollback',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Removed custom vital signs support from patient checkups\n";
    }
};
