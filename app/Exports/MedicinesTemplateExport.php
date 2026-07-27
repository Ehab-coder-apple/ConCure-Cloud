<?php

namespace App\Exports;

use App\Imports\MedicinesImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MedicinesTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $includeSampleData;

    public function __construct(bool $includeSampleData = true)
    {
        $this->includeSampleData = $includeSampleData;
    }

    public function array(): array
    {
        if ($this->includeSampleData) {
            return MedicinesImport::getSampleData();
        }

        // Return empty rows for template structure with proper data types
        $headers = array_keys(MedicinesImport::getExpectedHeaders());

        // Create properly formatted empty rows
        $emptyRows = [];
        for ($i = 0; $i < 5; $i++) { // 5 empty rows for user convenience
            $row = [];
            foreach ($headers as $header) {
                $row[$header] = ''; // Empty string for all fields
            }
            $emptyRows[] = $row;
        }

        return $emptyRows;
    }

    public function headings(): array
    {
        return array_keys(MedicinesImport::getExpectedHeaders());
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row with blue background and white text
        $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';

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
