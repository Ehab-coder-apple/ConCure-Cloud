<?php

namespace Tests\Feature;

use App\Models\PatientCheckup;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class CheckupClinicalFieldsPresentationTest extends TestCase
{
    public function test_fixed_clinical_fields_partial_renders_standard_inputs(): void
    {
        $html = view('checkups.partials.fixed-clinical-fields')
            ->with('errors', new ViewErrorBag())
            ->render();

        $this->assertStringContainsString('name="chief_complaint"', $html);
        $this->assertStringContainsString('name="diagnosis"', $html);
        $this->assertStringContainsString('name="clinical_examination"', $html);
    }

    public function test_reserved_clinical_custom_field_keys_cover_fixed_and_legacy_template_names(): void
    {
        $keys = PatientCheckup::reservedClinicalCustomFieldKeys();

        $this->assertContains('chief_complaint', $keys);
        $this->assertContains('diagnosis', $keys);
        $this->assertContains('physical_examination', $keys);
        $this->assertContains('clinical_examination', $keys);
        $this->assertContains('assessment', $keys);
        $this->assertContains('exam', $keys);
    }
}