<?php

namespace App\Exports;

use App\Imports\FoodsImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FoodsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $includeSampleData;

    public function __construct(bool $includeSampleData = true)
    {
        $this->includeSampleData = $includeSampleData;
    }

    public function array(): array
    {
        if ($this->includeSampleData) {
            return FoodsImport::getSampleData();
        }

        // Return empty rows for template structure with proper data types
        $headers = array_keys(FoodsImport::getExpectedHeaders());

        // Create properly formatted empty rows
        $emptyRows = [];
        for ($i = 0; $i < 5; $i++) { // 5 empty rows for user convenience
            $row = [];
            foreach ($headers as $header) {
                // Set appropriate default values based on column type
                if (in_array($header, ['calories', 'protein', 'carbohydrates', 'fat', 'fiber', 'sugar', 'sodium'])) {
                    $row[$header] = ''; // Empty string for numeric fields
                } else {
                    $row[$header] = ''; // Empty string for text fields
                }
            }
            $emptyRows[] = $row;
        }

        return $emptyRows;
    }

    public function headings(): array
    {
        return array_keys(FoodsImport::getExpectedHeaders());
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row with teal background and white text
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
                    'startColor' => ['rgb' => '008080'], // Teal color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
