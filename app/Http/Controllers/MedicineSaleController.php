<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Medicine;
use App\Models\MedicineSaleInvoice;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Services\ThermalReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;

class MedicineSaleController extends Controller
{
    /**
     * Show the multi-item sell form.
     */
    public function create()
    {
        $user = Auth::user();
        Gate::authorize('create', Medicine::class);

        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        $medicines = Medicine::visibleToUser($user)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'generic_name', 'brand_name', 'dosage', 'form', 'stock_quantity', 'selling_price']);

        return view('medicines.sales.create', compact('patients', 'medicines'));
    }

    /**
     * Process a multi-item medicine sale: one invoice, N stock-decrementing items.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        Gate::authorize('create', Medicine::class);

        $data = $request->validate([
            'patient_id'        => 'nullable|exists:patients,id',
            'payment_method'    => 'required|in:cash,card,credit,insurance,other',
            'discount'          => 'nullable|numeric|min:0|max:9999999',
            'tax'               => 'nullable|numeric|min:0|max:9999999',
            'paid_amount'       => 'nullable|numeric|min:0|max:99999999',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1|max:100',
            'items.*.medicine_id' => 'required|integer|distinct|exists:medicines,id',
            'items.*.quantity'    => 'required|numeric|min:0.01|max:1000000|regex:/^\d+(\.\d{1,2})?$/',
            'items.*.unit_price'  => 'required|numeric|min:0|max:99999999',
        ], [
            'items.required'       => __('Add at least one medicine to the sale.'),
            'items.*.medicine_id.distinct' => __('The same medicine cannot be added twice — increase its quantity instead.'),
            'items.*.quantity.regex' => __('Quantity must be a number with up to 2 decimal places.'),
        ]);

        $items = $data['items'];

        DB::beginTransaction();
        try {
            // Lock all referenced medicines in one query, scoped to the user's clinic + visibility,
            // so we evaluate stock against the freshest values.
            $ids = collect($items)->pluck('medicine_id')->all();
            $medicines = Medicine::visibleToUser($user)
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($medicines->count() !== count(array_unique($ids))) {
                DB::rollBack();
                return back()->withInput()->with('error', __('One or more medicines are unavailable in your clinic.'));
            }

            // Per-item authorization mirrors the legacy single-item flow.
            foreach ($medicines as $med) {
                Gate::authorize('update', $med);
            }

            $subtotal = 0.0;
            $createdItems = [];
            foreach ($items as $row) {
                /** @var Medicine $medicine */
                $medicine = $medicines[$row['medicine_id']];
                $qty   = (float) $row['quantity'];
                $price = (float) $row['unit_price'];

                if ((float) $medicine->stock_quantity < $qty) {
                    DB::rollBack();
                    return back()->withInput()->with('error', __(
                        'Insufficient stock for :name (available: :stock).',
                        ['name' => $medicine->name, 'stock' => (float) $medicine->stock_quantity]
                    ));
                }

                $lineTotal = round($qty * $price, 2);
                $subtotal += $lineTotal;
                $createdItems[] = compact('medicine', 'qty', 'price', 'lineTotal');
            }

            $discount = (float) ($data['discount'] ?? 0);
            $tax      = (float) ($data['tax'] ?? 0);
            $total    = max(0.0, round($subtotal - $discount + $tax, 2));
            $paid     = isset($data['paid_amount']) ? (float) $data['paid_amount'] : $total;

            $invoice = MedicineSaleInvoice::create([
                'clinic_id'      => $user->clinic_id,
                'user_id'        => $user->id,
                'patient_id'     => $data['patient_id'] ?? null,
                'invoice_number' => MedicineSaleInvoice::generateInvoiceNumber($user->clinic_id),
                'payment_method' => $data['payment_method'],
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => $paid,
                'notes'          => $data['notes'] ?? null,
                'sold_at'        => now(),
            ]);

            foreach ($createdItems as $i) {
                $stockBefore = (float) $i['medicine']->stock_quantity;

                MedicineTransaction::create([
                    'medicine_id'              => $i['medicine']->id,
                    'clinic_id'                => $user->clinic_id,
                    'user_id'                  => $user->id,
                    'medicine_sale_invoice_id' => $invoice->id,
                    'type'                     => 'sale',
                    'quantity'                 => $i['qty'],
                    'unit_price'               => $i['price'],
                    'total_amount'             => $i['lineTotal'],
                    'reference_number'         => $invoice->invoice_number,
                    'patient_id'               => $invoice->patient_id,
                    'payment_method'           => $invoice->payment_method,
                    'stock_before'             => $stockBefore,
                    'stock_after'              => $stockBefore - $i['qty'],
                    'transaction_date'         => now(),
                ]);

                $i['medicine']->decrement('stock_quantity', $i['qty']);
            }

            DB::commit();

            return redirect()
                ->route('medicines.sales.show', $invoice)
                ->with('success', __('Sale recorded. Invoice :no.', ['no' => $invoice->invoice_number]));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('Sale failed: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Show a sale invoice / receipt.
     */
    public function show(MedicineSaleInvoice $invoice)
    {
        $this->authorizeView($invoice);
        $invoice->load(['items.medicine:id,name,dosage,form', 'patient', 'user']);

        return view('medicines.sales.show', compact('invoice'));
    }

    /**
     * Download the invoice as a regular A4 PDF (DomPDF).
     */
    public function pdf(MedicineSaleInvoice $invoice)
    {
        $this->authorizeView($invoice);
        $invoice->load(['items.medicine:id,name,dosage,form', 'patient', 'user', 'clinic']);

        $clinic = $invoice->clinic ?: Clinic::find($invoice->clinic_id);
        $currency = $this->resolveCurrency($invoice->clinic_id);

        $pdf = Pdf::loadView('medicines.sales.pdf', compact('invoice', 'clinic', 'currency'))
            ->setPaper('a4');

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Render the invoice as a thermal printer-friendly receipt (58mm / 80mm).
     */
    public function thermal(Request $request, MedicineSaleInvoice $invoice, ThermalReceiptService $thermal)
    {
        $this->authorizeView($invoice);
        $invoice->load(['items.medicine:id,name,dosage,form', 'patient', 'user', 'clinic']);

        $clinic = $invoice->clinic ?: Clinic::find($invoice->clinic_id);
        $widthMm = (int) $request->query('width', 0);
        if (!in_array($widthMm, ThermalReceiptService::ALLOWED_WIDTHS, true)) {
            $widthMm = ThermalReceiptService::DEFAULT_WIDTH;
        }

        $currency = $this->resolveCurrency($invoice->clinic_id);
        $symbol = $this->currencySymbol($currency);

        $items = $invoice->items->map(function ($item) {
            $name = $item->medicine->name ?? __('Unknown');
            if (!empty($item->medicine->dosage)) {
                $name .= ' ' . $item->medicine->dosage;
            }
            return [
                'name'       => $name,
                'qty'        => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total'      => (float) $item->total_amount,
            ];
        })->all();

        $financials = [
            'currency'        => $currency,
            'currency_symbol' => $symbol,
            'subtotal'        => (float) $invoice->subtotal,
            'discount'        => (float) $invoice->discount,
            'tax'             => (float) $invoice->tax,
            'total'           => (float) $invoice->total,
            'paid'            => (float) $invoice->paid_amount,
            'balance'         => (float) $invoice->total - (float) $invoice->paid_amount,
            'method'          => ucfirst((string) $invoice->payment_method),
            'receipt_number'  => $invoice->invoice_number,
            'notes'           => $invoice->notes,
        ];

        $payload = [
            'title'         => __('Pharmacy Receipt'),
            'reference'     => $invoice->invoice_number,
            'clinic'        => $clinic,
            'patient'       => $invoice->patient,
            'doctor_label'  => __('Cashier'),
            'doctor_name'   => optional($invoice->user)->full_name ?: optional($invoice->user)->username,
            'meta'          => [
                ['label' => __('Invoice'), 'value' => $invoice->invoice_number],
                ['label' => __('Date'), 'value' => $invoice->sold_at?->format('Y-m-d H:i') ?? '-'],
            ],
            'services'      => [],
            'items'         => $items,
            'financials'    => $financials,
            'qr_payload'    => $invoice->invoice_number,
            'qr_svg'        => $thermal->qrSvg($invoice->invoice_number),
            'width_mm'      => $widthMm,
            'printed_by'    => optional(Auth::user())->full_name ?: optional(Auth::user())->username,
            'printed_at'    => Carbon::now(),
            'thank_you'     => __('Thank you for your purchase'),
            'locale'        => app()->getLocale(),
            'is_rtl'        => in_array(app()->getLocale(), ['ar', 'ku', 'fa', 'he'], true),
            'auto_print'    => $request->boolean('auto', true),
        ];

        return view('receipts.thermal', $payload);
    }

    /**
     * Authorize a clinic-scoped view of the invoice.
     */
    protected function authorizeView(MedicineSaleInvoice $invoice): void
    {
        $user = Auth::user();
        if ($invoice->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403);
        }
    }

    /**
     * Read the clinic's configured currency code (defaults to USD).
     */
    protected function resolveCurrency(int $clinicId): string
    {
        $code = DB::table('settings')
            ->where('clinic_id', $clinicId)
            ->where('key', 'currency')
            ->value('value');
        return is_string($code) && $code !== '' ? strtoupper($code) : 'USD';
    }

    /**
     * Map ISO currency code to a printable symbol.
     */
    protected function currencySymbol(string $code): string
    {
        return [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£',
            'IQD' => 'IQD', 'SAR' => 'SAR', 'AED' => 'AED',
            'EGP' => 'EGP', 'KWD' => 'KWD',
        ][strtoupper($code)] ?? $code;
    }
}
