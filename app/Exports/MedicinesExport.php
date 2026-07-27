<?php

namespace App\Exports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MedicinesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $clinicId;
    protected $user;

    public function __construct($clinicId = null, $user = null)
    {
        $this->clinicId = $clinicId;
        $this->user = $user;
    }

    /**
     * Get the medicines collection to export
     */
    public function collection()
    {
        $query = Medicine::with(['clinic', 'creator']);

        // Apply role-based filtering if user is provided
        if ($this->user) {
            $query->visibleToUser($this->user);
        } elseif ($this->clinicId) {
            // Fallback to clinic filtering if no user provided
            $query->where('clinic_id', $this->clinicId);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    /**
     * Map each medicine to an array for export
     */
    public function map($medicine): array
    {
        return [
            $medicine->name,
            $medicine->generic_name ?? '',
            $medicine->brand_name ?? '',
            $medicine->dosage ?? '',
            $medicine->form_display ?? '',
            $medicine->description ?? '',
            $medicine->side_effects ?? '',
            $medicine->contraindications ?? '',
            $medicine->is_frequent ? 'Yes' : 'No',
            $medicine->clinic ? $medicine->clinic->name : '',
            $medicine->creator ? $medicine->creator->name : '',
            $medicine->is_active ? 'Active' : 'Inactive',
            $medicine->created_at ? $medicine->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Define the headings for the export
     */
    public function headings(): array
    {
        return [
            'Name',
            'Generic Name',
            'Brand Name',
            'Dosage',
            'Form',
            'Description',
            'Side Effects',
            'Contraindications',
            'Is Frequent',
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
                    'startColor' => ['rgb' => '007BFF'], // Blue color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}

