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
        // Create patient vital signs assignments table
        Schema::create('patient_vital_signs_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('custom_vital_sign_id');
            $table->string('medical_condition')->nullable(); // e.g., "Diabetes", "Hypertension"
            $table->text('reason')->nullable(); // Why this vital sign was assigned
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('assigned_by'); // User who assigned it
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('custom_vital_sign_id')->references('id')->on('custom_vital_signs_config')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['patient_id', 'custom_vital_sign_id'], 'patient_vital_sign_unique');
            $table->index(['patient_id', 'is_active']);
        });

        // Create medical condition templates table
        Schema::create('medical_condition_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('condition_name'); // e.g., "Diabetes Management", "Cardiac Monitoring"
            $table->text('description')->nullable();
            $table->json('vital_sign_ids'); // Array of custom vital sign IDs
            $table->string('specialty')->nullable(); // e.g., "Cardiology", "Endocrinology"
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['clinic_id', 'is_active']);
        });

        // Create some default medical condition templates
        $clinics = DB::table('clinics')->get();
        
        foreach ($clinics as $clinic) {
            // Get some custom vital signs for this clinic
            $customSigns = DB::table('custom_vital_signs_config')
                            ->where('clinic_id', $clinic->id)
                            ->where('is_active', true)
                            ->get();

            if ($customSigns->count() > 0) {
                $defaultTemplates = [
                    [
                        'clinic_id' => $clinic->id,
                        'condition_name' => 'Diabetes Management',
                        'description' => 'Comprehensive monitoring for diabetic patients',
                        'vital_sign_ids' => json_encode([
                            $customSigns->where('name', 'Blood Sugar')->first()->id ?? $customSigns->first()->id,
                        ]),
                        'specialty' => 'Endocrinology',
                        'is_active' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'condition_name' => 'Cardiac Monitoring',
                        'description' => 'Heart condition monitoring and assessment',
                        'vital_sign_ids' => json_encode([
                            $customSigns->where('name', 'Oxygen Saturation')->first()->id ?? $customSigns->first()->id,
                        ]),
                        'specialty' => 'Cardiology',
                        'is_active' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'condition_name' => 'Pain Management',
                        'description' => 'Comprehensive pain assessment and monitoring',
                        'vital_sign_ids' => json_encode([
                            $customSigns->where('name', 'Pain Level')->first()->id ?? $customSigns->first()->id,
                        ]),
                        'specialty' => 'Pain Management',
                        'is_active' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'condition_name' => 'Respiratory Care',
                        'description' => 'Respiratory function monitoring',
                        'vital_sign_ids' => json_encode([
                            $customSigns->where('name', 'Oxygen Saturation')->first()->id ?? $customSigns->first()->id,
                            $customSigns->where('name', 'Peak Flow')->first()->id ?? $customSigns->first()->id,
                        ]),
                        'specialty' => 'Pulmonology',
                        'is_active' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'condition_name' => 'Geriatric Assessment',
                        'description' => 'Comprehensive elderly patient monitoring',
                        'vital_sign_ids' => json_encode([
                            $customSigns->where('name', 'Mobility Status')->first()->id ?? $customSigns->first()->id,
                            $customSigns->where('name', 'Mental Status')->first()->id ?? $customSigns->first()->id,
                        ]),
                        'specialty' => 'Geriatrics',
                        'is_active' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];

                foreach ($defaultTemplates as $template) {
                    DB::table('medical_condition_templates')->insert($template);
                }
            }
        }

        // Log the migration
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration',
            'model_type' => 'PatientVitalSignsAssignment',
            'model_id' => null,
            'description' => 'Created patient-specific vital signs assignment system',
            'changes' => json_encode([
                'migration' => 'create_patient_vital_signs_assignments',
                'created_tables' => ['patient_vital_signs_assignments', 'medical_condition_templates'],
                'default_templates_added' => 5
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Created patient-specific vital signs assignment system\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_vital_signs_assignments');
        Schema::dropIfExists('medical_condition_templates');

        // Log the rollback
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration_rollback',
            'model_type' => 'PatientVitalSignsAssignment',
            'model_id' => null,
            'description' => 'Removed patient-specific vital signs assignment system',
            'changes' => json_encode([
                'migration' => 'create_patient_vital_signs_assignments_rollback',
                'dropped_tables' => ['patient_vital_signs_assignments', 'medical_condition_templates']
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration Rollback',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Removed patient-specific vital signs assignment system\n";
    }
};
