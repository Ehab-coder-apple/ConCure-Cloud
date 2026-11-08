<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Exports the filtered Invoices Report (Finance > Reports > Invoices) to
 * Excel. Accepts the same normalized, merged row collection (generic
 * Invoice + AestheticInvoice + MedicineSaleInvoice) that the on-screen
 * report and PDF print use, so all three export the exact same data.
 */
class InvoicesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function map($row): array
    {
        return [
            $row->invoice_number,
            $row->date ? $row->date->format('Y-m-d') : '',
            $row->patient_name ?: '',
            $row->category_label,
            $row->item_types ?: '',
            $row->user_name ?: '',
            ucfirst(str_replace('_', ' ', $row->status)),
            number_format($row->subtotal, 2, '.', ''),
            number_format($row->tax_amount, 2, '.', ''),
            number_format($row->discount_amount, 2, '.', ''),
            number_format($row->total_amount, 2, '.', ''),
            number_format($row->paid_amount, 2, '.', ''),
            number_format($row->balance, 2, '.', ''),
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Date',
            'Patient',
            'Category',
            'Item Type(s)',
            'Created By',
            'Status',
            'Subtotal',
            'Tax',
            'Discount',
            'Total',
            'Paid',
            'Balance',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '007BFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
