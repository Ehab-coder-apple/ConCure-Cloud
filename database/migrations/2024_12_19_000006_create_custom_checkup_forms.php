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
        // Create custom checkup form templates table (idempotent)
        if (!Schema::hasTable('custom_checkup_templates')) {
            Schema::create('custom_checkup_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('clinic_id');
                $table->string('name'); // e.g., "Pre-Surgery Assessment", "Diabetes Follow-up"
                $table->text('description')->nullable();
                $table->string('medical_condition')->nullable(); // e.g., "Diabetes", "Pre-Surgery"
                $table->string('specialty')->nullable(); // e.g., "Cardiology", "Surgery"
                $table->string('checkup_type')->default('follow_up'); // follow_up, initial, emergency, specialty
                $table->json('form_config'); // Complete form configuration
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false); // Default template for condition
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->index(['clinic_id', 'is_active']);
                $table->index(['medical_condition', 'is_active']);
            });
        }

        // Create custom checkup fields table (idempotent)
        if (!Schema::hasTable('custom_checkup_fields')) {
            Schema::create('custom_checkup_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('template_id');
                $table->string('field_name'); // e.g., "surgical_site", "pain_assessment"
                $table->string('field_label'); // e.g., "Surgical Site", "Pain Assessment"
                $table->string('field_type'); // text, number, select, checkbox, textarea, date, time, file
                $table->json('field_options')->nullable(); // For select, checkbox, etc.
                $table->json('validation_rules')->nullable(); // required, min, max, etc.
                $table->string('default_value')->nullable();
                $table->text('help_text')->nullable();
                $table->string('section')->default('additional'); // vital_signs, symptoms, additional, assessment
                $table->integer('sort_order')->default(0);
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('custom_checkup_templates')->onDelete('cascade');
                $table->index(['template_id', 'is_active']);
                $table->index(['section', 'sort_order']);
            });
        }

        // Create patient checkup template assignments table (idempotent)
        if (!Schema::hasTable('patient_checkup_template_assignments')) {
            Schema::create('patient_checkup_template_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('template_id');
                $table->string('medical_condition')->nullable();
                $table->text('reason')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('assigned_by');
                $table->timestamp('assigned_at');
                $table->timestamps();

                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('template_id')->references('id')->on('custom_checkup_templates')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');

                $table->unique(['patient_id', 'template_id'], 'patient_template_unique');
                $table->index(['patient_id', 'is_active']);
            });
        }

        // Add custom_fields/template_id columns to patient_checkups table (idempotent)
        Schema::table('patient_checkups', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_checkups', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('custom_vital_signs');
            }
            if (!Schema::hasColumn('patient_checkups', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('custom_fields');
                $table->foreign('template_id')->references('id')->on('custom_checkup_templates')->onDelete('set null');
                $table->index('template_id');
            }
        });

        // Create some default custom checkup templates
        $clinics = DB::table('clinics')->get();
        
        foreach ($clinics as $clinic) {
            $defaultTemplates = [
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Pre-Surgery Assessment',
                    'description' => 'Comprehensive pre-operative evaluation and clearance',
                    'medical_condition' => 'Pre-Surgery',
                    'specialty' => 'Surgery',
                    'checkup_type' => 'initial',
                    'form_config' => json_encode([
                        'sections' => [
                            'surgical_assessment' => [
                                'title' => 'Surgical Assessment',
                                'fields' => [
                                    'surgical_procedure' => [
                                        'type' => 'text',
                                        'label' => 'Planned Procedure',
                                        'required' => true
                                    ],
                                    'surgical_site' => [
                                        'type' => 'text',
                                        'label' => 'Surgical Site',
                                        'required' => true
                                    ],
                                    'anesthesia_type' => [
                                        'type' => 'select',
                                        'label' => 'Anesthesia Type',
                                        'options' => ['General', 'Regional', 'Local', 'Sedation']
                                    ],
                                    'surgical_risk' => [
                                        'type' => 'select',
                                        'label' => 'Surgical Risk Assessment',
                                        'options' => ['Low', 'Moderate', 'High']
                                    ]
                                ]
                            ],
                            'pre_op_clearance' => [
                                'title' => 'Pre-Operative Clearance',
                                'fields' => [
                                    'cardiac_clearance' => [
                                        'type' => 'checkbox',
                                        'label' => 'Cardiac Clearance Required'
                                    ],
                                    'pulmonary_clearance' => [
                                        'type' => 'checkbox',
                                        'label' => 'Pulmonary Clearance Required'
                                    ],
                                    'lab_work_ordered' => [
                                        'type' => 'checkbox',
                                        'label' => 'Lab Work Ordered'
                                    ]
                                ]
                            ]
                        ]
                    ]),
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Diabetes Follow-up',
                    'description' => 'Comprehensive diabetes management and monitoring',
                    'medical_condition' => 'Diabetes',
                    'specialty' => 'Endocrinology',
                    'checkup_type' => 'follow_up',
                    'form_config' => json_encode([
                        'sections' => [
                            'diabetes_management' => [
                                'title' => 'Diabetes Management',
                                'fields' => [
                                    'hba1c_level' => [
                                        'type' => 'number',
                                        'label' => 'HbA1c Level (%)',
                                        'min' => 4,
                                        'max' => 15,
                                        'step' => 0.1
                                    ],
                                    'medication_compliance' => [
                                        'type' => 'select',
                                        'label' => 'Medication Compliance',
                                        'options' => ['Excellent', 'Good', 'Fair', 'Poor']
                                    ],
                                    'diet_compliance' => [
                                        'type' => 'select',
                                        'label' => 'Diet Compliance',
                                        'options' => ['Excellent', 'Good', 'Fair', 'Poor']
                                    ],
                                    'exercise_frequency' => [
                                        'type' => 'select',
                                        'label' => 'Exercise Frequency',
                                        'options' => ['Daily', '4-6 times/week', '2-3 times/week', '1 time/week', 'Rarely']
                                    ]
                                ]
                            ],
                            'complications_screening' => [
                                'title' => 'Complications Screening',
                                'fields' => [
                                    'foot_examination' => [
                                        'type' => 'select',
                                        'label' => 'Foot Examination',
                                        'options' => ['Normal', 'Calluses', 'Ulcers', 'Neuropathy signs']
                                    ],
                                    'eye_examination_due' => [
                                        'type' => 'checkbox',
                                        'label' => 'Eye Examination Due'
                                    ],
                                    'kidney_function_check' => [
                                        'type' => 'checkbox',
                                        'label' => 'Kidney Function Check Needed'
                                    ]
                                ]
                            ]
                        ]
                    ]),
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Cardiac Assessment',
                    'description' => 'Comprehensive cardiac evaluation and monitoring',
                    'medical_condition' => 'Cardiac',
                    'specialty' => 'Cardiology',
                    'checkup_type' => 'follow_up',
                    'form_config' => json_encode([
                        'sections' => [
                            'cardiac_assessment' => [
                                'title' => 'Cardiac Assessment',
                                'fields' => [
                                    'chest_pain' => [
                                        'type' => 'select',
                                        'label' => 'Chest Pain',
                                        'options' => ['None', 'Mild', 'Moderate', 'Severe']
                                    ],
                                    'shortness_of_breath' => [
                                        'type' => 'select',
                                        'label' => 'Shortness of Breath',
                                        'options' => ['None', 'On exertion', 'At rest', 'Severe']
                                    ],
                                    'palpitations' => [
                                        'type' => 'checkbox',
                                        'label' => 'Palpitations'
                                    ],
                                    'ankle_swelling' => [
                                        'type' => 'checkbox',
                                        'label' => 'Ankle Swelling'
                                    ]
                                ]
                            ],
                            'functional_assessment' => [
                                'title' => 'Functional Assessment',
                                'fields' => [
                                    'exercise_tolerance' => [
                                        'type' => 'select',
                                        'label' => 'Exercise Tolerance',
                                        'options' => ['Excellent', 'Good', 'Fair', 'Poor', 'Unable']
                                    ],
                                    'nyha_class' => [
                                        'type' => 'select',
                                        'label' => 'NYHA Functional Class',
                                        'options' => ['Class I', 'Class II', 'Class III', 'Class IV']
                                    ]
                                ]
                            ]
                        ]
                    ]),
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'clinic_id' => $clinic->id,
                    'name' => 'Mental Health Assessment',
                    'description' => 'Comprehensive mental health evaluation and screening',
                    'medical_condition' => 'Mental Health',
                    'specialty' => 'Psychiatry',
                    'checkup_type' => 'follow_up',
                    'form_config' => json_encode([
                        'sections' => [
                            'mood_assessment' => [
                                'title' => 'Mood Assessment',
                                'fields' => [
                                    'mood_rating' => [
                                        'type' => 'select',
                                        'label' => 'Overall Mood (1-10)',
                                        'options' => ['1 - Very Poor', '2 - Poor', '3 - Below Average', '4 - Fair', '5 - Average', '6 - Above Average', '7 - Good', '8 - Very Good', '9 - Excellent', '10 - Outstanding']
                                    ],
                                    'anxiety_level' => [
                                        'type' => 'select',
                                        'label' => 'Anxiety Level',
                                        'options' => ['None', 'Mild', 'Moderate', 'Severe']
                                    ],
                                    'sleep_quality' => [
                                        'type' => 'select',
                                        'label' => 'Sleep Quality',
                                        'options' => ['Excellent', 'Good', 'Fair', 'Poor', 'Very Poor']
                                    ],
                                    'appetite_changes' => [
                                        'type' => 'select',
                                        'label' => 'Appetite Changes',
                                        'options' => ['No change', 'Increased', 'Decreased', 'Significantly decreased']
                                    ]
                                ]
                            ],
                            'functional_assessment' => [
                                'title' => 'Functional Assessment',
                                'fields' => [
                                    'daily_activities' => [
                                        'type' => 'select',
                                        'label' => 'Daily Activities Performance',
                                        'options' => ['Normal', 'Slightly impaired', 'Moderately impaired', 'Severely impaired']
                                    ],
                                    'social_functioning' => [
                                        'type' => 'select',
                                        'label' => 'Social Functioning',
                                        'options' => ['Normal', 'Slightly impaired', 'Moderately impaired', 'Severely impaired']
                                    ],
                                    'medication_compliance' => [
                                        'type' => 'select',
                                        'label' => 'Medication Compliance',
                                        'options' => ['Excellent', 'Good', 'Fair', 'Poor']
                                    ]
                                ]
                            ]
                        ]
                    ]),
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ];

            foreach ($defaultTemplates as $template) {
                DB::table('custom_checkup_templates')->insert($template);
            }
        }

        // Log the migration (best-effort)
        try {
            DB::table('audit_logs')->insert([
                'user_id' => null,
                'clinic_id' => null,
                'action' => 'system_migration',
                'model_type' => 'CustomCheckupTemplate',
                'model_id' => null,
                'description' => 'Created custom checkup forms system',
                'changes' => json_encode([
                    'migration' => 'create_custom_checkup_forms',
                    'created_tables' => ['custom_checkup_templates', 'custom_checkup_fields', 'patient_checkup_template_assignments'],
                    'enhanced_tables' => ['patient_checkups'],
                    'default_templates_added' => 4
                ]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Migration',
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            // ignore logging failure
        }

        echo "Created custom checkup forms system with default templates\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_checkups', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['custom_fields', 'template_id']);
        });

        Schema::dropIfExists('patient_checkup_template_assignments');
        Schema::dropIfExists('custom_checkup_fields');
        Schema::dropIfExists('custom_checkup_templates');

        // Log the rollback
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration_rollback',
            'model_type' => 'CustomCheckupTemplate',
            'model_id' => null,
            'description' => 'Removed custom checkup forms system',
            'changes' => json_encode([
                'migration' => 'create_custom_checkup_forms_rollback',
                'dropped_tables' => ['custom_checkup_templates', 'custom_checkup_fields', 'patient_checkup_template_assignments'],
                'modified_tables' => ['patient_checkups']
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration Rollback',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Removed custom checkup forms system\n";
    }
};
