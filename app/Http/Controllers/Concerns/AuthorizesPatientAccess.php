<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Patient;

trait AuthorizesPatientAccess
{
    protected function authorizePatientAccess(Patient $patient): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to patient.');
        }

        if ($user->isClinicAdmin()) {
            return;
        }

        if ($user->hasAnyPermission(['patients_view', 'patients_edit', 'patients_manage'])) {
            return;
        }

        $hasAccess = $patient->appointments()->where('doctor_id', $user->id)->exists()
            || $patient->prescriptions()->where('doctor_id', $user->id)->exists()
            || $patient->simplePrescriptions()->where('doctor_id', $user->id)->exists();

        if (!$hasAccess) {
            abort(403, 'You can only access patients you have appointments or prescriptions with.');
        }
    }
}