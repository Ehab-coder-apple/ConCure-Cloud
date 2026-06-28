<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DentalTreatment;
use App\Models\MedicineSaleInvoice;
use App\Models\OrthodonticCase;
use App\Models\PatientVisit;
use App\Models\Receipt;
use App\Models\SimplePrescription;
use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Builds the normalized payload consumed by the thermal receipt blade view.
 *
 * Tenant-aware: every call expects entities that are already scoped to the
 * authenticated user's clinic_id by the controller.
 */
class ThermalReceiptService
{
    public const ALLOWED_WIDTHS = [58, 80];
    public const DEFAULT_WIDTH = 80;

    /**
     * Build payload for a clinical visit (no payment by default).
     */
    public function buildForVisit(PatientVisit $visit, ?int $widthMm = null): array
    {
        $visit->loadMissing(['patient', 'creator', 'clinic']);
        $clinic = $visit->clinic ?: Clinic::find($visit->clinic_id);
        $this->applyLocale($clinic);

        $title = __('Visit Receipt');
        $reference = sprintf('VISIT-%d', $visit->id);

        $meta = [
            ['label' => __('Visit #'), 'value' => $visit->id],
            ['label' => __('Date'), 'value' => optional($visit->visit_date)->format('Y-m-d H:i') ?: '-'],
            ['label' => __('Type'), 'value' => $this->humanize($visit->visit_type) ?: '-'],
            ['label' => __('Status'), 'value' => $this->humanize($visit->status) ?: '-'],
        ];

        $services = [];
        if ($visit->reason_for_visit) {
            $services[] = ['label' => __('Reason'), 'value' => $visit->reason_for_visit];
        }
        if ($visit->visit_type) {
            $services[] = ['label' => __('Service'), 'value' => $this->humanize($visit->visit_type)];
        }

        return $this->finalize([
            'title' => $title,
            'reference' => $reference,
            'clinic' => $clinic,
            'patient' => $visit->patient,
            'doctor_label' => __('Recorded By'),
            'doctor_name' => optional($visit->creator)->full_name_with_title ?: optional($visit->creator)->full_name,
            'meta' => $meta,
            'services' => $services,
            'financials' => null,
            'qr_payload' => $this->signedPublicUrl('public.receipt.visit', ['visit' => $visit->id], $reference, $this->effectiveLocale($clinic)),
            'width_mm' => $this->normalizeWidth($widthMm, $clinic),
        ], $clinic);
    }

    /**
     * Build payload for an appointment (with optional Receipt for financials).
     */
    public function buildForAppointment(Appointment $appointment, ?int $widthMm = null): array
    {
        $appointment->loadMissing(['patient', 'doctor', 'clinic']);
        $clinic = $appointment->clinic ?: Clinic::find($appointment->clinic_id);
        $this->applyLocale($clinic);

        $receipt = Receipt::where('clinic_id', $appointment->clinic_id)
            ->where('reference_number', (string) $appointment->id)
            ->first();

        $dt = $appointment->appointment_datetime
            ? Carbon::parse($appointment->appointment_datetime)
            : null;

        $title = $receipt ? __('Payment Receipt') : __('Appointment Receipt');
        $reference = $receipt ? $receipt->receipt_number : ('APPT-' . $appointment->appointment_number);

        $meta = [
            ['label' => __('Appointment #'), 'value' => $appointment->appointment_number],
            ['label' => __('Date'), 'value' => $dt ? $dt->format('Y-m-d H:i') : '-'],
            ['label' => __('Type'), 'value' => $this->humanize($appointment->type) ?: '-'],
            ['label' => __('Status'), 'value' => $this->humanize($appointment->status) ?: '-'],
        ];

        $services = [
            ['label' => __('Service'), 'value' => $this->humanize($appointment->type ?? 'consultation')],
        ];
        if ($appointment->reason) {
            $services[] = ['label' => __('Reason'), 'value' => $appointment->reason];
        }

        $financials = null;
        if ($receipt) {
            $currency = DB::table('settings')
                ->where('clinic_id', $appointment->clinic_id)
                ->where('key', 'currency')
                ->value('value') ?? 'USD';

            $financials = [
                'currency' => $currency,
                'currency_symbol' => $this->currencySymbol($currency),
                'total' => (float) $receipt->amount,
                'paid' => (float) $receipt->amount,
                'balance' => 0.0,
                'method' => $this->humanize($receipt->payment_method),
                'receipt_number' => $receipt->receipt_number,
                'notes' => $receipt->notes,
            ];
        }

        return $this->finalize([
            'title' => $title,
            'reference' => $reference,
            'clinic' => $clinic,
            'patient' => $appointment->patient,
            'doctor_label' => __('Doctor'),
            'doctor_name' => optional($appointment->doctor)->full_name_with_title
                ?: optional($appointment->doctor)->full_name,
            'meta' => $meta,
            'services' => $services,
            'financials' => $financials,
            'qr_payload' => $this->signedPublicUrl('public.receipt.appointment', ['appointment' => $appointment->id], $reference, $this->effectiveLocale($clinic)),
            'width_mm' => $this->normalizeWidth($widthMm, $clinic),
        ], $clinic);
    }

    /**
     * Build payload for a dental treatment plan.
     */
    public function buildForDentalTreatment(DentalTreatment $treatment, ?int $widthMm = null): array
    {
        $treatment->loadMissing(['patient', 'assignedDoctor', 'performedBy', 'clinic', 'invoice.items']);
        $clinic = $treatment->clinic ?: Clinic::find($treatment->clinic_id);
        $this->applyLocale($clinic);

        $title = __('Dental Treatment Receipt');
        $reference = $treatment->treatment_number;

        $teeth = is_array($treatment->tooth_numbers) && !empty($treatment->tooth_numbers)
            ? implode(', ', array_map(fn ($t) => '#' . $t, $treatment->tooth_numbers))
            : ($treatment->tooth_number ? '#' . $treatment->tooth_number : '-');

        $surfaces = is_array($treatment->surfaces_affected) && !empty($treatment->surfaces_affected)
            ? implode(', ', $treatment->surfaces_affected)
            : null;

        $meta = [
            ['label' => __('Treatment #'), 'value' => $treatment->treatment_number],
            ['label' => __('Date'), 'value' => optional($treatment->scheduled_date)->format('Y-m-d H:i') ?: '-'],
            ['label' => __('Status'), 'value' => $treatment->status_display ?: '-'],
            ['label' => __('Priority'), 'value' => $treatment->priority_display ?: '-'],
        ];
        if ($treatment->completed_date) {
            $meta[] = ['label' => __('Completed'), 'value' => $treatment->completed_date->format('Y-m-d')];
        }

        // Check if we have invoice items (itemized procedures) or use combined procedure name
        $services = [];
        if ($treatment->invoice && $treatment->invoice->items && $treatment->invoice->items->count() > 0) {
            // Show itemized procedures from invoice
            $services[] = ['label' => __('Procedures'), 'value' => ''];
            foreach ($treatment->invoice->items as $item) {
                $services[] = [
                    'label' => '',
                    'value' => $item->description,
                    'price' => $item->unit_price,
                ];
            }
        } else {
            // Fallback to combined procedure name
            $services[] = ['label' => __('Procedure'), 'value' => trim(($treatment->procedure_name ?? '') . ($treatment->procedure_code ? ' (' . $treatment->procedure_code . ')' : '')) ?: '-'];
        }

        $services[] = ['label' => __('Tooth'), 'value' => $teeth];

        if ($surfaces) {
            $services[] = ['label' => __('Surfaces'), 'value' => $surfaces];
        }
        if ($treatment->diagnosis) {
            $services[] = ['label' => __('Diagnosis'), 'value' => trim($treatment->diagnosis . ($treatment->icd10_code ? ' [' . $treatment->icd10_code . ']' : ''))];
        }
        if ($treatment->estimated_duration_minutes) {
            $services[] = ['label' => __('Duration'), 'value' => $treatment->estimated_duration_minutes . ' ' . __('min')];
        }

        $linkedReceipt = Receipt::where('clinic_id', $treatment->clinic_id)
            ->where('reference_number', (string) $treatment->treatment_number)
            ->latest('id')
            ->first();

        $currency = $treatment->currency
            ?: (DB::table('settings')
                ->where('clinic_id', $treatment->clinic_id)
                ->where('key', 'currency')
                ->value('value') ?? 'USD');

        $total = (float) ($treatment->actual_cost ?? $treatment->estimated_cost ?? 0);
        $paid = (float) ($treatment->paid_amount ?? 0);
        $balance = max(0.0, $total - $paid);

        $financials = [
            'currency' => $currency,
            'currency_symbol' => $this->currencySymbol($currency),
            'total' => $total,
            'paid' => $paid,
            'balance' => $balance,
            'method' => $linkedReceipt ? $this->humanize($linkedReceipt->payment_method) : ($treatment->payment_status_display ?: '-'),
            'receipt_number' => $linkedReceipt?->receipt_number,
            'notes' => $linkedReceipt?->notes,
        ];

        return $this->finalize([
            'title' => $title,
            'reference' => $reference,
            'clinic' => $clinic,
            'patient' => $treatment->patient,
            'doctor_label' => __('Doctor'),
            'doctor_name' => optional($treatment->assignedDoctor)->full_name_with_title
                ?: optional($treatment->assignedDoctor)->full_name
                ?: optional($treatment->performedBy)->full_name_with_title
                ?: optional($treatment->performedBy)->full_name,
            'meta' => $meta,
            'services' => $services,
            'financials' => $financials,
            'qr_payload' => $this->signedPublicUrl('public.receipt.dental-treatment', ['dentalTreatment' => $treatment->id], $reference, $this->effectiveLocale($clinic)),
            'width_mm' => $this->normalizeWidth($widthMm, $clinic),
        ], $clinic);
    }

    /**
     * Build payload for an orthodontic case financial summary.
     */
    public function buildForOrthodonticCase(OrthodonticCase $case, ?int $widthMm = null): array
    {
        $case->loadMissing(['patient', 'doctor', 'clinic', 'payments']);
        $clinic = $case->clinic ?: Clinic::find($case->clinic_id);
        $this->applyLocale($clinic);

        $latestPayment = $case->payments->sortByDesc('payment_date')->first();
        $currency = $case->currency ?: (DB::table('settings')
            ->where('clinic_id', $case->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD');

        $paymentHistory = $case->payments->sortBy('payment_date')->map(function ($payment) {
            return [
                'date' => optional($payment->payment_date)->format('Y-m-d'),
                'amount' => (float) $payment->amount,
            ];
        })->values()->all();

        $financials = [
            'currency' => $currency,
            'currency_symbol' => $this->currencySymbol($currency),
            'total' => (float) $case->total_cost,
            'paid' => (float) $case->paid_amount,
            'balance' => max(0.0, (float) $case->balance),
            'method' => $latestPayment ? $this->humanize($latestPayment->payment_method) : '-',
            'receipt_number' => $latestPayment?->receipt_number,
            'payment_plan' => OrthodonticCase::PAYMENT_PLANS[$case->payment_plan] ?? $this->humanize($case->payment_plan),
            'payment_history' => $paymentHistory,
            'notes' => $latestPayment?->notes,
        ];

        return $this->finalize([
            'title' => __('Orthodontic Financial Receipt'),
            'reference' => $case->case_number,
            'clinic' => $clinic,
            'patient' => $case->patient,
            'doctor_label' => __('Doctor'),
            'doctor_name' => optional($case->doctor)->full_name_with_title ?: optional($case->doctor)->full_name,
            'meta' => [
                ['label' => __('Case #'), 'value' => $case->case_number],
                ['label' => __('Start Date'), 'value' => optional($case->start_date)->format('Y-m-d') ?: '-'],
                ['label' => __('Treatment'), 'value' => $case->treatment_type_display ?: '-'],
                ['label' => __('Status'), 'value' => $case->status_display ?: '-'],
            ],
            'services' => [
                ['label' => __('Current Phase'), 'value' => $case->current_phase_display ?: '-'],
                ['label' => __('Duration'), 'value' => ($case->estimated_duration_months ?: 0) . ' ' . __('months')],
            ],
            'financials' => $financials,
            'qr_payload' => $this->signedPublicUrl('public.receipt.orthodontic-case', ['orthodonticCase' => $case->id], $case->case_number, $this->effectiveLocale($clinic)),
            'width_mm' => $this->normalizeWidth($widthMm, $clinic),
        ], $clinic);
    }

    /**
     * Build payload for a multi-item pharmacy sale invoice.
     */
    public function buildForMedicineSale(MedicineSaleInvoice $invoice, ?int $widthMm = null): array
    {
        $invoice->loadMissing(['items.medicine:id,name,dosage,form', 'patient', 'user', 'clinic']);
        $clinic = $invoice->clinic ?: Clinic::find($invoice->clinic_id);
        $this->applyLocale($clinic);

        $currency = DB::table('settings')
            ->where('clinic_id', $invoice->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

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
            'currency_symbol' => $this->currencySymbol($currency),
            'subtotal'        => (float) $invoice->subtotal,
            'discount'        => (float) $invoice->discount,
            'tax'             => (float) $invoice->tax,
            'total'           => (float) $invoice->total,
            'paid'            => (float) $invoice->paid_amount,
            'balance'         => max(0.0, (float) $invoice->total - (float) $invoice->paid_amount),
            'method'          => $this->humanize((string) $invoice->payment_method) ?: '-',
            'receipt_number'  => $invoice->invoice_number,
            'notes'           => $invoice->notes,
        ];

        return $this->finalize([
            'title'        => __('Pharmacy Receipt'),
            'reference'    => $invoice->invoice_number,
            'clinic'       => $clinic,
            'patient'      => $invoice->patient,
            'doctor_label' => __('Cashier'),
            'doctor_name'  => optional($invoice->user)->full_name_with_title
                ?: optional($invoice->user)->full_name
                ?: optional($invoice->user)->username,
            'meta'         => [
                ['label' => __('Invoice'), 'value' => $invoice->invoice_number],
                ['label' => __('Date'), 'value' => optional($invoice->sold_at)->format('Y-m-d H:i') ?: '-'],
            ],
            'services'     => [],
            'items'        => $items,
            'financials'   => $financials,
            'qr_payload'   => $this->signedPublicUrl('public.receipt.medicine-sale', ['invoice' => $invoice->id], $invoice->invoice_number, $this->effectiveLocale($clinic)),
            'width_mm'     => $this->normalizeWidth($widthMm, $clinic),
        ], $clinic);
    }

    /**
     * Build payload for a prescription (no financials; medicine list only).
     */
    public function buildForPrescription(SimplePrescription $prescription, ?int $widthMm = null): array
    {
        $prescription->loadMissing(['patient', 'doctor', 'medicines', 'clinic']);
        $clinic = $prescription->clinic ?: Clinic::find($prescription->clinic_id);
        $this->applyLocale($clinic);

        $items = $prescription->medicines->map(function ($med) {
            $bits = array_filter([
                $med->dosage ?: null,
                $med->frequency ?: null,
                $med->duration ?: null,
                $med->instructions ?: null,
            ], fn ($v) => is_string($v) && trim($v) !== '');
            return [
                'name'     => $med->medicine_name,
                'subtitle' => implode(' · ', $bits) ?: null,
            ];
        })->all();

        $meta = [
            ['label' => __('Prescription #'), 'value' => $prescription->prescription_number],
            ['label' => __('Date'), 'value' => optional($prescription->prescribed_date)->format('Y-m-d') ?: '-'],
            ['label' => __('Status'), 'value' => $this->humanize((string) $prescription->status) ?: '-'],
        ];
        if ($prescription->diagnosis) {
            $meta[] = ['label' => __('Diagnosis'), 'value' => $prescription->diagnosis];
        }

        return $this->finalize([
            'title'        => __('Prescription'),
            'reference'    => $prescription->prescription_number,
            'clinic'       => $clinic,
            'patient'      => $prescription->patient,
            'doctor_label' => __('Doctor'),
            'doctor_name'  => optional($prescription->doctor)->full_name_with_title
                ?: optional($prescription->doctor)->full_name,
            'meta'         => $meta,
            'services'     => [],
            'items'        => $items,
            'items_title'  => __('Medicines'),
            'financials'   => null,
            'qr_payload'   => $this->signedPublicUrl('public.receipt.prescription', ['prescription' => $prescription->id], $prescription->prescription_number, $this->effectiveLocale($clinic)),
            'width_mm'     => $this->normalizeWidth($widthMm, $clinic),
        ], $clinic);
    }

    /**
     * Apply the final shared fields and the QR SVG.
     */
    protected function finalize(array $payload, ?Clinic $clinic): array
    {
        $user = auth()->user();
        $payload['printed_by'] = $user instanceof User
            ? ($user->full_name_with_title ?: $user->full_name)
            : null;
        $payload['printed_at'] = Carbon::now();
        $locale = $this->effectiveLocale($clinic);
        $payload['locale'] = $locale;
        $payload['is_rtl'] = in_array($locale, ['ar', 'ku', 'fa', 'he'], true);
        $payload['thank_you'] = $this->resolveThankYou($clinic);
        $payload['qr_svg'] = $this->qrSvg($payload['qr_payload']);
        return $payload;
    }

    /**
     * Generate an inline SVG QR code (no GD dependency).
     */
    public function qrSvg(string $data, int $size = 140): string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle($size, 1),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            return $writer->writeString($data);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Pull the receipt thank-you message from the clinic settings if set.
     */
    protected function resolveThankYou(?Clinic $clinic): string
    {
        $settings = is_array($clinic?->settings) ? $clinic->settings : [];
        $custom = $settings['receipt_thank_you'] ?? null;
        return is_string($custom) && trim($custom) !== ''
            ? $custom
            : __('Thank you for your visit');
    }

    /**
     * Resolve the default paper width: query > clinic settings > default.
     */
    protected function normalizeWidth(?int $widthMm, ?Clinic $clinic): int
    {
        if (in_array($widthMm, self::ALLOWED_WIDTHS, true)) {
            return $widthMm;
        }
        $settings = is_array($clinic?->settings) ? $clinic->settings : [];
        $configured = (int) ($settings['receipt_paper_width_mm'] ?? 0);
        return in_array($configured, self::ALLOWED_WIDTHS, true)
            ? $configured
            : self::DEFAULT_WIDTH;
    }

    /**
     * Generate a permanent signed public URL for a receipt route.
     * Bakes the current locale into the signed query so the public page renders
     * in the language the receipt was issued in (sessionless context). The
     * locale is part of the signature, so it cannot be tampered with.
     * Returns the fallback string (e.g. the receipt reference) on failure.
     */
    protected function signedPublicUrl(string $routeName, array $params, string $fallback, ?string $locale = null): string
    {
        try {
            $locale = $locale ?: app()->getLocale();
            if (in_array($locale, ['en', 'ar', 'ku', 'fa', 'he'], true)) {
                $params['lang'] = $locale;
            }
            return URL::signedRoute($routeName, $params);
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    /**
     * Resolve the effective locale for a receipt. Resolution order:
     *   1. Per-tenant receipt-only override (`clinic.settings.receipt_language`)
     *      stored in the JSON `clinics.settings` column.
     *   2. The clinic's `default_language` in the key/value `settings` table
     *      (the value bound to the "Default Language" select in
     *      Settings → General). This is the field tenants toggle when they
     *      want their whole clinic — receipts included — in Arabic / Kurdish.
     *   3. The current session locale (`app()->getLocale()`).
     */
    protected function effectiveLocale(?Clinic $clinic): string
    {
        $supported = ['en', 'ar', 'ku', 'fa', 'he'];
        if ($clinic) {
            $pref = $clinic->getSetting('receipt_language');
            if (is_string($pref) && in_array($pref, $supported, true)) {
                return $pref;
            }

            $clinicDefault = DB::table('settings')
                ->where('clinic_id', $clinic->id)
                ->where('key', 'default_language')
                ->value('value');
            if (is_string($clinicDefault) && in_array($clinicDefault, $supported, true)) {
                return $clinicDefault;
            }
        }
        return app()->getLocale();
    }

    /**
     * Apply the clinic-resolved locale to the application before we evaluate
     * any __() strings. Without this, every __() call in buildFor*() and in
     * the thermal blade resolves against the staff member's current session
     * locale — causing receipts to render in English even when the clinic's
     * Default Language is Arabic.
     */
    protected function applyLocale(?Clinic $clinic): void
    {
        $locale = $this->effectiveLocale($clinic);
        if ($locale && $locale !== app()->getLocale()) {
            app()->setLocale($locale);
        }
    }

    protected function humanize(?string $value): string
    {
        if (!$value) {
            return '';
        }
        return ucwords(str_replace(['_', '-'], ' ', $value));
    }

    protected function currencySymbol(string $code): string
    {
        return [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'IQD' => 'IQD',
            'SAR' => 'SAR',
            'AED' => 'AED',
            'EGP' => 'EGP',
            'KWD' => 'KWD',
        ][strtoupper($code)] ?? $code;
    }
}

