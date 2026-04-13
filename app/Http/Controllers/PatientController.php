<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\PatientDental;
use App\Models\PatientEnt;
use App\Models\PatientFile;
use App\Models\PatientNutrition;
use App\Models\PatientPediatric;
use App\Models\PatientImage;
use App\Models\PatientMedicalOverview;
use App\Models\Prescription;
use App\Models\SimplePrescription;
use App\Imports\PatientsImport;
use App\Exports\PatientsExport;
use App\Models\Clinic;
use App\Http\Traits\SmartSearch;

use App\Services\PatientProfileModuleRegistry;
use App\Services\StorageQuotaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PatientController extends Controller
{
    use SmartSearch;
    use AuthorizesPatientAccess;
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Check if user has a clinic assigned (allow super admin without clinic)
        if (!$user->clinic_id && !$user->isSuperAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'You must be assigned to a clinic to view patients. Please contact your administrator.');
        }

        $query = Patient::with(['clinic', 'creator', 'checkups' => function ($q) {
            $q->latest('checkup_date')->limit(1);
        }]);

        // Restrict to clinic for clinic users; super admin sees all clinics
        if ($user->clinic_id) {
            $query->byClinic($user->clinic_id);
        }

        // Filter patients based on user role
        // Only Super Admins and Clinic Admins can see all patients
        // Assistants see patients of their assigned doctors
        // Regular doctors can only see patients they have appointments or prescriptions with
        // Dentists see only patients they created or have dental records for
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            if ($user->role === 'assistant') {
	            // If assistants have patient access permissions, they should be able to view
	            // all clinic patients (matches how the UI/permissions are expected to work).
	            // If they don't, fallback to showing patients linked to their assigned doctors
	            // (or patients they created).
	            if (!$user->hasAnyPermission(['patients_view', 'patients_create', 'patients_edit', 'patients_delete', 'patients_files', 'patients_history'])) {
	                $doctorIds = $user->allowedDoctorIds();
	                if (!empty($doctorIds)) {
	                    $query->where(function($q) use ($doctorIds, $user) {
	                        $q->whereHas('appointments', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('prescriptions', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('simplePrescriptions', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('labRequests', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('dietPlans', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhere('created_by', $user->id);
	                    });
	                } else {
	                    // No assigned doctors: still show patients created by the assistant
	                    $query->where('created_by', $user->id);
	                }
	            }
            } elseif ($user->role === 'dental_dept') {
                // Dentists see only patients they created or have dental records for
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('dentalCharts', function($subQ) use ($user) {
                        $subQ->where('created_by', $user->id);
                    })
                    ->orWhereHas('dentalTreatments', function($subQ) use ($user) {
                        $subQ->where('assigned_doctor_id', $user->id);
                    });
                });
            } else {
                // Regular doctors see only their own patients
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('appointments', function($appointmentQuery) use ($user) {
                        $appointmentQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('prescriptions', function($prescriptionQuery) use ($user) {
                        $prescriptionQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('simplePrescriptions', function($simplePrescriptionQuery) use ($user) {
                        $simplePrescriptionQuery->where('doctor_id', $user->id);
                    });
                });
            }
        }

        // Apply smart search filter
        $searchTerm = $this->getValidatedSearchTerm($request);
        if ($searchTerm !== null) {
            $query->search($searchTerm);
        }

        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('age_range')) {
            $ageRange = explode('-', $request->age_range);
            if (count($ageRange) === 2) {
                $query->byAgeRange((int) $ageRange[0], (int) $ageRange[1]);
            }
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15);

        $availableClinics = auth()->user()?->isSuperAdmin()
            ? Clinic::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('patients.index', array_merge($this->patientFormViewData(), [
            'patients' => $patients,
            'availableClinics' => $availableClinics,
        ]));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        $availableClinics = auth()->user()?->isSuperAdmin()
            ? Clinic::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('patients.create', array_merge($this->patientFormViewData(), [
            'availableClinics' => $availableClinics,
        ]));
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user has a clinic assigned (allow super admin without clinic)
        if (!$user->clinic_id && !$user->isSuperAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be assigned to a clinic to create patients. Please contact your administrator.'
                ], 403);
            }

            return redirect()->route('patients.index')
                           ->with('error', 'You must be assigned to a clinic to create patients. Please contact your administrator.');
        }

        // Determine clinic for creation (handles super admin without assigned clinic)
        $clinicId = $user->clinic_id;
        if (!$clinicId && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $clinicId = $request->input('clinic_id');
            if (!$clinicId) {
                // If there is exactly one clinic, default to it; otherwise ask user to pick
                $clinicCount = Clinic::count();
                if ($clinicCount === 1) {
                    $clinicId = Clinic::value('id');
                } else {
                    $message = 'Please select a clinic to create patients.';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'errors' => ['clinic_id' => [$message]],
                        ], 422);
                    }
                    return redirect()->route('patients.index')->with('error', $message);
                }
            }
        }

        // Adjust validation rules for quick add (AJAX requests)
        $isQuickAdd = $request->wantsJson() || $request->ajax();

        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'date_of_birth' => $isQuickAdd ? 'nullable|date|before:today' : 'required|date|before:today',
            'gender' => $isQuickAdd ? 'nullable|in:male,female,other' : 'required|in:male,female,other',
            'whatsapp_phone' => 'nullable|string|max:20',
            'job' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:1|max:500',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-,NA',
            'birth_weight' => 'nullable|integer|min:200|max:7000',
            'gestational_age_weeks' => 'nullable|integer|min:20|max:45',
            'allergies' => 'nullable|string',
            'is_pregnant' => 'boolean',
            'chronic_illnesses' => 'nullable|string',
            'surgeries_history' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'current_medications_summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'clinic_id' => 'nullable|exists:clinics,id',
            'selected_modules' => 'nullable|array',
            'selected_modules.*' => ['string', Rule::in(collect(PatientProfileModuleRegistry::all())->pluck('key')->all())],
            'medical_flags' => 'nullable|array',
            'medical_flags.pregnant' => 'nullable|boolean',
            'medical_flags.diabetic' => 'nullable|boolean',
            'medical_flags.hypertensive' => 'nullable|boolean',
            'dental_oral_hygiene' => ['nullable', 'string', Rule::in(array_keys(PatientDental::ORAL_HYGIENE_STATUSES))],
            'dental_smoking_status' => ['nullable', 'string', Rule::in(array_keys(PatientDental::SMOKING_STATUSES))],
            'pediatric_birth_weight' => 'nullable|integer|min:200|max:7000',
            'pediatric_gestational_age_weeks' => 'nullable|integer|min:20|max:45',
            'nutrition_height' => 'nullable|numeric|min:50|max:300',
            'nutrition_weight' => 'nullable|numeric|min:1|max:500',
            'ent_notes' => 'nullable|string',
            'medical_files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isQuickAdd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $nutritionHeight = $request->input('nutrition_height', $request->input('height'));
        $nutritionWeight = $request->input('nutrition_weight', $request->input('weight'));
        $pediatricBirthWeight = $request->input('pediatric_birth_weight', $request->input('birth_weight'));
        $pediatricGestationalAgeWeeks = $request->input('pediatric_gestational_age_weeks', $request->input('gestational_age_weeks'));
        $pregnantFlag = $request->boolean('medical_flags.pregnant') || $request->boolean('is_pregnant');

        $patient = Patient::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'whatsapp_phone' => $request->whatsapp_phone,
            'email' => $request->email,
            'address' => $request->address,
            'job' => $request->job,
            'education' => $request->education,
            'height' => $nutritionHeight,
            'weight' => $nutritionWeight,
            'is_pregnant' => $pregnantFlag,
            'blood_type' => $request->blood_type,
            'birth_weight' => $pediatricBirthWeight,
            'gestational_age_weeks' => $pediatricGestationalAgeWeeks,
            'notes' => $request->notes,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'clinic_id' => $clinicId,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        $this->syncMedicalOverview($patient, $request);
        $selectedModules = $this->eligibleSelectedModulesForPatient(
            $patient,
            $this->requestedModuleKeys($request)
        );
        $this->syncSelectedModules($patient, $selectedModules);
        $this->syncModuleProfiles($patient, $request, $selectedModules);

        // Handle medical history file uploads
        if ($request->hasFile('medical_files')) {
            $tenantDir = StorageQuotaService::getTenantStoragePath($clinicId, 'documents');
            foreach ($request->file('medical_files') as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($tenantDir, $filename, StorageQuotaService::SPACES_DISK);

                $fileSize = $file->getSize();
                PatientFile::create([
                    'patient_id' => $patient->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $fileSize,
                    'mime_type' => $file->getMimeType(),
                    'category' => 'medical_report',
                    'description' => 'Uploaded during patient registration',
                    'uploaded_by' => auth()->id(),
                ]);

                // Increment storage usage
                app(StorageQuotaService::class)->incrementUsage($clinicId, $fileSize);
            }
        }

        // Return JSON response for AJAX requests
        if ($isQuickAdd) {
            return response()->json([
                'success' => true,
                'message' => 'Patient created successfully',
                'patient' => [
                    'id' => $patient->id,
                    'patient_id' => $patient->patient_id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'full_name' => $patient->first_name . ' ' . $patient->last_name
                ]
            ]);
        }

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified patient.
     */
    public function show(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $patient->load([
            'clinic',
            'creator',
            'medicalOverview',
            'dentalProfile',
            'entProfile',
            'pediatricProfile',
            'nutritionProfile',
            'activeModules',
            'files' => function ($q) {
                $q->with('uploader')->latest()->limit(10);
            },
        'images' => function ($q) {
            $q->latest()->limit(12);
        },
        'videos' => function ($q) {
            $q->latest()->limit(24);
        },
            'prescriptions' => function ($q) {
                $q->with('doctor')->latest()->limit(5);
            },
            'simplePrescriptions' => function ($q) {
                $q->with('doctor')->latest()->limit(5);
            },
            'appointments' => function ($q) {
                $q->with('doctor')->latest('appointment_datetime')->limit(5);
            },
            'medications' => function ($q) {
                $q->latest('started_on')->limit(8);
            },
            'visits' => function ($q) {
                $q->with(['hpi', 'creator'])->latest('visit_date')->limit(6);
            },
        ]);

        $patient->loadCount([
            'dentalCharts',
            'dentalTreatments',
            'dentalImages',
            'growthMeasurements',
            'pediatricPrescriptions',
            'vaccinations',
            'dietPlans',
            'nutritionProgressMeasurements',
            'nutritionGoals',
        ]);

        $visitTimelineSearch = trim((string) $request->input('visit_search', ''));
        $visitTimeline = $this->buildVisitTimelinePaginator($patient, $visitTimelineSearch, 1);

        $medicalOverview = $patient->medicalOverview ?: new PatientMedicalOverview([
            'allergies' => $patient->getRawOriginal('allergies'),
            'chronic_diseases' => $patient->getRawOriginal('chronic_illnesses'),
            'surgeries' => $patient->getRawOriginal('surgeries_history'),
            'medical_history' => $patient->getRawOriginal('medical_history'),
            'flags' => array_filter([
                'pregnant' => (bool) $patient->getRawOriginal('is_pregnant'),
            ]),
        ]);

        $activeProfileModules = PatientProfileModuleRegistry::filterVisibleToUser($patient->active_profile_modules, auth()->user());
        $availableProfileModules = PatientProfileModuleRegistry::filterVisibleToUser($patient->available_profile_modules, auth()->user());
        $currentMedications = $patient->medications->where('status', 'current')->values();
        $pastMedications = $patient->medications->where('status', 'past')->values();
        $recentVisits = $patient->visits;
        $legacyProfileHpi = $patient->getRawOriginal('history_of_present_illness');
        $latestDentalChart = $patient->dentalCharts()->latest('created_at')->first();
        $dentalLastVisitLabel = optional($latestDentalChart?->created_at)->format('M d, Y') ?: __('Not recorded');
        $patient->setAttribute('dental_last_visit_label', $dentalLastVisitLabel);
        $dentalProfile = $patient->dentalProfile ?: new PatientDental([
            'smoking_status' => 'unknown',
            'bruxism' => false,
        ]);
        $entProfile = $patient->entProfile ?: new PatientEnt([
            'dizziness' => false,
        ]);
        $patient->setAttribute('ent_issue_count', $entProfile->issue_count);
        $patient->setAttribute('ent_dizziness_label', $entProfile->dizziness ? __('Yes') : __('No'));
        $patient->setAttribute('ent_file_count', $patient->files()->entRelated()->count());
        $pediatricProfile = $patient->pediatricProfile ?: new PatientPediatric([
            'birth_weight' => $patient->getRawOriginal('birth_weight'),
            'gestational_age' => $patient->getRawOriginal('gestational_age_weeks'),
            'vaccination_status' => 'unknown',
            'feeding_type' => 'unknown',
        ]);
        $patient->setAttribute('pediatric_growth_status_label', __($pediatricProfile->growth_status_label));
        $patient->setAttribute('pediatric_vaccination_status_label', $pediatricProfile->vaccination_status_label ? __($pediatricProfile->vaccination_status_label) : __('Not recorded'));
        $latestNutritionMeasurement = $patient->nutritionProgressMeasurements->sortByDesc('measurement_date')->first();
        $activeNutritionGoal = $patient->nutritionGoals->where('is_active', true)->sortByDesc('created_at')->first();
        $latestNutritionPlan = $patient->dietPlans->sortByDesc('created_at')->first();
        $nutritionProfile = $patient->nutritionProfile ?: new PatientNutrition([
            'height' => $latestNutritionMeasurement?->height_cm ?? $patient->height,
            'weight' => $latestNutritionMeasurement?->weight_kg ?? $patient->weight,
            'bmi' => $latestNutritionMeasurement?->bmi ?? $patient->bmi,
        ]);
        $nutritionLastVisitLabel = optional($recentVisits->first()?->visit_date)->format('M d, Y') ?: __('Not recorded');
        $patient->setAttribute('nutrition_last_visit_label', $nutritionLastVisitLabel);

        return view('patients.show', compact(
            'patient',
            'visitTimeline',
            'visitTimelineSearch',
            'medicalOverview',
            'activeProfileModules',
            'availableProfileModules',
            'currentMedications',
            'pastMedications',
            'recentVisits',
            'legacyProfileHpi',
            'dentalProfile',
            'dentalLastVisitLabel',
            'entProfile',
            'pediatricProfile',
            'nutritionProfile',
            'latestNutritionMeasurement',
            'activeNutritionGoal',
            'latestNutritionPlan'
        ));
    }

    /**
     * Return paginated visit timeline markup for AJAX lazy loading/search.
     */
    public function visitTimeline(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $visitTimeline = $this->buildVisitTimelinePaginator($patient, $search, $page);

        return response()->json([
            'html' => view('patients.partials.visit-timeline-items', compact('patient', 'visitTimeline'))->render(),
            'next_page_url' => $visitTimeline->nextPageUrl(),
            'has_more_pages' => $visitTimeline->hasMorePages(),
            'current_page' => $visitTimeline->currentPage(),
            'total' => $visitTimeline->total(),
        ]);
    }

    /**
     * Build a paginated visit timeline for a patient.
     */
    private function buildVisitTimelinePaginator(Patient $patient, string $search = '', int $page = 1, int $perPage = 8): LengthAwarePaginator
    {
        $query = $patient->checkups()
            ->with('recorder')
            ->orderByDesc('checkup_date')
            ->orderByDesc('id');

        if ($search !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';

            $query->where(function ($builder) use ($like) {
                $builder->where('symptoms', 'like', $like)
                    ->orWhere('notes', 'like', $like)
                    ->orWhere('recommendations', 'like', $like)
                    ->orWhere('custom_fields', 'like', $like);
            });
        }

        $visitTimeline = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'search' => $search,
        ]);

        $this->decorateVisitTimeline($patient, $visitTimeline->getCollection());

        return $visitTimeline;
    }

    /**
     * Decorate timeline entries with derived badges, prescriptions, and attachments.
     */
    private function decorateVisitTimeline(Patient $patient, Collection $checkups): void
    {
        if ($checkups->isEmpty()) {
            return;
        }

        $firstVisitId = $patient->checkups()->orderBy('checkup_date')->orderBy('id')->value('id');
        $mostRecentVisitId = $patient->checkups()->orderByDesc('checkup_date')->orderByDesc('id')->value('id');

        $visitDates = $checkups
            ->map(fn (PatientCheckup $checkup) => $checkup->checkup_date instanceof Carbon
                ? $checkup->checkup_date->copy()
                : Carbon::parse($checkup->checkup_date ?? now()))
            ->filter();

        if ($visitDates->isEmpty()) {
            return;
        }

        $dateStart = $visitDates->sort()->first()->copy()->startOfDay();
        $dateEnd = $visitDates->sortDesc()->first()->copy()->endOfDay();

        $standardPrescriptions = Prescription::query()
            ->with(['doctor', 'medicines.medicine'])
            ->where('patient_id', $patient->id)
            ->whereBetween('prescribed_date', [$dateStart->toDateString(), $dateEnd->toDateString()])
            ->get()
            ->groupBy(fn (Prescription $prescription) => optional($prescription->prescribed_date)->format('Y-m-d'));

        $simplePrescriptions = SimplePrescription::query()
            ->with(['doctor', 'medicines'])
            ->where('patient_id', $patient->id)
            ->whereBetween('prescribed_date', [$dateStart->toDateString(), $dateEnd->toDateString()])
            ->get()
            ->groupBy(fn (SimplePrescription $prescription) => optional($prescription->prescribed_date)->format('Y-m-d'));

        $files = PatientFile::query()
            ->with('uploader')
            ->where('patient_id', $patient->id)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->latest()
            ->get()
            ->groupBy(fn (PatientFile $file) => optional($file->created_at)->toDateString());

        $images = PatientImage::query()
            ->with('uploader')
            ->where('patient_id', $patient->id)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->latest()
            ->get()
            ->groupBy(fn (PatientImage $image) => optional($image->created_at)->toDateString());

        foreach ($checkups as $checkup) {
            $dateKey = optional($checkup->checkup_date)->toDateString();

            $timelinePrescriptions = $this->transformPrescriptions($standardPrescriptions->get($dateKey, collect()), false)
                ->merge($this->transformPrescriptions($simplePrescriptions->get($dateKey, collect()), true))
                ->values();

            $checkup->setAttribute('is_first_visit', (int) $checkup->id === (int) $firstVisitId);
            $checkup->setAttribute('is_most_recent_visit', (int) $checkup->id === (int) $mostRecentVisitId);
            $checkup->setAttribute('timeline_prescriptions', $timelinePrescriptions);
            $checkup->setAttribute('timeline_prescription_summary', $this->buildPrescriptionSummary($timelinePrescriptions));
            $checkup->setAttribute(
                'timeline_attachments',
                $this->buildVisitAttachments(
                    $files->get($dateKey, collect()),
                    $images->get($dateKey, collect())
                )
            );
        }
    }

    /**
     * Transform prescriptions into presentation-friendly timeline payloads.
     */
    private function transformPrescriptions(Collection $prescriptions, bool $isSimple): Collection
    {
        return $prescriptions->map(function ($prescription) use ($isSimple) {
            $medicines = $prescription->medicines
                ->map(fn ($medicine) => $this->formatPrescriptionMedicine($medicine, $isSimple))
                ->values();

            return [
                'id' => $prescription->id,
                'type' => $isSimple ? 'simple' : 'standard',
                'number' => $prescription->prescription_number,
                'diagnosis' => $prescription->diagnosis,
                'notes' => $prescription->notes,
                'doctor_name' => $prescription->doctor?->full_name,
                'date' => optional($prescription->prescribed_date)->format('M d, Y'),
                'medicines' => $medicines,
                'summary' => $medicines->pluck('name')->filter()->take(3)->implode(', '),
            ];
        });
    }

    /**
     * Format a prescription medicine for drawer display.
     */
    private function formatPrescriptionMedicine(object $medicine, bool $isSimple): array
    {
        return [
            'name' => $isSimple
                ? $medicine->medicine_name
                : ($medicine->medicine_name_display ?? $medicine->medicine_name),
            'dosage' => $medicine->dosage,
            'frequency' => $isSimple ? $medicine->frequency : ($medicine->frequency_display ?? $medicine->frequency),
            'duration' => $isSimple ? $medicine->duration : ($medicine->duration_display ?? $medicine->duration),
            'instructions' => $medicine->instructions,
        ];
    }

    /**
     * Build a concise prescription summary for the timeline card.
     */
    private function buildPrescriptionSummary(Collection $prescriptions): ?string
    {
        $medicineNames = $prescriptions
            ->flatMap(fn (array $prescription) => collect($prescription['medicines'] ?? [])->pluck('name'))
            ->filter()
            ->unique()
            ->values();

        if ($medicineNames->isNotEmpty()) {
            $visible = $medicineNames->take(3)->implode(', ');
            $remaining = max($medicineNames->count() - 3, 0);

            return $remaining > 0
                ? sprintf('%s +%d more', $visible, $remaining)
                : $visible;
        }

        $diagnoses = $prescriptions->pluck('diagnosis')->filter()->unique()->values();

        if ($diagnoses->isNotEmpty()) {
            return $diagnoses->take(2)->implode(' · ');
        }

        return null;
    }

    /**
     * Build a visit attachment collection from files and images uploaded that day.
     */
    private function buildVisitAttachments(Collection $files, Collection $images): Collection
    {
        $fileAttachments = $files->map(fn (PatientFile $file) => [
            'type' => __('File'),
            'label' => $file->original_name,
            'url' => $file->file_url,
            'description' => $file->description,
            'meta' => $file->category_display,
            'icon' => $file->file_icon,
            'uploaded_at' => optional($file->created_at)->format('M d, Y g:i A'),
            'uploaded_at_sort' => optional($file->created_at)->timestamp,
            'uploader' => $file->uploader?->full_name,
        ]);

        $imageAttachments = $images->map(fn (PatientImage $image) => [
            'type' => __('Image'),
            'label' => $image->filename,
            'url' => $image->url,
            'description' => $image->caption,
            'meta' => __('Clinical image'),
            'icon' => 'fas fa-image text-success',
            'uploaded_at' => optional($image->created_at)->format('M d, Y g:i A'),
            'uploaded_at_sort' => optional($image->created_at)->timestamp,
            'uploader' => $image->uploader?->full_name,
        ]);

        return $fileAttachments
            ->merge($imageAttachments)
            ->sortByDesc('uploaded_at_sort')
            ->values();
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $patient->loadMissing([
            'medicalOverview',
            'modules',
            'dentalProfile',
            'entProfile',
            'pediatricProfile',
            'nutritionProfile',
        ]);

        return view('patients.edit', array_merge(
            ['patient' => $patient, 'selectedModuleKeys' => $this->selectedModuleKeysForPatient($patient)],
            $this->patientFormViewData($patient->clinic)
        ));
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'whatsapp_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:1|max:500',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-,NA',
            'birth_weight' => 'nullable|integer|min:200|max:7000',
            'gestational_age_weeks' => 'nullable|integer|min:20|max:45',
            'allergies' => 'nullable|string',
            'is_pregnant' => 'boolean',
            'chronic_illnesses' => 'nullable|string',
            'surgeries_history' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'current_medications_summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'selected_modules' => 'nullable|array',
            'selected_modules.*' => ['string', Rule::in(collect(PatientProfileModuleRegistry::all())->pluck('key')->all())],
            'medical_flags' => 'nullable|array',
            'medical_flags.pregnant' => 'nullable|boolean',
            'medical_flags.diabetic' => 'nullable|boolean',
            'medical_flags.hypertensive' => 'nullable|boolean',
            'dental_oral_hygiene' => ['nullable', 'string', Rule::in(array_keys(PatientDental::ORAL_HYGIENE_STATUSES))],
            'dental_smoking_status' => ['nullable', 'string', Rule::in(array_keys(PatientDental::SMOKING_STATUSES))],
            'pediatric_birth_weight' => 'nullable|integer|min:200|max:7000',
            'pediatric_gestational_age_weeks' => 'nullable|integer|min:20|max:45',
            'nutrition_height' => 'nullable|numeric|min:50|max:300',
            'nutrition_weight' => 'nullable|numeric|min:1|max:500',
            'ent_notes' => 'nullable|string',
            'is_active' => 'boolean',
            'medical_files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $nutritionHeight = $request->has('nutrition_height')
            ? ($request->input('nutrition_height') ?: null)
            : ($request->has('height') ? ($request->input('height') ?: null) : $patient->height);
        $nutritionWeight = $request->has('nutrition_weight')
            ? ($request->input('nutrition_weight') ?: null)
            : ($request->has('weight') ? ($request->input('weight') ?: null) : $patient->weight);
        $pediatricBirthWeight = $request->has('pediatric_birth_weight')
            ? ($request->input('pediatric_birth_weight') ?: null)
            : ($request->has('birth_weight') ? ($request->input('birth_weight') ?: null) : $patient->birth_weight);
        $pediatricGestationalAgeWeeks = $request->has('pediatric_gestational_age_weeks')
            ? ($request->input('pediatric_gestational_age_weeks') ?: null)
            : ($request->has('gestational_age_weeks') ? ($request->input('gestational_age_weeks') ?: null) : $patient->gestational_age_weeks);
        $pregnantFlag = $request->boolean('_supports_extended_medical_flags') || $request->has('is_pregnant')
            ? ($request->boolean('medical_flags.pregnant') || $request->boolean('is_pregnant'))
            : $patient->is_pregnant;

        $patient->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'whatsapp_phone' => $request->whatsapp_phone,
            'email' => $request->email,
            'address' => $request->address,
            'job' => $request->job,
            'education' => $request->education,
            'height' => $nutritionHeight,
            'weight' => $nutritionWeight,
            'is_pregnant' => $pregnantFlag,
            'blood_type' => $request->blood_type,
            'birth_weight' => $pediatricBirthWeight,
            'gestational_age_weeks' => $pediatricGestationalAgeWeeks,
            'notes' => $request->notes,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncMedicalOverview($patient, $request);
        $selectedModules = $this->eligibleSelectedModulesForPatient(
            $patient,
            $this->requestedModuleKeys($request)
        );
        $this->syncSelectedModules($patient, $selectedModules);
        $this->syncModuleProfiles($patient, $request, $selectedModules);

        // Handle medical history file uploads (same behavior as store())
        if ($request->hasFile('medical_files')) {
            $tenantDir = StorageQuotaService::getTenantStoragePath($patient->clinic_id, 'documents');
            foreach ($request->file('medical_files') as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($tenantDir, $filename, StorageQuotaService::SPACES_DISK);

                $fileSize = $file->getSize();
                PatientFile::create([
                    'patient_id' => $patient->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $fileSize,
                    'mime_type' => $file->getMimeType(),
                    'category' => 'medical_report',
                    'description' => 'Uploaded during patient update',
                    'uploaded_by' => auth()->id(),
                ]);

                // Increment storage usage
                app(StorageQuotaService::class)->incrementUsage($patient->clinic_id, $fileSize);
            }
        }

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient updated successfully.');
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        // Check if patient has any related records
        if ($patient->prescriptions()->count() > 0 ||
            $patient->appointments()->count() > 0 ||
            $patient->invoices()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete patient with existing medical records. Deactivate instead.']);
        }

        $patient->delete();

        return redirect()->route('patients.index')
                        ->with('success', 'Patient deleted successfully.');
    }

    /**
     * Show patient history.
     */
    public function history(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $patient->load([
            'checkups' => function ($q) {
                $q->with('recorder')->latest('checkup_date');
            },
            'prescriptions' => function ($q) {
                $q->with(['doctor', 'medicines'])->latest('prescribed_date');
            },
            'labRequests' => function ($q) {
                $q->with(['doctor', 'tests'])->latest('requested_date');
            },
            'dietPlans' => function ($q) {
                $q->with('doctor')->latest('start_date');
            },
            'appointments' => function ($q) {
                $q->with('doctor')->latest('appointment_datetime');
            }
        ]);

        return view('patients.history', compact('patient'));
    }

    /**
     * Get patients list for API/AJAX requests.
     */
    public function apiList(Request $request)
    {
        $user = auth()->user();

        // Check if user has a clinic assigned (allow super admin without clinic)
        if (!$user->clinic_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be assigned to a clinic to access patients.',
                'data' => [],
                'count' => 0
            ], 403);
        }

        $query = Patient::query()
                        ->select('id', 'patient_id', 'first_name', 'last_name');
        if ($user->clinic_id) {
            $query->where('clinic_id', $user->clinic_id);
        }

        // Filter patients based on user role
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
	        if ($user->role === 'assistant') {
	            // Assistants with patient permissions should be able to search all clinic patients.
	            // If the assistant does not have patient permissions, scope to assigned doctors'
	            // interactions (or patients they created).
	            if (!$user->hasAnyPermission(['patients_view', 'patients_create', 'patients_edit', 'patients_delete', 'patients_files', 'patients_history'])) {
	                $doctorIds = $user->allowedDoctorIds();
	                if (!empty($doctorIds)) {
	                    $query->where(function($q) use ($doctorIds, $user) {
	                        $q->whereHas('appointments', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('prescriptions', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('simplePrescriptions', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('labRequests', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhereHas('dietPlans', function($subQ) use ($doctorIds) {
	                            $subQ->whereIn('doctor_id', $doctorIds);
	                        })
	                        ->orWhere('created_by', $user->id);
	                    });
	                } else {
	                    $query->where('created_by', $user->id);
	                }
	            }
	        } elseif ($user->role === 'dental_dept') {
                // Dentists see only patients they created or have dental records for
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('dentalCharts', function($subQ) use ($user) {
                        $subQ->where('created_by', $user->id);
                    })
                    ->orWhereHas('dentalTreatments', function($subQ) use ($user) {
                        $subQ->where('assigned_doctor_id', $user->id);
                    });
                });
            } else {
                // Regular doctors and other roles
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('appointments', function($appointmentQuery) use ($user) {
                        $appointmentQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('prescriptions', function($prescriptionQuery) use ($user) {
                        $prescriptionQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('simplePrescriptions', function($simplePrescriptionQuery) use ($user) {
                        $simplePrescriptionQuery->where('doctor_id', $user->id);
                    });
                });
            }
        }

        $query->orderBy('first_name')
                        ->orderBy('last_name');

        // Apply smart search with minimum length validation
        $searchTerm = $this->getValidatedSearchTerm($request);
        if ($searchTerm !== null) {
            $query->search($searchTerm);
        }

        $patients = $query->get();

        return response()->json(
            $this->getSearchResponse($patients, $searchTerm, 'Start typing to search patients...')
        );
    }

    /**
     * Add a new checkup for the patient.
     */
    public function addCheckup(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $validationRules = [
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:50|max:300',
            'blood_pressure' => 'nullable|string|regex:/^\d{2,3}\/\d{2,3}$/',
            'heart_rate' => 'nullable|integer|min:30|max:200',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:50',
            'blood_sugar' => 'nullable|numeric|min:20|max:600',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'custom_vital_signs' => 'nullable|array',
        ];

        // Add validation rules for custom vital signs
        $customSigns = \App\Models\CustomVitalSignsConfig::forClinic($patient->clinic_id)
                                                         ->active()
                                                         ->get();

        foreach ($customSigns as $sign) {
            $fieldName = "custom_vital_signs.{$sign->id}";
            $rules = ['nullable'];

            if ($sign->type === 'number') {
                $rules[] = 'numeric';
                if ($sign->min_value) $rules[] = "min:{$sign->min_value}";
                if ($sign->max_value) $rules[] = "max:{$sign->max_value}";
            } elseif ($sign->type === 'select' && $sign->options) {
                $rules[] = 'in:' . implode(',', array_keys($sign->options));
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        $request->validate($validationRules);

        // Process custom vital signs
        $customVitalSigns = [];
        if ($request->has('custom_vital_signs')) {
            foreach ($request->custom_vital_signs as $configId => $value) {
                if ($value !== null && $value !== '') {
                    $customVitalSigns[$configId] = $value;
                }
            }
        }

        PatientCheckup::create([
            'patient_id' => $patient->id,
            'weight' => $request->weight,
            'height' => $request->height,
            'blood_pressure' => $request->blood_pressure,
            'heart_rate' => $request->heart_rate,
            'temperature' => $request->temperature,
            'respiratory_rate' => $request->respiratory_rate,
            'blood_sugar' => $request->blood_sugar,
            'custom_vital_signs' => $customVitalSigns ?: null,
            'symptoms' => $request->symptoms,
            'notes' => $request->notes,
            'recommendations' => $request->recommendations,
            'recorded_by' => auth()->id(),
            'checkup_date' => now(),
        ]);

        return back()->with('success', 'Checkup recorded successfully.');
    }

    /**
     * Upload a file for the patient.
     */
    public function uploadFile(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'file' => 'required|file|max:' . config('app.concure.max_file_size'),
            'category' => 'required|in:lab_result,medicine_photo,medical_report,ent_audiometry,ent_scan,other',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $allowedTypes = config('app.concure.allowed_file_types');

        // Restrict lab results to images/PDF only
        if ($request->category === 'lab_result') {
            $labAllowed = ['jpg','jpeg','png','pdf'];
            if (!in_array($ext, $labAllowed)) {
                return back()->withErrors(['file' => __('Only JPG, JPEG, PNG, or PDF allowed for lab results.')]);
            }
        } elseif (!in_array($ext, $allowedTypes)) {
            return back()->withErrors(['file' => __('File type not allowed.')]);
        }

        // Determine tenant folder based on category
        $typeMap = ['lab_result' => 'lab', 'medicine_photo' => 'images', 'medical_report' => 'documents', 'ent_audiometry' => 'documents', 'ent_scan' => 'images', 'other' => 'documents'];
        $tenantDir = StorageQuotaService::getTenantStoragePath($patient->clinic_id, $typeMap[$request->category] ?? 'documents');

        // Generate unique filename
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs($tenantDir, $filename, StorageQuotaService::SPACES_DISK);

        $fileSize = $file->getSize();
        PatientFile::create([
            'patient_id' => $patient->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $fileSize,
            'category' => $request->category,
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        // Increment storage usage
        app(StorageQuotaService::class)->incrementUsage($patient->clinic_id, $fileSize);

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Update a patient's file (e.g., description/caption).
     */
    public function updateFile(Request $request, Patient $patient, PatientFile $file)
    {
        $this->authorizePatientAccess($patient);
        if ($file->patient_id !== $patient->id) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
        ]);

        $file->update([
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', __('File updated successfully.'));
    }

    /**
     * Delete a patient's file.
     */
    public function destroyFile(Patient $patient, PatientFile $file)
    {
        $this->authorizePatientAccess($patient);
        if ($file->patient_id !== $patient->id) {
            abort(403);
        }

        $file->delete();

        return back()->with('success', __('File deleted successfully.'));
    }


    /**
     * Check patient permission (disabled in development mode)
     */
    private function checkPatientPermission($permission)
    {
        // DEVELOPMENT MODE: Disable all authorization checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return;
        }

        $user = auth()->user();
        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized access to patient management.');
        }
    }

    private function syncMedicalOverview(Patient $patient, Request $request): void
    {
        $existingFlags = $patient->medicalOverview?->flags ?? [];
        $submittedFlags = $request->input('medical_flags', []);
        $supportsExtendedFlags = $request->boolean('_supports_extended_medical_flags');

        $flags = [
            'pregnant' => $supportsExtendedFlags || $request->has('is_pregnant')
                ? ($request->boolean('medical_flags.pregnant') || $request->boolean('is_pregnant'))
                : (bool) data_get($existingFlags, 'pregnant', false),
            'diabetic' => $supportsExtendedFlags
                ? (bool) data_get($submittedFlags, 'diabetic', false)
                : (bool) data_get($existingFlags, 'diabetic', false),
            'hypertensive' => $supportsExtendedFlags
                ? (bool) data_get($submittedFlags, 'hypertensive', false)
                : (bool) data_get($existingFlags, 'hypertensive', false),
        ];

        $patient->medicalOverview()->updateOrCreate(
            [],
            [
                'allergies' => $request->input('allergies'),
                'chronic_diseases' => $request->input('chronic_illnesses'),
                'surgeries' => $request->input('surgeries_history'),
                'medical_history' => $request->input('medical_history'),
                'current_medications_summary' => $request->input('current_medications_summary'),
                'flags' => array_filter($flags),
            ]
        );
    }

    private function requestedModuleKeys(Request $request): array
    {
        $selectedModules = collect($request->input('selected_modules', []))
            ->filter(fn ($module) => is_string($module) && trim($module) !== '')
            ->map(fn (string $module) => strtolower(trim($module)));

        $autoDetectedModules = collect([
            $request->filled('dental_oral_hygiene') || $request->filled('dental_smoking_status') ? 'dental' : null,
            $request->filled('pediatric_birth_weight') || $request->filled('pediatric_gestational_age_weeks') || $request->filled('birth_weight') || $request->filled('gestational_age_weeks') ? 'pediatric' : null,
            $request->filled('nutrition_height') || $request->filled('nutrition_weight') || $request->filled('height') || $request->filled('weight') ? 'nutrition' : null,
            $request->filled('ent_notes') ? 'ent' : null,
        ]);

        $allowedModules = collect(PatientProfileModuleRegistry::all())->pluck('key')->all();

        return $selectedModules
            ->merge($autoDetectedModules)
            ->filter()
            ->intersect($allowedModules)
            ->unique()
            ->values()
            ->all();
    }

    private function eligibleSelectedModulesForPatient(Patient $patient, array $requestedModules): array
    {
        $eligibleModules = collect(PatientProfileModuleRegistry::eligibleModulesForPatient($patient))
            ->pluck('key')
            ->all();

        return collect($requestedModules)
            ->intersect($eligibleModules)
            ->values()
            ->all();
    }

    private function syncSelectedModules(Patient $patient, array $selectedModules): void
    {
        $knownModules = collect(PatientProfileModuleRegistry::all())->pluck('key')->all();

        $patient->modules()
            ->whereIn('module_name', $knownModules)
            ->whereNotIn('module_name', $selectedModules)
            ->update(['is_active' => false]);

        foreach ($selectedModules as $module) {
            $patient->modules()->updateOrCreate(
                ['module_name' => $module],
                ['is_active' => true]
            );
        }
    }

    private function patientFormViewData(?Clinic $clinic = null): array
    {
        $clinic ??= $this->resolvePatientFormClinic();

        return [
            'moduleDefinitions' => PatientProfileModuleRegistry::filterVisibleToUser(
                PatientProfileModuleRegistry::modulesForClinic($clinic),
                auth()->user()
            )->all(),
            'medicalFlags' => PatientMedicalOverview::FLAG_LABELS,
            'dentalOralHygieneOptions' => PatientDental::ORAL_HYGIENE_STATUSES,
            'dentalSmokingStatusOptions' => PatientDental::SMOKING_STATUSES,
        ];
    }

    private function resolvePatientFormClinic(): ?Clinic
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        if ($user->clinic_id) {
            return $user->clinic;
        }

        $selectedClinicId = old('clinic_id');

        return $selectedClinicId ? Clinic::query()->find($selectedClinicId) : null;
    }

    private function selectedModuleKeysForPatient(Patient $patient): array
    {
        $activeModuleKeys = $patient->modules
            ->where('is_active', true)
            ->pluck('module_name');

        if ($patient->modules->isEmpty()) {
            $activeModuleKeys = $activeModuleKeys->merge([
                $patient->dentalProfile ? 'dental' : null,
                $patient->pediatricProfile ? 'pediatric' : null,
                $patient->nutritionProfile ? 'nutrition' : null,
                $patient->entProfile ? 'ent' : null,
            ]);
        }

        return $activeModuleKeys
            ->filter()
            ->intersect(collect(PatientProfileModuleRegistry::eligibleModulesForPatient($patient))->pluck('key'))
            ->unique()
            ->values()
            ->all();
    }

    private function syncModuleProfiles(Patient $patient, Request $request, array $selectedModules): void
    {
        if (in_array('dental', $selectedModules, true)) {
            $dentalData = array_filter([
                'oral_hygiene' => $request->input('dental_oral_hygiene'),
                'smoking_status' => $request->input('dental_smoking_status'),
            ], fn ($value) => $value !== null && $value !== '');

            if ($dentalData !== []) {
                $patient->dentalProfile()->updateOrCreate([], $dentalData);
            }
        }

        if (in_array('pediatric', $selectedModules, true)) {
            $pediatricData = array_filter([
                'birth_weight' => $request->input('pediatric_birth_weight', $request->input('birth_weight')),
                'gestational_age' => $request->input('pediatric_gestational_age_weeks', $request->input('gestational_age_weeks')),
            ], fn ($value) => $value !== null && $value !== '');

            if ($pediatricData !== []) {
                $patient->pediatricProfile()->updateOrCreate([], $pediatricData);
            }
        }

        if (in_array('nutrition', $selectedModules, true)) {
            $nutritionData = array_filter([
                'height' => $request->input('nutrition_height', $request->input('height')),
                'weight' => $request->input('nutrition_weight', $request->input('weight')),
            ], fn ($value) => $value !== null && $value !== '');

            if ($nutritionData !== []) {
                $patient->nutritionProfile()->updateOrCreate([], $nutritionData);
            }
        }

        if (in_array('ent', $selectedModules, true)) {
            $entNotes = $request->input('ent_notes');

            if ($entNotes !== null && $entNotes !== '') {
                $patient->entProfile()->updateOrCreate([], ['notes' => $entNotes]);
            }
        }
    }

    /**
     * Show the import form.
     */
    public function showImport()
    {
        $this->checkPatientPermission('patients_create');

        return view('patients.import');
    }

    /**
     * Download the import template.
     */
    public function downloadTemplate(Request $request)
    {
        $this->checkPatientPermission('patients_create');

        $includeSampleData = $request->boolean('sample', true);
        $format = $request->get('format', 'xlsx'); // Default to Excel

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Get headers
        $headers = PatientsImport::getExpectedHeaders();
        $headerKeys = array_keys($headers);
        // Set headers
        $sheet->fromArray([$headerKeys], null, 'A1');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:' . chr(64 + count($headerKeys)) . '1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', chr(64 + count($headerKeys))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add sample data if requested
        if ($includeSampleData) {
            $sampleData = PatientsImport::getSampleData();
            $startRow = 2;
            foreach ($sampleData as $rowData) {
                $rowValues = [];
                foreach ($headerKeys as $key) {
                    $rowValues[] = $rowData[$key] ?? '';
                }
                $sheet->fromArray([$rowValues], null, 'A' . $startRow);
                $startRow++;
            }
        }

        // Generate filename
        $filename = 'patients_import_template_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create writer and return response
        $writer = new Xlsx($spreadsheet);

        return response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import patients from uploaded file.
     */
    public function import(Request $request)
    {
        $this->checkPatientPermission('patients_create');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new PatientsImport();

            Excel::import($import, $request->file('file'));

            $message = "Import completed successfully! ";
            $message .= "Imported: {$import->getImportedCount()} patients. ";

            if ($import->getSkippedCount() > 0) {
                $message .= "Skipped: {$import->getSkippedCount()} patients (duplicates or errors).";
            }

            if ($import->hasErrors() || (method_exists($import, 'hasWarnings') && $import->hasWarnings())) {
                $errors = $import->getErrors();
                $warnings = method_exists($import, 'getWarnings') ? $import->getWarnings() : [];

                return redirect()->route('patients.import')
                    ->with('warning', $message)
                    ->with('import_errors_list', array_slice($errors, 0, 50))
                    ->with('import_warnings_list', array_slice($warnings, 0, 50));
            }

            return redirect()->route('patients.import')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('patients.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export patients to Excel
     */
    public function export()
    {
        $user = auth()->user();

        try {
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            $filename = 'patients_export_' . date('Y-m-d_His') . '.xlsx';
            $clinicId = $user->clinic_id;

            return Excel::download(
                new PatientsExport($clinicId, $user),
                $filename,
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        } catch (\Exception $e) {
            return redirect()->route('patients.index')
                ->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete patients
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'patient_ids' => 'required|array',
            'patient_ids.*' => 'exists:patients,id',
        ]);

        $user = auth()->user();

        try {
            DB::beginTransaction();

            $query = Patient::whereIn('id', $request->patient_ids);

            // Restrict to clinic for non-super admin users
            if ($user->clinic_id) {
                $query->where('clinic_id', $user->clinic_id);
            }

            // Filter patients based on user role
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('appointments', function($appointmentQuery) use ($user) {
                        $appointmentQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('prescriptions', function($prescriptionQuery) use ($user) {
                        $prescriptionQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('simplePrescriptions', function($simplePrescriptionQuery) use ($user) {
                        $simplePrescriptionQuery->where('doctor_id', $user->id);
                    });
                });
            }

            $count = $query->count();

            // Delete related records first
            foreach ($query->get() as $patient) {
                // Delete patient files from storage (supports both Spaces and legacy local)
                $files = PatientFile::where('patient_id', $patient->id)->get();
                foreach ($files as $file) {
                    StorageQuotaService::deleteFromDisk($file->file_path);
                }

                // Delete related records
                $patient->files()->delete();
                $patient->checkups()->delete();
                $patient->prescriptions()->delete();
                $patient->labRequests()->delete();
                $patient->dietPlans()->delete();
                $patient->appointments()->delete();
                $patient->invoices()->delete();
                $patient->communicationLogs()->delete();
            }

            // Delete patients
            $query->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} patient(s).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all patients for the current clinic
     */
    public function clearAll(Request $request)
    {
        $user = auth()->user();

        if (!$user->clinic_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be assigned to a clinic to perform this action.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $query = Patient::query();

            // Restrict to clinic for non-super admin users
            if ($user->clinic_id) {
                $query->where('clinic_id', $user->clinic_id);
            }

            // Filter patients based on user role
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('appointments', function($appointmentQuery) use ($user) {
                        $appointmentQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('prescriptions', function($prescriptionQuery) use ($user) {
                        $prescriptionQuery->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('simplePrescriptions', function($simplePrescriptionQuery) use ($user) {
                        $simplePrescriptionQuery->where('doctor_id', $user->id);
                    });
                });
            }

            $count = $query->count();

            // Delete related records first
            foreach ($query->get() as $patient) {
                // Delete patient files from storage (supports both Spaces and legacy local)
                $files = PatientFile::where('patient_id', $patient->id)->get();
                foreach ($files as $file) {
                    StorageQuotaService::deleteFromDisk($file->file_path);
                }

                // Delete related records
                $patient->files()->delete();
                $patient->checkups()->delete();
                $patient->prescriptions()->delete();
                $patient->labRequests()->delete();
                $patient->dietPlans()->delete();
                $patient->appointments()->delete();
                $patient->invoices()->delete();
                $patient->communicationLogs()->delete();
            }

            // Delete all patients
            $query->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared all {$count} patient(s).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Clear all failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
