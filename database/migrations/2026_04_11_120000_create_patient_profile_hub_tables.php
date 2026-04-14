<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medical_overviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->cascadeOnDelete();
            $table->text('allergies')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->text('surgeries')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('current_medications_summary')->nullable();
            $table->json('flags')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('module_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['patient_id', 'module_name']);
        });

        Schema::create('patient_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('route')->nullable();
            $table->string('indication')->nullable();
            $table->string('status')->default('current');
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('visit_date');
            $table->string('visit_type')->default('consultation');
            $table->string('status')->default('completed');
            $table->text('reason_for_visit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_hpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->unique()->constrained('visits')->cascadeOnDelete();
            $table->text('chief_complaint')->nullable();
            $table->longText('hpi_summary')->nullable();
            $table->text('associated_symptoms')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->timestamps();
        });

        $this->backfillMedicalOverviews();
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_hpis');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('patient_medications');
        Schema::dropIfExists('patient_modules');
        Schema::dropIfExists('patient_medical_overviews');
    }

    private function backfillMedicalOverviews(): void
    {
        if (!Schema::hasTable('patients')) {
            return;
        }

        DB::table('patients')
            ->select('id', 'allergies', 'chronic_illnesses', 'surgeries_history', 'medical_history', 'is_pregnant')
            ->orderBy('id')
            ->chunkById(100, function ($patients): void {
                $timestamp = now();
                $rows = [];

                foreach ($patients as $patient) {
                    $flags = array_filter([
                        'pregnant' => (bool) $patient->is_pregnant,
                    ]);

                    if (!$patient->allergies
                        && !$patient->chronic_illnesses
                        && !$patient->surgeries_history
                        && !$patient->medical_history
                        && empty($flags)) {
                        continue;
                    }

                    $rows[] = [
                        'patient_id' => $patient->id,
                        'allergies' => $patient->allergies,
                        'chronic_diseases' => $patient->chronic_illnesses,
                        'surgeries' => $patient->surgeries_history,
                        'medical_history' => $patient->medical_history,
                        'current_medications_summary' => null,
                        'flags' => empty($flags) ? null : json_encode($flags),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                if ($rows !== []) {
                    DB::table('patient_medical_overviews')->upsert(
                        $rows,
                        ['patient_id'],
                        ['allergies', 'chronic_diseases', 'surgeries', 'medical_history', 'flags', 'updated_at']
                    );
                }
            });
    }
};