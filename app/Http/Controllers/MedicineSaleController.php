<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\InvoiceReturn;
use App\Models\Medicine;
use App\Models\MedicineSaleInvoice;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Models\ReturnItem;
use App\Services\ThermalReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

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
            ->get(['id', 'name', 'generic_name', 'brand_name', 'dosage', 'form', 'stock_quantity', 'selling_price', 'barcode']);

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
     * JSON snapshot of an invoice's line items, used to populate the
     * Return Items / Edit Invoice modals from the Finance ledger table
     * without preloading every invoice's items up front.
     */
    public function itemsJson(MedicineSaleInvoice $invoice)
    {
        $this->authorizeView($invoice);
        $invoice->load(['items.medicine:id,name,dosage,form,stock_quantity', 'patient']);

        return response()->json([
            'invoice' => [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status'         => $invoice->status,
                'subtotal'       => (float) $invoice->subtotal,
                'discount'       => (float) $invoice->discount,
                'tax'            => (float) $invoice->tax,
                'total'          => (float) $invoice->total,
                'paid_amount'    => (float) $invoice->paid_amount,
                'payment_method' => $invoice->payment_method,
                'patient_name'   => $invoice->patient ? trim($invoice->patient->first_name . ' ' . $invoice->patient->last_name) : null,
                'is_mutable'     => $invoice->isMutable(),
            ],
            'items' => $invoice->items->map(function (MedicineTransaction $item) {
                return [
                    'transaction_id'      => $item->id,
                    'medicine_id'         => $item->medicine_id,
                    'medicine_name'       => $item->medicine->name ?? __('Unknown medicine'),
                    'medicine_dosage'     => $item->medicine->dosage ?? null,
                    'medicine_form'       => $item->medicine->form ?? null,
                    'medicine_stock'      => $item->medicine ? (float) $item->medicine->stock_quantity : null,
                    'quantity'            => (float) $item->quantity,
                    'unit_price'          => (float) $item->unit_price,
                    'total_amount'        => (float) $item->total_amount,
                    'returned_quantity'   => $item->returnedQuantity(),
                    'remaining_returnable'=> $item->remainingReturnableQuantity(),
                ];
            })->values(),
            'reason_codes'        => InvoiceReturn::REASON_CODES,
            'cancel_reason_codes' => MedicineSaleInvoice::CANCEL_REASON_CODES,
        ]);
    }

    /**
     * POST /medicines/sales/{invoice}/returns
     *
     * Partial or full return of one or more sold line items:
     *  - Validates the returned quantity never exceeds what is still
     *    outstanding on that line (sold - already returned).
     *  - Puts the returned quantity back into the medicine's active stock.
     *  - Records a credit note (InvoiceReturn + ReturnItem rows) and
     *    deducts the refund from the invoice's live subtotal/total/paid
     *    fields, which is what every finance/dashboard revenue sum() reads
     *    from - so daily/monthly pharmacy revenue reflects the return
     *    immediately with no extra ledger bookkeeping needed.
     */
    public function storeReturn(Request $request, MedicineSaleInvoice $invoice)
    {
        $this->authorizeManage($invoice);

        if ($invoice->isCancelled()) {
            return $this->failure($request, __('This invoice was cancelled and cannot be returned against.'), 422);
        }

        $data = $request->validate([
            'reason_code' => ['required', Rule::in(array_keys(InvoiceReturn::REASON_CODES))],
            'notes'       => 'nullable|string|max:1000',
            'items'                        => 'required|array|min:1',
            'items.*.transaction_id'      => 'required|integer|distinct',
            'items.*.quantity'            => 'required|numeric|min:0.01|max:1000000',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $transactionIds = collect($data['items'])->pluck('transaction_id')->all();

            $transactions = MedicineTransaction::with('medicine')
                ->where('medicine_sale_invoice_id', $invoice->id)
                ->where('type', 'sale')
                ->whereIn('id', $transactionIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($transactions->count() !== count(array_unique($transactionIds))) {
                DB::rollBack();
                return $this->failure($request, __('One or more selected items do not belong to this invoice.'), 422);
            }

            $refundTotal = 0.0;
            $lines = [];
            foreach ($data['items'] as $row) {
                /** @var MedicineTransaction $transaction */
                $transaction = $transactions[$row['transaction_id']];
                $qty = (float) $row['quantity'];
                $remaining = $transaction->remainingReturnableQuantity();

                if ($qty > $remaining + 0.0001) {
                    DB::rollBack();
                    return $this->failure($request, __(
                        'Cannot return :qty of :name - only :remaining remain available to return.',
                        ['qty' => $qty, 'name' => $transaction->medicine->name ?? '#' . $transaction->medicine_id, 'remaining' => $remaining]
                    ), 422);
                }

                $lineTotal = round($qty * (float) $transaction->unit_price, 2);
                $refundTotal += $lineTotal;
                $lines[] = [
                    'transaction' => $transaction,
                    'qty'         => $qty,
                    'lineTotal'   => $lineTotal,
                ];
            }

            $invoiceReturn = InvoiceReturn::create([
                'clinic_id'            => $invoice->clinic_id,
                'invoice_id'           => $invoice->id,
                'returned_by_user_id'  => $user->id,
                'reason_code'          => $data['reason_code'],
                'notes'                => $data['notes'] ?? null,
                'refund_amount'        => round($refundTotal, 2),
            ]);

            foreach ($lines as $line) {
                /** @var MedicineTransaction $transaction */
                $transaction = $line['transaction'];

                ReturnItem::create([
                    'invoice_return_id'       => $invoiceReturn->id,
                    'medicine_transaction_id' => $transaction->id,
                    'medicine_id'             => $transaction->medicine_id,
                    'quantity_returned'       => $line['qty'],
                    'unit_price'              => $transaction->unit_price,
                    'line_total'              => $line['lineTotal'],
                    'reason_code'             => $data['reason_code'],
                ]);

                if ($transaction->medicine) {
                    $transaction->medicine->increment('stock_quantity', $line['qty']);
                }
            }

            $invoice->snapshotOriginalTotalsIfNeeded();
            $invoice->subtotal    = max(0, round((float) $invoice->subtotal - $refundTotal, 2));
            $invoice->total       = max(0, round((float) $invoice->total - $refundTotal, 2));
            $invoice->paid_amount = max(0, round((float) $invoice->paid_amount - $refundTotal, 2));

            $totalSold = (float) $invoice->items()->where('type', 'sale')->sum('quantity');
            $totalReturned = (float) ReturnItem::whereHas('invoiceReturn', fn ($q) => $q->where('invoice_id', $invoice->id))
                ->sum('quantity_returned');

            $invoice->status = $totalReturned >= $totalSold - 0.0001
                ? MedicineSaleInvoice::STATUS_RETURNED
                : MedicineSaleInvoice::STATUS_PARTIALLY_RETURNED;
            $invoice->save();

            DB::commit();

            return $this->success($request, $invoice->fresh(), __('Return processed. Refund: :amount', ['amount' => number_format($refundTotal, 2)]));
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->failure($request, __('Return failed: :msg', ['msg' => $e->getMessage()]), 500);
        }
    }

    /**
     * PUT /medicines/sales/{invoice}
     *
     * Edits quantities/unit prices of existing sold line items. For each
     * submitted line, computes the delta between old and new quantity and
     * adjusts medicine stock accordingly (reduced quantity reverts stock,
     * increased quantity deducts further stock). Setting a line's quantity
     * to 0 removes it entirely and fully reverts its stock. Lines not
     * included in the submission are left untouched. Totals/tax/balance
     * are recalculated from the resulting line items.
     */
    public function update(Request $request, MedicineSaleInvoice $invoice)
    {
        $this->authorizeManage($invoice);

        if ($invoice->isCancelled()) {
            return $this->failure($request, __('This invoice was cancelled and cannot be edited.'), 422);
        }

        $data = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.transaction_id'   => 'required|integer|distinct',
            'items.*.quantity'         => 'required|numeric|min:0|max:1000000',
            'items.*.unit_price'       => 'required|numeric|min:0|max:99999999',
            'discount'      => 'nullable|numeric|min:0|max:9999999',
            'tax'           => 'nullable|numeric|min:0|max:9999999',
            'paid_amount'   => 'nullable|numeric|min:0|max:99999999',
            'payment_method' => 'nullable|in:cash,card,credit,insurance,other',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $transactionIds = collect($data['items'])->pluck('transaction_id')->all();

            $transactions = MedicineTransaction::with('medicine')
                ->where('medicine_sale_invoice_id', $invoice->id)
                ->where('type', 'sale')
                ->whereIn('id', $transactionIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($transactions->count() !== count(array_unique($transactionIds))) {
                DB::rollBack();
                return $this->failure($request, __('One or more selected items do not belong to this invoice.'), 422);
            }

            foreach ($data['items'] as $row) {
                /** @var MedicineTransaction $transaction */
                $transaction = $transactions[$row['transaction_id']];
                $medicine = $transaction->medicine;
                $oldQty = (float) $transaction->quantity;
                $newQty = (float) $row['quantity'];
                $newPrice = (float) $row['unit_price'];
                $delta = $newQty - $oldQty;

                if ($delta > 0 && $medicine && (float) $medicine->stock_quantity < $delta) {
                    DB::rollBack();
                    return $this->failure($request, __(
                        'Cannot increase :name to :qty - only :stock additional units in stock.',
                        ['name' => $medicine->name, 'qty' => $newQty, 'stock' => (float) $medicine->stock_quantity]
                    ), 422);
                }

                if ($medicine && $delta !== 0.0) {
                    if ($delta > 0) {
                        $medicine->decrement('stock_quantity', $delta);
                    } else {
                        $medicine->increment('stock_quantity', abs($delta));
                    }
                }

                if ($newQty <= 0.0001) {
                    $transaction->delete();
                    continue;
                }

                $transaction->update([
                    'quantity'     => $newQty,
                    'unit_price'   => $newPrice,
                    'total_amount' => round($newQty * $newPrice, 2),
                    'stock_after'  => $medicine ? (float) $medicine->fresh()->stock_quantity : $transaction->stock_after,
                ]);
            }

            $invoice->snapshotOriginalTotalsIfNeeded();

            $subtotal = (float) $invoice->items()->where('type', 'sale')->sum('total_amount');
            $discount = array_key_exists('discount', $data) ? (float) $data['discount'] : (float) $invoice->discount;
            $tax      = array_key_exists('tax', $data) ? (float) $data['tax'] : (float) $invoice->tax;
            $total    = max(0.0, round($subtotal - $discount + $tax, 2));
            $paid     = array_key_exists('paid_amount', $data) ? (float) $data['paid_amount'] : (float) $invoice->paid_amount;
            $paid     = min($paid, $total);

            $invoice->subtotal = round($subtotal, 2);
            $invoice->discount = round($discount, 2);
            $invoice->tax = round($tax, 2);
            $invoice->total = $total;
            $invoice->paid_amount = round($paid, 2);
            if (!empty($data['payment_method'])) {
                $invoice->payment_method = $data['payment_method'];
            }
            if ($invoice->status === MedicineSaleInvoice::STATUS_COMPLETED) {
                $invoice->status = MedicineSaleInvoice::STATUS_EDITED;
            }
            $invoice->edited_at = now();
            $invoice->edited_by = $user->id;
            $invoice->save();

            DB::commit();

            return $this->success($request, $invoice->fresh(), __('Invoice updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->failure($request, __('Update failed: :msg', ['msg' => $e->getMessage()]), 500);
        }
    }

    /**
     * POST /medicines/sales/{invoice}/cancel
     *
     * Voids the invoice entirely: zeroes its financial fields (so revenue
     * ledgers immediately exclude it) and returns 100% of each line's
     * still-outstanding quantity (sold minus whatever was already returned
     * individually) back into active inventory stock.
     */
    public function cancel(Request $request, MedicineSaleInvoice $invoice)
    {
        $this->authorizeManage($invoice);

        if ($invoice->isCancelled()) {
            return $this->failure($request, __('This invoice is already cancelled.'), 422);
        }

        $data = $request->validate([
            'reason_code' => ['required', Rule::in(array_keys(MedicineSaleInvoice::CANCEL_REASON_CODES))],
            'notes'       => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $transactions = MedicineTransaction::with('medicine')
                ->where('medicine_sale_invoice_id', $invoice->id)
                ->where('type', 'sale')
                ->lockForUpdate()
                ->get();

            foreach ($transactions as $transaction) {
                $remaining = $transaction->remainingReturnableQuantity();
                if ($remaining > 0 && $transaction->medicine) {
                    $transaction->medicine->increment('stock_quantity', $remaining);
                }
            }

            $invoice->snapshotOriginalTotalsIfNeeded();
            $invoice->subtotal = 0;
            $invoice->discount = 0;
            $invoice->tax = 0;
            $invoice->total = 0;
            $invoice->paid_amount = 0;
            $invoice->status = MedicineSaleInvoice::STATUS_CANCELLED;
            $invoice->cancelled_at = now();
            $invoice->cancelled_by = $user->id;
            $invoice->cancel_reason_code = $data['reason_code'];
            $invoice->cancel_notes = $data['notes'] ?? null;
            $invoice->save();

            DB::commit();

            return $this->success($request, $invoice->fresh(), __('Invoice cancelled and stock restored.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->failure($request, __('Cancellation failed: :msg', ['msg' => $e->getMessage()]), 500);
        }
    }

    /**
     * Only pharmacists/admins can mutate (return/edit/cancel) a sale invoice.
     */
    protected function authorizeManage(MedicineSaleInvoice $invoice): void
    {
        $this->authorizeView($invoice);

        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && $user->role !== 'pharmacist') {
            abort(403, __('Only pharmacists and admins can modify sale invoices.'));
        }
    }

    /**
     * JSON (for fetch()-driven modals) or redirect-back (classic form
     * fallback) success response, carrying enough of the fresh invoice
     * state for the UI to patch badges/totals without a hard reload.
     */
    protected function success(Request $request, MedicineSaleInvoice $invoice, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice' => [
                    'id'          => $invoice->id,
                    'status'      => $invoice->status,
                    'subtotal'    => (float) $invoice->subtotal,
                    'discount'    => (float) $invoice->discount,
                    'tax'         => (float) $invoice->tax,
                    'total'       => (float) $invoice->total,
                    'paid_amount' => (float) $invoice->paid_amount,
                    'balance'     => max(0, round((float) $invoice->total - (float) $invoice->paid_amount, 2)),
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    protected function failure(Request $request, string $message, int $status = 422)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return back()->withInput()->with('error', $message);
    }

    /**
     * Download the invoice as a regular A4 PDF (DomPDF).
     */
    public function pdf(MedicineSaleInvoice $invoice)
    {
        $this->authorizeView($invoice);
        $invoice->load(['items.medicine:id,name,dosage,form', 'patient', 'user', 'clinic']);

        $clinic = $invoice->clinic ?: Clinic::find($invoice->clinic_id);
        $currency = $this->currencySymbol($this->resolveCurrency($invoice->clinic_id));

        $pdf = Pdf::loadView('medicines.sales.pdf', compact('invoice', 'clinic', 'currency'))
            ->setPaper('a4');

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Render the invoice as a thermal printer-friendly receipt (58mm / 80mm).
     * QR encodes a signed public URL so customers can scan and view the receipt.
     */
    public function thermal(Request $request, MedicineSaleInvoice $invoice, ThermalReceiptService $thermal)
    {
        $this->authorizeView($invoice);

        $widthMm = (int) $request->query('width', 0);
        if (!in_array($widthMm, ThermalReceiptService::ALLOWED_WIDTHS, true)) {
            $widthMm = ThermalReceiptService::DEFAULT_WIDTH;
        }

        $payload = $thermal->buildForMedicineSale($invoice, $widthMm);
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Authorize a clinic-scoped view of the invoice.
     *
     * Super admins see everything. Pharmacists are tenant-wide so they can
     * print receipts created by sister clinics they cover. Everyone else is
     * restricted to their own clinic_id.
     */
    protected function authorizeView(MedicineSaleInvoice $invoice): void
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return;
        }
        if ($user->role === 'pharmacist' && $user->clinic) {
            $tenantClinicIds = $user->clinic->getTenantClinicIds();
            if (in_array($invoice->clinic_id, $tenantClinicIds, true)) {
                return;
            }
        }
        if ($invoice->clinic_id !== $user->clinic_id) {
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
