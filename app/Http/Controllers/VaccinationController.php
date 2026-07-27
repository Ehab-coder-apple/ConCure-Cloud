<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Patient;
use App\Models\PatientVaccination;
use App\Models\Vaccine;
use App\Models\VaccinationSchedule;
use App\Models\ScheduleItem;
use App\Models\VaccineTranslation;
use App\Services\VaccinationService;
use App\Http\Traits\SmartSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VaccinationController extends Controller
{
    use SmartSearch;

    protected VaccinationService $service;

    public function __construct(VaccinationService $service)
    {
        $this->service = $service;
    }

    /**
     * Dashboard / index: list patients with vaccination data.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $minDate = now()->subYears(20)->startOfDay();
        $query = Patient::whereNotNull('vaccination_schedule_id')
            ->whereNotNull('date_of_birth')
            ->where('date_of_birth', '>=', $minDate)
            ->where('date_of_birth', '!=', '0000-00-00');

        if ($user->clinic_id) {
            $query->where('clinic_id', $user->clinic_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('vaccinations', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $patients = $query->with(['vaccinations.vaccine', 'vaccinationSchedule'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Available schedules for enrollment modal
        $schedules = VaccinationSchedule::where('is_active', true)
            ->with('country')
            ->orderBy('name')
            ->get();

        return view('vaccination.index', compact('patients', 'schedules'));
    }

    /**
     * Show patient vaccination timeline/card.
     */
    public function show(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $patient->load(['vaccinations.vaccine.translations', 'vaccinationSchedule.country', 'clinic']);

        // Update statuses in real-time
        $this->service->updateStatuses($patient);
        $patient->refresh();

        $stats = $this->service->getCompletionStats($patient);
        $language = app()->getLocale();

        return view('vaccination.show', compact('patient', 'stats', 'language'));
    }

    /**
     * Generate vaccination schedule for a patient.
     */
    public function generate(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $created = $this->service->generateScheduleForPatient($patient);

        if ($created->isEmpty()) {
            return back()->with('warning', __('No vaccination schedule available for this patient\'s clinic/country, or all doses already exist.'));
        }

        return back()->with('success', __(':count vaccination records generated.', ['count' => $created->count()]));
    }

    /**
     * Record a given vaccination.
     */
    public function record(Request $request, PatientVaccination $vaccination)
    {
        $request->validate([
            'given_date' => 'required|date|before_or_equal:today',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'administered_by' => 'nullable|string|max:255',
        ]);

        // Validate min age
        $patient = $vaccination->patient;
        $this->authorizePatientAccess($patient);

        $this->service->recordVaccination($vaccination, $request->all());

        return back()->with('success', __('Vaccination recorded successfully.'));
    }

    /**
     * Skip a vaccination.
     */
    public function skip(Request $request, PatientVaccination $vaccination)
    {
        $patient = $vaccination->patient;
        $this->authorizePatientAccess($patient);

        $vaccination->update([
            'status' => PatientVaccination::STATUS_SKIPPED,
            'notes' => $request->input('notes', $vaccination->notes),
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', __('Vaccination marked as skipped.'));
    }

    /**
     * Print vaccination card as PDF.
     */
    public function printCard(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $patient->load(['vaccinations.vaccine.translations', 'vaccinationSchedule.country', 'clinic']);
        $this->service->updateStatuses($patient);
        $patient->refresh();

        $stats = $this->service->getCompletionStats($patient);
        $language = app()->getLocale();
        $isRtl = in_array($language, ['ar', 'ku']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('vaccination.pdf-card', compact('patient', 'stats', 'language', 'isRtl'));
        $pdf->setPaper($request->input('size', 'a4'), 'portrait');

        return $pdf->download("vaccination-card-{$patient->patient_id}.pdf");
    }

    // ──────────────────────────────────────────────
    //  ADMIN: Schedule Management
    // ──────────────────────────────────────────────

    /**
     * Admin panel: list countries and schedules.
     */
    public function adminIndex()
    {
        $user = auth()->user();
        $query = Country::with(['vaccinationSchedules' => function ($q) {
            $q->withCount('items');
        }])->orderBy('name');

        // Non-master-admin tenants: only show their clinic's country
        if ($user->role !== 'master_admin' && $user->clinic_id) {
            $clinicCountryId = $user->clinic?->country_id;
            if ($clinicCountryId) {
                $query->where('id', $clinicCountryId);
            }
        }

        $countries = $query->get();

        return view('vaccination.admin.index', compact('countries'));
    }

    /**
     * Admin: show schedule details with items.
     */
    public function adminShowSchedule(VaccinationSchedule $schedule)
    {
        $schedule->load(['items.vaccine.translations', 'country']);
        return view('vaccination.admin.schedule-show', compact('schedule'));
    }

    /**
     * Admin: create/edit country.
     */
    public function adminStoreCountry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:5|unique:countries,iso_code,' . $request->id,
            'default_language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
        ]);

        Country::updateOrCreate(
            ['id' => $request->id],
            $request->only(['name', 'iso_code', 'default_language', 'timezone'])
        );

        return back()->with('success', __('Country saved successfully.'));
    }

    /**
     * Admin: delete country (only if no schedules or clinics attached).
     */
    public function adminDestroyCountry(Country $country)
    {
        if ($country->vaccinationSchedules()->exists()) {
            return back()->with('error', __('Cannot delete country ":name" because it has vaccination schedules. Delete the schedules first.', ['name' => $country->name]));
        }

        if ($country->clinics()->exists()) {
            return back()->with('error', __('Cannot delete country ":name" because clinics are assigned to it.', ['name' => $country->name]));
        }

        $name = $country->name;
        $country->delete();

        return back()->with('success', __('Country ":name" deleted successfully.', ['name' => $name]));
    }

    /**
     * Admin: create/edit schedule.
     */
    public function adminStoreSchedule(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:20',
            'is_default' => 'boolean',
            'effective_from' => 'nullable|date',
        ]);

        $data = $request->only(['country_id', 'name', 'version', 'effective_from']);
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = true;

        // If marking as default, unset others for same country
        if ($data['is_default']) {
            VaccinationSchedule::where('country_id', $request->country_id)
                ->where('id', '!=', $request->schedule_id)
                ->update(['is_default' => false]);
        }

        VaccinationSchedule::updateOrCreate(
            ['id' => $request->schedule_id],
            $data
        );

        return back()->with('success', __('Schedule saved successfully.'));
    }

    /**
     * Admin: add/edit schedule item.
     */
    public function adminStoreItem(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:vaccination_schedules,id',
            'vaccine_id' => 'required|exists:vaccines,id',
            'dose_number' => 'required|integer|min:1',
            'recommended_age_value' => 'required|integer|min:0',
            'recommended_age_unit' => 'required|in:days,weeks,months,years',
            'min_age_value' => 'nullable|integer|min:0',
            'max_age_value' => 'nullable|integer|min:0',
            'grace_period_days' => 'nullable|integer|min:0',
            'is_mandatory' => 'boolean',
        ]);

        ScheduleItem::updateOrCreate(
            ['id' => $request->item_id],
            $request->only([
                'schedule_id', 'vaccine_id', 'dose_number',
                'recommended_age_value', 'recommended_age_unit',
                'min_age_value', 'max_age_value', 'grace_period_days',
            ]) + ['is_mandatory' => $request->boolean('is_mandatory')]
        );

        return back()->with('success', __('Schedule item saved.'));
    }

    /**
     * Admin: import schedule from JSON.
     */
    public function adminImportJson(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:2048',
        ]);

        try {
            $data = json_decode(file_get_contents($request->file('json_file')->getRealPath()), true);
            if (!$data) {
                return back()->with('error', __('Invalid JSON file.'));
            }

            $schedule = $this->service->importScheduleFromJson($data);

            return back()->with('success', __('Schedule ":name" imported with :count items.', [
                'name' => $schedule->name,
                'count' => $schedule->items->count(),
            ]));
        } catch (\Exception $e) {
            return back()->with('error', __('Import failed: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Admin: manage vaccines.
     */
    public function adminVaccines()
    {
        $vaccines = Vaccine::with('translations')->orderBy('code')->get();
        return view('vaccination.admin.vaccines', compact('vaccines'));
    }

    /**
     * Admin: store/update vaccine.
     */
    public function adminStoreVaccine(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:vaccines,code,' . $request->vaccine_id,
            'global_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $vaccine = Vaccine::updateOrCreate(
            ['id' => $request->vaccine_id],
            $request->only(['code', 'global_name', 'description'])
        );

        // Save translations
        if ($request->filled('translations')) {
            foreach ($request->translations as $lang => $trans) {
                if (!empty($trans['name'])) {
                    VaccineTranslation::updateOrCreate(
                        ['vaccine_id' => $vaccine->id, 'language_code' => $lang],
                        ['name' => $trans['name'], 'description' => $trans['description'] ?? null]
                    );
                }
            }
        }

        return back()->with('success', __('Vaccine saved successfully.'));
    }

    /**
     * API: Get patient vaccination data as JSON.
     */
    public function apiPatientVaccinations(Patient $patient)
    {
        $patient->load(['vaccinations.vaccine.translations', 'vaccinationSchedule']);
        $stats = $this->service->getCompletionStats($patient);

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->full_name,
                'dob' => $patient->date_of_birth?->format('Y-m-d'),
            ],
            'schedule' => $patient->vaccinationSchedule?->name,
            'stats' => $stats,
            'vaccinations' => $patient->vaccinations->map(function ($v) {
                return [
                    'id' => $v->id,
                    'vaccine' => $v->vaccine->global_name,
                    'vaccine_code' => $v->vaccine->code,
                    'dose_number' => $v->dose_number,
                    'scheduled_date' => $v->scheduled_date->format('Y-m-d'),
                    'given_date' => $v->given_date?->format('Y-m-d'),
                    'status' => $v->status,
                    'delay_days' => $v->delay_days,
                ];
            }),
        ]);
    }

    /**
     * Enroll a patient into a vaccination schedule.
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'schedule_id' => 'nullable|exists:vaccination_schedules,id',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $this->authorizePatientAccess($patient);

        if (!$patient->date_of_birth) {
            return back()->with('error', __('Patient ":name" has no date of birth. Please update the patient record first.', ['name' => $patient->full_name]));
        }

        if ($patient->vaccination_schedule_id) {
            return back()->with('warning', __('Patient ":name" is already enrolled in a vaccination schedule.', ['name' => $patient->full_name]));
        }

        // If a specific schedule was chosen, assign it
        if ($request->filled('schedule_id')) {
            $patient->update(['vaccination_schedule_id' => $request->schedule_id]);
        }

        $created = $this->service->generateScheduleForPatient($patient);

        if ($created->isEmpty()) {
            return back()->with('error', __('No vaccination schedule could be resolved for this patient. Make sure the clinic has a country/schedule assigned.'));
        }

        return redirect()->route('vaccination.show', $patient)
            ->with('success', __('Patient ":name" enrolled with :count vaccination records.', [
                'name' => $patient->full_name,
                'count' => $created->count(),
            ]));
    }

    /**
     * API: Search patients not yet enrolled in vaccination.
     */
    public function searchUnenrolled(Request $request)
    {
        $user = auth()->user();
        $searchTerm = $this->getValidatedSearchTerm($request);

        $minDate = now()->subYears(20)->startOfDay();
        $query = Patient::whereNull('vaccination_schedule_id')
            ->whereNotNull('date_of_birth')
            ->where('date_of_birth', '>=', $minDate)
            ->where('date_of_birth', '!=', '0000-00-00');

        if ($user->clinic_id) {
            $query->where('clinic_id', $user->clinic_id);
        }

        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('patient_id', 'like', "%{$searchTerm}%");
            });
        }

        $patients = $query->select('id', 'patient_id', 'first_name', 'last_name', 'date_of_birth', 'gender')
            ->orderBy('first_name')
            ->limit(20)
            ->get();

        return response()->json(
            $this->getSearchResponse($patients, $searchTerm, 'Start typing to search unenrolled patients...')
        );
    }

    /**
     * Authorize access to patient data (same pattern as PatientController).
     */
    protected function authorizePatientAccess(Patient $patient): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) return;
        if ($user->clinic_id && $user->clinic_id !== $patient->clinic_id) {
            abort(403, 'Unauthorized access to this patient.');
        }
    }
}

