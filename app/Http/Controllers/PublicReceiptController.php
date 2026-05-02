<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentalTreatment;
use App\Models\MedicineSaleInvoice;
use App\Models\PatientVisit;
use App\Services\ThermalReceiptService;
use Illuminate\Http\Request;

/**
 * Public, signature-validated read-only receipt views.
 *
 * The QR codes printed on thermal receipts encode signed URLs that resolve
 * here. The `signed` middleware on the route group rejects any tampered or
 * unsigned request with a 403 before reaching these methods, so we do not
 * need to authenticate the patient.
 *
 * Each method renders the same data shown on the printed receipt — no
 * additional clinical or admin information is exposed.
 */
class PublicReceiptController extends Controller
{
    public function __construct(protected ThermalReceiptService $service)
    {
    }

    public function showVisit(Request $request, PatientVisit $visit)
    {
        $this->applyLocale($request);
        $payload = $this->service->buildForVisit($visit);
        return view('receipts.public', $this->finalize($payload));
    }

    public function showAppointment(Request $request, Appointment $appointment)
    {
        $this->applyLocale($request);
        $payload = $this->service->buildForAppointment($appointment);
        return view('receipts.public', $this->finalize($payload));
    }

    public function showDentalTreatment(Request $request, DentalTreatment $dentalTreatment)
    {
        $this->applyLocale($request);
        $payload = $this->service->buildForDentalTreatment($dentalTreatment);
        return view('receipts.public', $this->finalize($payload));
    }

    public function showMedicineSale(Request $request, MedicineSaleInvoice $invoice)
    {
        $this->applyLocale($request);
        $payload = $this->service->buildForMedicineSale($invoice);
        return view('receipts.public', $this->finalize($payload));
    }

    /**
     * Honor the `lang` query parameter that was baked into the signed URL at
     * print time. Falls back to the configured default if missing/invalid.
     */
    protected function applyLocale(Request $request): void
    {
        $supported = ['en', 'ar', 'ku', 'fa', 'he'];
        $lang = (string) $request->query('lang', '');
        if (in_array($lang, $supported, true)) {
            app()->setLocale($lang);
        }
    }

    /**
     * Strip the QR + interactive bits the public page does not need.
     */
    protected function finalize(array $payload): array
    {
        $payload['qr_svg'] = '';
        $payload['qr_payload'] = '';
        $payload['auto_print'] = false;
        return $payload;
    }
}
