<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Exports the filtered Invoices Report (Finance > Reports > Invoices)
 * to Excel, applying the same period/category/item/user filters as the
 * on-screen report.
 */
class InvoicesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected User $user;
    protected array $filters;

    public function __construct(User $user, array $filters)
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Invoice::with(['patient', 'creator', 'items', 'dentalTreatment'])
            ->where('clinic_id', $this->user->clinic_id)
            ->byDateRange($this->filters['date_from'], $this->filters['date_to']);

        if (!empty($this->filters['category'])) {
            if ($this->filters['category'] === 'dental') {
                $query->whereHas('dentalTreatment');
            } elseif ($this->filters['category'] === 'finance') {
                $query->whereDoesntHave('dentalTreatment')
                    ->where(function ($q) {
                        $q->whereNull('source_module')->orWhere('source_module', 'finance');
                    });
            } else {
                $query->where('source_module', $this->filters['category']);
            }
        }

        if (!empty($this->filters['item_type'])) {
            $query->whereHas('items', function ($q) {
                $q->where('item_type', $this->filters['item_type']);
            });
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('created_by', $this->filters['user_id']);
        }

        return $query->orderByDesc('invoice_date')->orderByDesc('id')->get();
    }

    public function map($invoice): array
    {
        $category = $invoice->dentalTreatment ? 'Dental' : ucfirst(str_replace('_', ' ', $invoice->source_module ?: 'finance'));

        return [
            $invoice->invoice_number,
            $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '',
            $invoice->patient ? $invoice->patient->full_name : '',
            $category,
            $invoice->items->pluck('item_type')->filter()->unique()->map(fn ($t) => ucfirst(str_replace('_', ' ', $t)))->implode(', '),
            $invoice->creator ? $invoice->creator->full_name : '',
            ucfirst(str_replace('_', ' ', $invoice->status)),
            number_format((float) $invoice->subtotal, 2, '.', ''),
            number_format((float) $invoice->tax_amount, 2, '.', ''),
            number_format((float) $invoice->discount_amount, 2, '.', ''),
            number_format((float) $invoice->total_amount, 2, '.', ''),
            number_format((float) $invoice->paid_amount, 2, '.', ''),
            number_format((float) $invoice->balance, 2, '.', ''),
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
