<?php

namespace Tests\Feature;

use App\Models\CustomCheckupTemplate;
use Tests\TestCase;

class CustomCheckupTemplateClinicalDefaultsTest extends TestCase
{
    public function test_normalize_form_config_adds_built_in_clinical_summary_fields(): void
    {
        $config = CustomCheckupTemplate::normalizeFormConfig([
            'sections' => [
                'history' => [
                    'title' => 'History',
                    'fields' => [
                        'duration' => ['type' => 'text', 'label' => 'Duration'],
                    ],
                ],
            ],
        ]);

        $this->assertArrayHasKey('clinical_summary', $config['sections']);
        $this->assertArrayHasKey('history', $config['sections']);
        $this->assertArrayHasKey('chief_complaint', $config['sections']['clinical_summary']['fields']);
        $this->assertArrayHasKey('diagnosis', $config['sections']['clinical_summary']['fields']);
        $this->assertArrayHasKey('physical_examination', $config['sections']['clinical_summary']['fields']);
    }

    public function test_normalize_form_config_moves_clinical_alias_fields_into_clinical_summary(): void
    {
        $config = CustomCheckupTemplate::normalizeFormConfig([
            'sections' => [
                'assessment' => [
                    'title' => 'Assessment',
                    'fields' => [
                        'diagnosis' => ['type' => 'textarea', 'label' => 'Diagnosis Notes'],
                        'exam' => ['type' => 'textarea', 'label' => 'Clinical Exam'],
                    ],
                ],
            ],
        ]);

        $this->assertSame('Diagnosis Notes', $config['sections']['clinical_summary']['fields']['diagnosis']['label']);
        $this->assertSame('Clinical Exam', $config['sections']['clinical_summary']['fields']['physical_examination']['label']);
        $this->assertArrayNotHasKey('diagnosis', $config['sections']['assessment']['fields']);
        $this->assertArrayNotHasKey('exam', $config['sections']['assessment']['fields']);
    }

    public function test_form_sections_accessor_includes_built_in_clinical_summary(): void
    {
        $template = new CustomCheckupTemplate([
            'form_config' => ['sections' => []],
        ]);

        $this->assertArrayHasKey('clinical_summary', $template->form_sections);
        $this->assertSame('Clinical Summary', $template->form_sections['clinical_summary']['title']);
    }
}