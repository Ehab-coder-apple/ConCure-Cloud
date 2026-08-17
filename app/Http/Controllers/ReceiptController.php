<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\AestheticInvoice;
use App\Models\Appointment;
use App\Models\DentalTreatment;
use App\Models\OrthodonticCase;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Services\ThermalReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Generates browser-printable thermal receipts (58mm / 80mm ESC/POS friendly)
 * for clinical visits and appointments. Tenant-scoped via clinic_id.
 *
 * Note: this is a complement to the existing DomPDF receipt
 * (AppointmentController::generateReceiptPDF) and is rendered as HTML so the
 * browser's native print dialog can target a thermal printer directly.
 */
class ReceiptController extends Controller
{
    use AuthorizesPatientAccess;

    public function __construct(protected ThermalReceiptService $service)
    {
    }

    /**
     * Print a thermal receipt for a clinical visit.
     */
    public function printVisit(Request $request, Patient $patient, PatientVisit $visit)
    {
        $this->authorizePatientAccess($patient);

        if ($visit->patient_id !== $patient->id) {
            abort(404);
        }

        $payload = $this->service->buildForVisit($visit, $this->resolveWidth($request));
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Print a thermal receipt for an appointment (with optional payment block).
     */
    public function printAppointment(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        $payload = $this->service->buildForAppointment($appointment, $this->resolveWidth($request));
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Print a thermal receipt for a dental treatment plan.
     */
    public function printDentalTreatment(Request $request, DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to treatment.');
            }
        }

        $payload = $this->service->buildForDentalTreatment($dentalTreatment, $this->resolveWidth($request));
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Print a thermal receipt for an orthodontic case financial summary.
     */
    public function printOrthodonticCase(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $orthodonticCase->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to orthodontic case.');
            }
        }

        $payload = $this->service->buildForOrthodonticCase($orthodonticCase, $this->resolveWidth($request));
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Print a thermal receipt for an aesthetic treatment invoice.
     */
    public function printAestheticInvoice(Request $request, AestheticInvoice $aestheticInvoice)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $aestheticInvoice->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        $payload = $this->service->buildForAestheticInvoice($aestheticInvoice, $this->resolveWidth($request));
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Read ?width=58|80 from the query string.
     */
    protected function resolveWidth(Request $request): ?int
    {
        $width = (int) $request->query('width', 0);
        return in_array($width, ThermalReceiptService::ALLOWED_WIDTHS, true) ? $width : null;
    }

    /**
     * Replicates AppointmentController's tenant + role-based visibility checks.
     */
    protected function authorizeAppointmentAccess(Appointment $appointment): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ($appointment->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to appointment.');
        }

        if ($user->isSuperAdmin() || $user->isClinicAdmin()) {
            return;
        }

        if ($user->role === 'doctor') {
            if ((int) $appointment->doctor_id !== (int) $user->id) {
                abort(403);
            }
            return;
        }

        if ($user->role === 'assistant') {
            $allowed = method_exists($user, 'allowedDoctorIds') ? $user->allowedDoctorIds() : [];
            $isAllowed = in_array((int) $appointment->doctor_id, array_map('intval', $allowed ?: []), true)
                || (int) $appointment->created_by === (int) $user->id;
            if (!$isAllowed) {
                abort(403);
            }
            return;
        }

        if ((int) $appointment->doctor_id !== (int) $user->id) {
            abort(403);
        }
    }
}
