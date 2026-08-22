<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientCheckup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Verifies the "Add Checkup" and "Edit Checkup" forms enable voice-typing
 * (speech-to-text) on their text/textarea fields, so doctors can dictate
 * clinical notes instead of typing them.
 */
class CheckupVoiceInputPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The checkup form views call Patient::assigned_custom_vital_signs
        // and Patient::assigned_checkup_templates, which query these tables
        // even for an in-memory (non-persisted) patient with no rows.
        if (!Schema::hasTable('patient_vital_signs_assignments')) {
            Schema::create('patient_vital_signs_assignments', function ($table) {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('custom_vital_sign_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('patient_checkup_template_assignments')) {
            Schema::create('patient_checkup_template_assignments', function ($table) {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('template_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    private function makePatient(): Patient
    {
        $patient = new Patient([
            'patient_id' => 'P-8001',
            'first_name' => 'Nadia',
            'last_name' => 'Hassan',
        ]);
        $patient->id = 601;
        $patient->exists = true;

        return $patient;
    }

    public function test_add_checkup_form_enables_auto_voice_typing_scope(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $html = view('checkups.create', [
            'patient' => $this->makePatient(),
            'template' => null,
            'patientTemplates' => collect(),
        ])->render();

        $this->assertStringContainsString('data-auto-voice-scope="checkup-form"', $html);
        $this->assertStringContainsString('const VoiceInput', $html);
        // Clinical text fields that should be voice-enabled via the scope.
        $this->assertStringContainsString('name="chief_complaint"', $html);
        $this->assertStringContainsString('name="diagnosis"', $html);
        $this->assertStringContainsString('name="clinical_examination"', $html);
        $this->assertStringContainsString('name="symptoms"', $html);
        $this->assertStringContainsString('name="notes"', $html);
        $this->assertStringContainsString('name="recommendations"', $html);

        // Numeric vital-sign fields should also be voice-enabled.
        $this->assertStringContainsString('input[type="number"]', $html);
        $this->assertStringContainsString('name="weight"', $html);
        $this->assertStringContainsString('name="height"', $html);
        $this->assertStringContainsString('name="heart_rate"', $html);
        $this->assertStringContainsString('name="temperature"', $html);
        $this->assertStringContainsString('name="respiratory_rate"', $html);
        $this->assertStringContainsString('name="blood_sugar"', $html);
    }

    public function test_edit_checkup_form_enables_auto_voice_typing_scope(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $checkup = new PatientCheckup(['checkup_date' => now()]);
        $checkup->id = 1;
        $checkup->exists = true;
        $checkup->setRelation('template', null);

        $html = view('checkups.edit', [
            'patient' => $this->makePatient(),
            'checkup' => $checkup,
        ])->render();

        $this->assertStringContainsString('data-auto-voice-scope="checkup-form"', $html);
        $this->assertStringContainsString('const VoiceInput', $html);
        $this->assertStringContainsString('name="chief_complaint"', $html);
        $this->assertStringContainsString('name="diagnosis"', $html);
        $this->assertStringContainsString('name="clinical_examination"', $html);

        // Numeric vital-sign fields should also be voice-enabled.
        $this->assertStringContainsString('input[type="number"]', $html);
        $this->assertStringContainsString('name="weight"', $html);
        $this->assertStringContainsString('name="height"', $html);
        $this->assertStringContainsString('name="heart_rate"', $html);
        $this->assertStringContainsString('name="temperature"', $html);
        $this->assertStringContainsString('name="respiratory_rate"', $html);
        $this->assertStringContainsString('name="blood_sugar"', $html);
    }

    public function test_shared_voice_input_component_replaces_number_field_value_from_speech(): void
    {
        $html = view('partials.voice-input')->render();

        // Number inputs are auto-enhanced alongside text/textarea fields.
        $this->assertStringContainsString('input[type="number"]', $html);

        // For numeric fields, dictated speech replaces the value with the
        // extracted number (browsers reject non-numeric strings), instead
        // of appending raw text as done for free-text fields.
        $this->assertStringContainsString("field.type === 'number'", $html);
        $this->assertMatchesRegularExpression('/field\.value\s*=\s*numberMatch\[0\]/', $html);
    }
}
