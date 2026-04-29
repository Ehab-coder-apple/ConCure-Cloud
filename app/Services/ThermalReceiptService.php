<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DentalTreatment;
use App\Models\PatientVisit;
use App\Models\Receipt;
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
        $treatment->loadMissing(['patient', 'assignedDoctor', 'performedBy', 'clinic']);
        $clinic = $treatment->clinic ?: Clinic::find($treatment->clinic_id);

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

        $services = [
            ['label' => __('Procedure'), 'value' => trim(($treatment->procedure_name ?? '') . ($treatment->procedure_code ? ' (' . $treatment->procedure_code . ')' : '')) ?: '-'],
            ['label' => __('Tooth'), 'value' => $teeth],
        ];
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
     * Resolve the effective locale for a receipt: per-tenant override
     * (`clinic.settings.receipt_language`) wins, otherwise the staff's
     * current session locale, otherwise the configured default.
     */
    protected function effectiveLocale(?Clinic $clinic): string
    {
        $supported = ['en', 'ar', 'ku', 'fa', 'he'];
        if ($clinic) {
            $pref = $clinic->getSetting('receipt_language');
            if (is_string($pref) && in_array($pref, $supported, true)) {
                return $pref;
            }
        }
        return app()->getLocale();
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

