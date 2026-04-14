<?php

namespace Tests\Feature;

use App\Models\CustomCheckupTemplate;
use App\Models\Patient;
use App\Models\PatientCheckupTemplateAssignment;
use Tests\TestCase;

class CheckupTemplateSelectorPresentationTest extends TestCase
{
    public function test_template_selector_renders_default_form_and_assigned_templates(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-100',
            'first_name' => 'Lina',
            'last_name' => 'Ahmad',
        ]);
        $patient->setAttribute('id', 100);

        $template = new CustomCheckupTemplate([
            'name' => 'Diabetes Follow-up',
            'description' => 'Structured follow-up for diabetic patients.',
        ]);
        $template->setAttribute('id', 7);

        $assignment = new PatientCheckupTemplateAssignment([
            'template_id' => 7,
            'medical_condition' => 'Diabetes',
        ]);
        $assignment->setRelation('template', $template);

        $html = view('checkups.partials.template-selector', [
            'patient' => $patient,
            'patientTemplates' => collect([$assignment]),
            'selectedTemplateId' => 7,
            'template' => $template,
        ])->render();

        $this->assertStringContainsString('Default checkup form', $html);
        $this->assertStringContainsString('Diabetes Follow-up', $html);
        $this->assertStringContainsString('Selected template:', $html);
        $this->assertStringContainsString('value="7" selected', $html);
    }

    public function test_template_selector_shows_default_form_message_when_no_templates_are_assigned(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-101',
            'first_name' => 'Omar',
            'last_name' => 'Saleh',
        ]);
        $patient->setAttribute('id', 101);

        $html = view('checkups.partials.template-selector', [
            'patient' => $patient,
            'patientTemplates' => collect(),
            'selectedTemplateId' => null,
            'template' => null,
        ])->render();

        $this->assertStringContainsString('Default checkup form', $html);
        $this->assertStringContainsString('No custom checkup templates are assigned to this patient yet', $html);
    }
}