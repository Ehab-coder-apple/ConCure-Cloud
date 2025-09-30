<?php

namespace App\Exports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PatientsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $clinicId;

    public function __construct($clinicId = null)
    {
        $this->clinicId = $clinicId;
    }

    /**
     * Get the patients collection to export
     */
    public function collection()
    {
        $query = Patient::with(['clinic', 'creator']);

        if ($this->clinicId) {
            $query->where('clinic_id', $this->clinicId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Map each patient to an array for export
     */
    public function map($patient): array
    {
        return [
            $patient->patient_id ?? 'N/A',
            $patient->first_name,
            $patient->last_name,
            $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '',
            $patient->age ?? '',
            ucfirst($patient->gender ?? ''),
            $patient->phone ?? '',
            $patient->whatsapp_phone ?? '',
            $patient->email ?? '',
            $patient->address ?? '',
            $patient->job ?? '',
            $patient->education ?? '',
            $patient->height ?? '',
            $patient->weight ?? '',
            $patient->bmi ?? '',
            $patient->bmi_category ?? '',
            $patient->blood_type ?? '',
            $patient->allergies ?? '',
            $patient->is_pregnant ? 'Yes' : 'No',
            $patient->chronic_illnesses ?? '',
            $patient->surgeries_history ?? '',
            $patient->diet_history ?? '',
            $patient->medical_history ?? '',
            $patient->notes ?? '',
            $patient->emergency_contact_name ?? '',
            $patient->emergency_contact_phone ?? '',
            $patient->clinic ? $patient->clinic->name : '',
            $patient->creator ? $patient->creator->name : '',
            $patient->is_active ? 'Active' : 'Inactive',
            $patient->created_at ? $patient->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Define the headings for the export
     */
    public function headings(): array
    {
        return [
            'Patient ID',
            'First Name',
            'Last Name',
            'Date of Birth',
            'Age',
            'Gender',
            'Phone',
            'WhatsApp Phone',
            'Email',
            'Address',
            'Job',
            'Education',
            'Height (cm)',
            'Weight (kg)',
            'BMI',
            'BMI Category',
            'Blood Type',
            'Allergies',
            'Is Pregnant',
            'Chronic Illnesses',
            'Surgeries History',
            'Diet History',
            'Medical History',
            'Notes',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Clinic',
            'Created By',
            'Status',
            'Created At',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D6EFD'], // Bootstrap primary blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}

