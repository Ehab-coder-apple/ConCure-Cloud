<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClinicReportController extends Controller
{
    /**
     * Display aggregated clinic activity/performance metrics.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'name');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Additional filters
        $city = trim((string) $request->query('city', ''));
        $clinicType = $request->query('clinic_type'); // tenant | demo | null
        $speciality = trim((string) $request->query('speciality', ''));

        // Use raw subqueries against the underlying tables so the report always
        // reflects the true system-wide data, completely independent of any
        // model-level global scopes or auth context.
        $query = Clinic::query()
            ->select('clinics.*')
            // Total patients per clinic
            ->selectSub(function ($sub) {
                $sub->from('patients')
                    ->whereColumn('patients.clinic_id', 'clinics.id')
                    ->selectRaw('COUNT(*)');
            }, 'total_patients')
            // Total prescriptions per clinic (all prescription engines):
            // - Standard prescriptions
            // - Simple prescriptions
            // - Pediatric prescriptions
            ->selectRaw("(
                    COALESCE((
                        SELECT COUNT(*)
                        FROM prescriptions
                        WHERE prescriptions.clinic_id = clinics.id
                    ), 0) +
                    COALESCE((
                        SELECT COUNT(*)
                        FROM simple_prescriptions
                        WHERE simple_prescriptions.clinic_id = clinics.id
                    ), 0) +
                    COALESCE((
                        SELECT COUNT(*)
                        FROM pediatric_prescriptions
                        WHERE pediatric_prescriptions.clinic_id = clinics.id
                    ), 0)
                ) AS total_prescriptions")
            // Total revenue per clinic across all clinical modules:
            // - Standard invoices (pediatric, ENT, dental, etc.)
            // - Aesthetic invoices
            ->selectRaw("(
                    COALESCE((
                        SELECT SUM(total_amount)
                        FROM invoices
                        WHERE invoices.clinic_id = clinics.id
                          AND invoices.status IN ('paid', 'partial_paid')
                    ), 0) +
                    COALESCE((
                        SELECT SUM(total_amount)
                        FROM aesthetic_invoices
                        WHERE aesthetic_invoices.clinic_id = clinics.id
                          AND aesthetic_invoices.status IN ('paid', 'partial')
                    ), 0)
                ) AS total_revenue")
            // Last login timestamp among clinic users
            ->selectSub(function ($sub) {
                $sub->from('users')
                    ->whereColumn('users.clinic_id', 'clinics.id')
                    ->selectRaw('MAX(last_login_at)');
            }, 'last_login_at')
            // Total images per clinic across all imaging sources:
            // - Aesthetic session images (by tenant_id)
            // - Medical Image Bank (patient_images)
            // - Dental images
            // - Orthodontic photos
            ->selectRaw("(
                    COALESCE((
                        SELECT COUNT(*)
                        FROM session_images
                        JOIN aesthetic_sessions ON session_images.session_id = aesthetic_sessions.id
                        WHERE aesthetic_sessions.deleted_at IS NULL
                          AND aesthetic_sessions.tenant_id = clinics.tenant_id
                    ), 0) +
                    COALESCE((
                        SELECT COUNT(*)
                        FROM patient_images
                        WHERE patient_images.clinic_id = clinics.id
                          AND (patient_images.mime IS NULL OR patient_images.mime LIKE 'image/%')
                    ), 0) +
                    COALESCE((
                        SELECT COUNT(*)
                        FROM dental_images
                        WHERE dental_images.clinic_id = clinics.id
                    ), 0) +
                    COALESCE((
                        SELECT COUNT(*)
                        FROM orthodontic_photos
                        WHERE orthodontic_photos.clinic_id = clinics.id
                    ), 0)
                ) AS total_images");

        // Name search
        if ($search !== '') {
            $query->where('clinics.name', 'like', '%' . $search . '%');
        }

        // Clinic type filter (Tenant vs Demo)
        if ($clinicType !== null && $clinicType !== '' && Schema::hasColumn('clinics', 'is_demo')) {
            if ($clinicType === 'tenant') {
                $query->where('clinics.is_demo', false);
            } elseif ($clinicType === 'demo') {
                $query->where('clinics.is_demo', true);
            }
        }

        // Speciality filter
        if ($speciality !== '' && Schema::hasColumn('clinics', 'speciality')) {
            $query->where('clinics.speciality', $speciality);
        }

        // City filter (with fallback to legacy address column)
        if ($city !== '') {
            $hasCity = Schema::hasColumn('clinics', 'city');

            $query->where(function ($q) use ($city, $hasCity) {
                if ($hasCity) {
                    $q->where('clinics.city', 'like', "%{$city}%")
                      ->orWhere('clinics.address', 'like', "%{$city}%");
                    return;
                }

                $q->where('clinics.address', 'like', "%{$city}%");
            });
        }

        $sortable = [
            'name' => 'clinics.name',
            'patients' => 'total_patients',
            'images' => 'total_images',
            'prescriptions' => 'total_prescriptions',
            'revenue' => 'total_revenue',
            'last_login' => 'last_login_at',
        ];

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'name';
        }

        $query->orderBy($sortable[$sort], $direction)
              ->orderBy('clinics.name');

        $clinics = $query->paginate(25)->withQueryString();

        return view('master.reports.clinics', [
            'clinics' => $clinics,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'city' => $city,
            'clinicType' => $clinicType,
            'specialityFilter' => $speciality,
            'specialities' => $this->specialityOptions(),
        ]);
    }

    /**
     * Common clinic specialties for Master forms/filters.
     * Duplicated from Master\\ClinicController to keep filters consistent.
     */
    protected function specialityOptions(): array
    {
        return [
            'General Medicine',
            'Dental',
            'Pediatrics',
            'Dermatology',
            'Cardiology',
            'Orthopedics',
            'ENT',
            'Ophthalmology',
            'Gynecology',
            'Psychiatry',
            'Radiology',
            'Laboratory',
            'Pharmacy',
            'Other',
        ];
    }
}
