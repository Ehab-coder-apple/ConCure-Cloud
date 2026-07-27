<?php

namespace App\Exports;

use App\Models\Food;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FoodsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $clinicId;
    protected $excludeSystemData;

    public function __construct($clinicId = null, bool $excludeSystemData = false)
    {
        $this->clinicId = $clinicId;
        $this->excludeSystemData = $excludeSystemData;
    }

    /**
     * Get the foods collection to export
     */
    public function collection()
    {
        $query = Food::with(['foodGroup', 'clinic', 'creator']);

        if ($this->clinicId) {
            $query->where('clinic_id', $this->clinicId);
        }

        // Exclude system/standard foods if user is not master admin
        if ($this->excludeSystemData) {
            $query->where('is_custom', true);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    /**
     * Map each food to an array for export
     */
    public function map($food): array
    {
        // Collect name translations (fallbacks handled by model accessors)
        $nameEn = $food->getNameTranslation('en') ?? $food->name;
        $nameAr = $food->getNameTranslation('ar') ?? '';
        $nameKuBahdini = $food->getNameTranslation('ku_bahdini') ?? '';
        $nameKuSorani = $food->getNameTranslation('ku_sorani') ?? '';

        return [
            $food->name,
            $nameEn,
            $nameAr,
            $nameKuBahdini,
            $nameKuSorani,
            $food->foodGroup ? $food->foodGroup->name : '',
            $food->description ?? '',
            $food->calories ?? '',
            $food->protein ?? '',
            $food->carbohydrates ?? '',
            $food->fat ?? '',
            $food->fiber ?? '',
            $food->sugar ?? '',
            $food->sodium ?? '',
            $food->potassium ?? '',
            $food->calcium ?? '',
            $food->iron ?? '',
            $food->vitamin_c ?? '',
            $food->vitamin_a ?? '',
            $food->serving_size ?? '',
            $food->serving_weight ?? '',
            $food->grams_per_piece ?? '',
            $food->is_custom ? 'Yes' : 'No',
            $food->clinic ? $food->clinic->name : 'Global',
            $food->creator ? $food->creator->name : '',
            $food->is_active ? 'Active' : 'Inactive',
            $food->created_at ? $food->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Define the headings for the export
     */
    public function headings(): array
    {
        return [
            'Name',
            'Name (EN)',
            'Name (AR)',
            'Name (KU Bahdini)',
            'Name (KU Sorani)',
            'Food Group',
            'Description',
            'Calories (per 100g)',
            'Protein (g)',
            'Carbohydrates (g)',
            'Fat (g)',
            'Fiber (g)',
            'Sugar (g)',
            'Sodium (mg)',
            'Potassium (mg)',
            'Calcium (mg)',
            'Iron (mg)',
            'Vitamin C (mg)',
            'Vitamin A (μg)',
            'Serving Size',
            'Serving Weight (g)',
            'Grams Per Piece',
            'Is Custom',
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

