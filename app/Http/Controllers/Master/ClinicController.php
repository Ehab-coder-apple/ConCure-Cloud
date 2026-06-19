<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClinicController extends Controller
{
    /**
     * Common clinic specialties for Master forms/filters.
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

    /**
     * Display a listing of clinics.
     */
    public function index(Request $request)
    {
        $hasIsDemo = Schema::hasColumn('clinics', 'is_demo');
        $hasSpeciality = Schema::hasColumn('clinics', 'speciality');
        $hasCity = Schema::hasColumn('clinics', 'city');
        $hasArea = Schema::hasColumn('clinics', 'area');
        $hasStreet = Schema::hasColumn('clinics', 'street');

        $query = Clinic::withCount('users')
            ->with(['users' => function($q) {
                $q->where('role', 'admin');
            }]);

        $this->applyAccessibleClinicScope($query);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Clinic type filter (Tenant vs Demo)
        if ($hasIsDemo && $request->filled('clinic_type')) {
            if ($request->clinic_type === 'tenant') {
                $query->where('is_demo', false);
            } elseif ($request->clinic_type === 'demo') {
                $query->where('is_demo', true);
            }
        }

        // Speciality filter
        if ($hasSpeciality && $request->filled('speciality')) {
            $query->where('speciality', $request->speciality);
        }

        // Location filters (fallback to legacy address for older records)
        if ($request->filled('city')) {
            $city = $request->city;
	    	    $query->where(function ($q) use ($city, $hasCity) {
	    	        if ($hasCity) {
	    	            $q->where('city', 'like', "%{$city}%")
	    	              ->orWhere('address', 'like', "%{$city}%");
	    	            return;
	    	        }

	    	        // If column doesn't exist yet (deployment window), filter using legacy address only.
	    	        $q->where('address', 'like', "%{$city}%");
	    	    });
        }
        if ($request->filled('area')) {
            $area = $request->area;
	    	    $query->where(function ($q) use ($area, $hasArea) {
	    	        if ($hasArea) {
	    	            $q->where('area', 'like', "%{$area}%")
	    	              ->orWhere('address', 'like', "%{$area}%");
	    	            return;
	    	        }

	    	        $q->where('address', 'like', "%{$area}%");
	    	    });
        }
        if ($request->filled('street')) {
            $street = $request->street;
	    	    $query->where(function ($q) use ($street, $hasStreet) {
	    	        if ($hasStreet) {
	    	            $q->where('street', 'like', "%{$street}%")
	    	              ->orWhere('address', 'like', "%{$street}%");
	    	            return;
	    	        }

	    	        $q->where('address', 'like', "%{$street}%");
	    	    });
        }

        $clinics = $query->latest()->paginate(15)->withQueryString();

        $specialities = $this->specialityOptions();

        return view('master.clinics.index', compact('clinics', 'specialities'));
    }

    /**
     * Show the form for creating a new clinic.
     */
    public function create()
    {
        $this->authorizeClinicCreationAccess();

        $specialities = $this->specialityOptions();
        $availableModules = \App\Models\Clinic::AVAILABLE_MODULES;
        $moduleGroups = \App\Models\Clinic::MODULE_GROUPS;
        $countries = \App\Models\Country::where('is_active', true)->orderBy('name')->get();
        $defaultContractTemplate = \App\Http\Controllers\Master\SettingsController::getDefaultContractTemplate();
        return view('master.clinics.create', compact('specialities', 'availableModules', 'moduleGroups', 'countries', 'defaultContractTemplate'));
    }

    /**
     * Store a newly created clinic.
     */
    public function store(Request $request)
    {
        $this->authorizeClinicCreationAccess();

        $creator = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clinics,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'speciality' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'max_users' => 'required|integer|min:1|max:1000',
            'clinic_type' => 'nullable|in:tenant,demo',
            // Billing fields
            'billing_user_price' => 'nullable|numeric|min:0|max:1000000',
            'billing_user_count' => 'nullable|integer|min:1|max:100000',
            'service_charge_amount' => 'nullable|numeric|min:0|max:10000000',
            'service_charge_date' => 'nullable|date',
            'service_charge_note' => 'nullable|string|max:500',
            'contract_renewal_at' => 'nullable|date|after_or_equal:1900-01-01',
            'enabled_modules' => 'nullable|array',
            'enabled_modules.*' => ['string', Rule::in(array_keys(Clinic::AVAILABLE_MODULES))],
            // Admin
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            // Contract (only for tenant clinics)
            'require_contract' => 'nullable|boolean',
            'contract_content' => 'required_if:require_contract,1|string',
            'contract_title' => 'nullable|string|max:255',
            'contract_duration_months' => 'nullable|integer|min:1|max:120',
            'annual_fee' => 'nullable|numeric|min:0|max:10000000',
        ]);

        DB::beginTransaction();
        try {
            $addressParts = collect([
                $request->input('street'),
                $request->input('area'),
                $request->input('city'),
            ])
                ->map(fn ($v) => is_string($v) ? trim($v) : $v)
                ->filter(fn ($v) => !empty($v));

            $computedAddress = $addressParts->implode(', ');

            // Create clinic
            $clinicData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                // keep legacy address populated for backward compatibility
                'address' => $request->filled('address') ? $request->address : ($computedAddress !== '' ? $computedAddress : null),
                'max_users' => $request->max_users,
                'is_active' => true,
                'activated_at' => now(),
            ];

            // Optional structured fields (guarded for safe deployments)
            if (Schema::hasColumn('clinics', 'speciality')) { $clinicData['speciality'] = $request->input('speciality'); }
            if (Schema::hasColumn('clinics', 'city')) { $clinicData['city'] = $request->input('city'); }
            if (Schema::hasColumn('clinics', 'area')) { $clinicData['area'] = $request->input('area'); }
            if (Schema::hasColumn('clinics', 'street')) { $clinicData['street'] = $request->input('street'); }

            // Country assignment (for Vaccination module) — default to Iraq if none selected
            if (Schema::hasColumn('clinics', 'country_id')) {
                $countryId = $request->input('country_id')
                    ?: \App\Models\Country::where('iso_code', 'IQ')->value('id');
                $clinicData['country_id'] = $countryId;
            }

            if (Schema::hasColumn('clinics', 'is_demo')) {
                $clinicData['is_demo'] = $request->input('clinic_type') === 'demo';
            }

            // Export permission for demo clinics
            if (Schema::hasColumn('clinics', 'can_export')) {
                $clinicData['can_export'] = $request->input('clinic_type') === 'demo'
                    ? (bool) $request->input('can_export', false)
                    : true; // Regular clinics always can export
            }

            // Optional billing fields
            if (Schema::hasColumn('clinics', 'billing_user_price')) { $clinicData['billing_user_price'] = $request->input('billing_user_price'); }
            if (Schema::hasColumn('clinics', 'billing_user_count')) { $clinicData['billing_user_count'] = $request->input('billing_user_count'); }
            if (Schema::hasColumn('clinics', 'service_charge_amount')) { $clinicData['service_charge_amount'] = $request->input('service_charge_amount'); }
            if (Schema::hasColumn('clinics', 'service_charge_date')) { $clinicData['service_charge_date'] = $request->input('service_charge_date'); }
            if (Schema::hasColumn('clinics', 'service_charge_note')) { $clinicData['service_charge_note'] = $request->input('service_charge_note'); }
            if (Schema::hasColumn('clinics', 'contract_renewal_at')) { $clinicData['contract_renewal_at'] = $request->input('contract_renewal_at') ?: null; }

            $enabledModules = collect($request->input('enabled_modules', []))
                ->filter(fn ($module) => is_string($module) && $module !== '')
                ->unique()
                ->values()
                ->all();

            // Module access control
            if (Schema::hasColumn('clinics', 'enabled_modules')) {
                $clinicData['enabled_modules'] = $enabledModules !== [] ? $enabledModules : null;
            }

            if (Schema::hasColumn('clinics', 'settings') && $creator?->isMasterAdmin()) {
                $clinicData['settings'] = array_merge(
                    is_array($clinicData['settings'] ?? null) ? $clinicData['settings'] : [],
                    [User::CLINIC_SETTINGS_SCOPED_OWNER_ID => $creator->id]
                );
            }

            $clinic = Clinic::create($clinicData);

            if ($creator?->isMasterAdmin()) {
                $creator->superAdminClinics()->syncWithoutDetaching([$clinic->id]);
            }

            // Create admin user for the clinic
            $adminUsername = strtolower(str_replace(' ', '', $request->admin_first_name . $request->admin_last_name));
            $originalUsername = $adminUsername;
            $counter = 1;
            
            while (User::where('username', $adminUsername)->exists()) {
                $adminUsername = $originalUsername . $counter;
                $counter++;
            }

            User::create([
                'first_name' => $request->admin_first_name,
                'last_name' => $request->admin_last_name,
                'email' => $request->admin_email,
                'username' => $adminUsername,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'clinic_id' => $clinic->id,
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Create contract if required (only for tenant clinics, not demos)
            if ($request->has('require_contract') && $request->require_contract && $request->clinic_type !== 'demo') {
                $startDate = now();
                $contractDuration = $request->contract_duration_months ?? 12;
                $endDate = $startDate->copy()->addMonths($contractDuration);

                \App\Models\ClinicContract::create([
                    'clinic_id' => $clinic->id,
                    'contract_type' => 'service_agreement',
                    'contract_title' => $request->contract_title ?? 'ConCure Cloud Service Agreement',
                    'contract_content' => $request->contract_content,
                    'annual_fee' => $request->annual_fee,
                    'monthly_price' => $request->billing_user_price, // Kept for compatibility
                    'contract_duration_months' => $contractDuration,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'requires_renewal' => true,
                    'status' => 'draft', // Created as draft, must be sent by Master
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            $message = 'Clinic created successfully with admin user.';
            if ($request->has('require_contract') && $request->require_contract) {
                $message .= ' The clinic admin must review and accept the contract before accessing the system.';
            }

            return redirect()->route('master.clinics.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create clinic: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified clinic.
     */
    public function show(Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $clinic->load(['users', 'patients', 'prescriptions', 'appointments']);
        
        $stats = [
            'total_users' => $clinic->users()->count(),
            'active_users' => $clinic->users()->where('is_active', true)->count(),
            'total_patients' => $clinic->patients()->count(),
            'total_prescriptions' => $clinic->prescriptions()->count(),
            'total_appointments' => $clinic->appointments()->count(),
            'monthly_patients' => $clinic->patients()->whereMonth('created_at', now()->month)->count(),
        ];

        return view('master.clinics.show', compact('clinic', 'stats'));
    }

    /**
     * Show the form for editing the specified clinic.
     */
    public function edit(Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        // Get the clinic admin user
        $adminUser = $clinic->users()->where('role', 'admin')->first();

        $specialities = $this->specialityOptions();
        $availableModules = \App\Models\Clinic::AVAILABLE_MODULES;
        $moduleGroups = \App\Models\Clinic::MODULE_GROUPS;
        $countries = \App\Models\Country::where('is_active', true)->orderBy('name')->get();

        return view('master.clinics.edit', compact('clinic', 'adminUser', 'specialities', 'availableModules', 'moduleGroups', 'countries'));
    }

    /**
     * Update the specified clinic.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        // Get the admin user for validation
        $adminUser = $clinic->users()->where('role', 'admin')->first();

        // Build validation rules
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('clinics')->ignore($clinic->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'speciality' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'max_users' => 'required|integer|min:1|max:1000',
            'clinic_type' => 'nullable|in:tenant,demo',
            'storage_limit_gb' => 'nullable|numeric|min:0.1|max:10000',
            // Billing fields
            'billing_user_price' => 'nullable|numeric|min:0|max:1000000',
            'billing_user_count' => 'nullable|integer|min:1|max:100000',
            'service_charge_amount' => 'nullable|numeric|min:0|max:10000000',
            'service_charge_date' => 'nullable|date',
            'service_charge_note' => 'nullable|string|max:500',
            'contract_renewal_at' => 'nullable|date|after_or_equal:1900-01-01',
            'enabled_modules' => 'nullable|array',
            'enabled_modules.*' => ['string', Rule::in(array_keys(Clinic::AVAILABLE_MODULES))],
        ];

        // Only validate admin fields if admin user exists
        if ($adminUser) {
            $validationRules['admin_first_name'] = 'required|string|max:255';
            $validationRules['admin_last_name'] = 'required|string|max:255';
            $validationRules['admin_username'] = [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($adminUser->id)
            ];
            $validationRules['admin_email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($adminUser->id)
            ];
        }

        $request->validate($validationRules);

        DB::beginTransaction();
        try {
            $addressParts = collect([
                $request->input('street'),
                $request->input('area'),
                $request->input('city'),
            ])
                ->map(fn ($v) => is_string($v) ? trim($v) : $v)
                ->filter(fn ($v) => !empty($v));

            $computedAddress = $addressParts->implode(', ');

            // Update clinic information
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                // if address textarea exists (older UI), prefer it; otherwise compute from structured fields
                'address' => $request->filled('address') ? $request->address : ($computedAddress !== '' ? $computedAddress : $clinic->address),
                'max_users' => $request->max_users,
            ];

            // Optional structured fields (guarded for safe deployments)
            if (Schema::hasColumn('clinics', 'speciality')) { $updateData['speciality'] = $request->input('speciality'); }
            if (Schema::hasColumn('clinics', 'city')) { $updateData['city'] = $request->input('city'); }
            if (Schema::hasColumn('clinics', 'area')) { $updateData['area'] = $request->input('area'); }
            if (Schema::hasColumn('clinics', 'street')) { $updateData['street'] = $request->input('street'); }

            // Country assignment (for Vaccination module)
            if (Schema::hasColumn('clinics', 'country_id')) {
                $updateData['country_id'] = $request->input('country_id');
            }

            if (Schema::hasColumn('clinics', 'is_demo')) {
                $updateData['is_demo'] = $request->input('clinic_type') === 'demo';
            }

            // Export permission for demo clinics
            if (Schema::hasColumn('clinics', 'can_export')) {
                $updateData['can_export'] = $request->input('clinic_type') === 'demo'
                    ? (bool) $request->input('can_export', false)
                    : true; // Regular clinics always can export
            }

            // Optional billing fields
            if (Schema::hasColumn('clinics', 'billing_user_price')) { $updateData['billing_user_price'] = $request->input('billing_user_price'); }
            if (Schema::hasColumn('clinics', 'billing_user_count')) { $updateData['billing_user_count'] = $request->input('billing_user_count'); }
            if (Schema::hasColumn('clinics', 'service_charge_amount')) { $updateData['service_charge_amount'] = $request->input('service_charge_amount'); }
            if (Schema::hasColumn('clinics', 'service_charge_date')) { $updateData['service_charge_date'] = $request->input('service_charge_date'); }
            if (Schema::hasColumn('clinics', 'service_charge_note')) { $updateData['service_charge_note'] = $request->input('service_charge_note'); }
            if (Schema::hasColumn('clinics', 'contract_renewal_at')) { $updateData['contract_renewal_at'] = $request->input('contract_renewal_at') ?: null; }

            // Storage limit
            if (Schema::hasColumn('clinics', 'storage_limit') && $request->filled('storage_limit_gb')) {
                $updateData['storage_limit'] = (int) ($request->storage_limit_gb * 1024 * 1024 * 1024);
            }

            $enabledModules = collect($request->input('enabled_modules', []))
                ->filter(fn ($module) => is_string($module) && $module !== '')
                ->unique()
                ->values()
                ->all();

            // Module access control
            if (Schema::hasColumn('clinics', 'enabled_modules')) {
                // If no checkboxes checked, store null (= all modules enabled by default)
                $updateData['enabled_modules'] = $enabledModules !== [] ? $enabledModules : null;
            }

            $clinic->update($updateData);

            // Update admin user information if admin exists
            if ($adminUser) {
                $adminUser->update([
                    'first_name' => $request->admin_first_name,
                    'last_name' => $request->admin_last_name,
                    'username' => $request->admin_username,
                    'email' => $request->admin_email,
                ]);
            }

            DB::commit();

            return redirect()->route('master.clinics.show', $clinic)
                ->with('success', 'Clinic and admin information updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update clinic: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Activate a clinic.
     */
    public function activate(Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $clinic->update([
            'is_active' => true,
            'activated_at' => now(),
        ]);

        return back()->with('success', 'Clinic activated successfully.');
    }

    /**
     * Deactivate a clinic.
     */
    public function deactivate(Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $clinic->update([
            'is_active' => false,
        ]);

        return back()->with('success', 'Clinic deactivated successfully.');
    }

    /**
     * Reset admin password for a clinic.
     */
    public function resetAdminPassword(Request $request, Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $admin = $clinic->users()->where('role', 'admin')->first();
        
        if (!$admin) {
            return back()->withErrors(['error' => 'No admin user found for this clinic.']);
        }

        $admin->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Admin password reset successfully.');
    }

    /**
     * Remove the specified clinic.
     */
    public function destroy(Clinic $clinic)
    {
        $this->authorizeClinicDeletion($clinic);

        $clinicId = $clinic->id;
        $clinicName = $clinic->name;

        try {
            // Block deletion if the clinic still has business data; deactivation is the safer path.
            $blockers = [
                'patients'       => fn () => $clinic->patients()->exists(),
                'prescriptions'  => fn () => $clinic->prescriptions()->exists(),
                'appointments'   => fn () => $clinic->appointments()->exists(),
                'medicines'      => fn () => $clinic->medicines()->exists(),
                'lab tests'      => fn () => $clinic->labTests()->exists(),
                'invoices'       => fn () => $clinic->invoices()->exists(),
                'expenses'       => fn () => $clinic->expenses()->exists(),
                'advertisements' => fn () => $clinic->advertisements()->exists(),
            ];

            $found = [];
            foreach ($blockers as $label => $check) {
                if ($check()) {
                    $found[] = $label;
                }
            }

            if (!empty($found)) {
                return back()->withErrors([
                    'error' => 'Cannot delete clinic with existing data (' . implode(', ', $found) . '). Deactivate the clinic instead.',
                ]);
            }

            DB::transaction(function () use ($clinic) {
                $userIds = $clinic->users()->pluck('id')->all();

                // Detach pivot/many-to-many rows that may not be covered by FK cascade.
                if (!empty($userIds) && Schema::hasTable('doctor_assistant')) {
                    DB::table('doctor_assistant')
                        ->where(function ($q) use ($userIds) {
                            $q->whereIn('doctor_id', $userIds)
                              ->orWhereIn('assistant_id', $userIds);
                        })
                        ->delete();
                }

                // Best-effort manual cleanup for tables that may not have ON DELETE CASCADE.
                $clinic->auditLogs()->delete();
                $clinic->activationCodes()->delete();
                $clinic->communicationLogs()->delete();

                // settings has no Eloquent model — clear it via raw query to avoid
                // autoloading the non-existent App\Models\Setting class.
                if (Schema::hasTable('settings')) {
                    DB::table('settings')->where('clinic_id', $clinic->id)->delete();
                }

                // Remove users last (other clinic-scoped tables FK them) and let DB cascade
                // handle remaining clinic_id-scoped tables when the clinic itself is removed.
                $clinic->users()->delete();
                $clinic->delete();
            });

            return redirect()->route('master.clinics.index')
                ->with('success', 'Clinic deleted successfully.');

        } catch (\Throwable $e) {
            Log::error('Master\ClinicController@destroy failed', [
                'clinic_id'   => $clinicId,
                'clinic_name' => $clinicName,
                'message'     => $e->getMessage(),
                'exception'   => get_class($e),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'trace'       => collect(explode("\n", $e->getTraceAsString()))->take(10)->implode("\n"),
            ]);

            $hint = $this->humanizeDeleteError($e);

            return back()->withErrors([
                'error' => 'Failed to delete clinic: ' . $hint,
            ]);
        }
    }

    /**
     * Translate raw deletion exceptions into a hint the operator can act on.
     */
    protected function humanizeDeleteError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (stripos($msg, 'foreign key') !== false || stripos($msg, '1451') !== false) {
            return 'A related record is preventing deletion (foreign key constraint). '
                 . 'Deactivate the clinic instead, or contact engineering with the clinic ID.';
        }

        return $msg ?: 'Unexpected server error. Check storage/logs/laravel.log for details.';
    }

    /**
     * Configure WhatsApp Meta Cloud API for a clinic.
     */
    public function configureWhatsApp(Request $request, Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $request->validate([
            'meta_phone_number_id' => 'required|string',
            'meta_access_token' => 'nullable|string',
        ]);

        try {
            $settings = $clinic->settings ?? [];
            $existingWa = $settings['whatsapp'] ?? [];

            $phoneNumberId = $request->meta_phone_number_id;
            // Use new token if provided, otherwise keep existing
            $accessToken = $request->filled('meta_access_token')
                ? $request->meta_access_token
                : ($existingWa['meta_access_token'] ?? null);

            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Token is required.',
                ], 400);
            }

            // Verify credentials with Meta API
            $verifyResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get("https://graph.facebook.com/v21.0/{$phoneNumberId}");

            if (!$verifyResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Meta API returned: ' . $verifyResponse->body(),
                ], 400);
            }

            $phoneInfo = $verifyResponse->json();

            $settings['whatsapp'] = [
                'provider' => 'meta',
                'meta_phone_number_id' => $phoneNumberId,
                'meta_access_token' => $accessToken,
                'meta_phone_display' => $phoneInfo['display_phone_number'] ?? null,
                'meta_verified_name' => $phoneInfo['verified_name'] ?? null,
                'configured_at' => now()->toDateTimeString(),
                'configured_by' => auth()->id(),
            ];

            $clinic->settings = $settings;
            $clinic->save();

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp configured successfully!',
                'phone_display' => $phoneInfo['display_phone_number'] ?? null,
                'verified_name' => $phoneInfo['verified_name'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Master: Failed to configure WhatsApp for clinic', [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show form to add/manage contract for existing clinic.
     */
    public function manageContract(Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $contracts = $clinic->contracts()->latest()->get();
        $defaultContractTemplate = \App\Http\Controllers\Master\SettingsController::getDefaultContractTemplate();

        return view('master.clinics.manage-contract', compact('clinic', 'contracts', 'defaultContractTemplate'));
    }

    /**
     * Store a new contract for existing clinic.
     */
    public function storeContract(Request $request, Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        $request->validate([
            'contract_title' => 'nullable|string|max:255',
            'contract_content' => 'required|string|min:100',
            'annual_fee' => 'nullable|numeric|min:0|max:10000000',
            'contract_duration_months' => 'nullable|integer|min:1|max:120',
            'start_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now();
            $contractDuration = $request->contract_duration_months ?? 12;
            $endDate = $startDate->copy()->addMonths($contractDuration);

            \App\Models\ClinicContract::create([
                'clinic_id' => $clinic->id,
                'contract_type' => 'service_agreement',
                'contract_title' => $request->contract_title ?? 'ConCure Cloud Service Agreement',
                'contract_content' => $request->contract_content,
                'annual_fee' => $request->annual_fee,
                'contract_duration_months' => $contractDuration,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requires_renewal' => true,
                'status' => 'draft', // Created as draft, must be sent
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('master.clinics.manage-contract', $clinic)
                ->with('success', 'Contract created successfully as DRAFT! Click "Send Contract" to send it to the clinic.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create contract: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mark an existing contract as renewed.
     */
    public function renewContract(Request $request, Clinic $clinic, \App\Models\ClinicContract $contract)
    {
        $this->authorizeAccessibleClinic($clinic);

        $request->validate([
            'annual_fee' => 'nullable|numeric|min:0|max:10000000',
            'contract_duration_months' => 'nullable|integer|min:1|max:120',
        ]);

        DB::beginTransaction();
        try {
            // Mark old contract as expired
            $contract->update(['status' => 'expired']);

            // Create new contract based on old one
            $startDate = now();
            $contractDuration = $request->contract_duration_months ?? $contract->contract_duration_months ?? 12;
            $endDate = $startDate->copy()->addMonths($contractDuration);

            $newContract = \App\Models\ClinicContract::create([
                'clinic_id' => $clinic->id,
                'contract_type' => $contract->contract_type,
                'contract_title' => $contract->contract_title,
                'contract_content' => $contract->contract_content,
                'annual_fee' => $request->annual_fee ?? $contract->annual_fee,
                'contract_duration_months' => $contractDuration,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requires_renewal' => true,
                'status' => 'draft', // Renewed as draft, must be sent
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('master.clinics.manage-contract', $clinic)
                ->with('success', 'Contract renewed successfully as DRAFT! Click "Send Contract" to send it to the clinic.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to renew contract: ' . $e->getMessage()]);
        }
    }

    /**
     * Send a draft contract to the clinic (activates it).
     */
    public function sendContract(Clinic $clinic, \App\Models\ClinicContract $contract)
    {
        $this->authorizeAccessibleClinic($clinic);

        // Verify contract belongs to this clinic
        if ($contract->clinic_id !== $clinic->id) {
            abort(403, 'Contract does not belong to this clinic.');
        }

        // Can only send draft contracts
        if ($contract->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft contracts can be sent. Current status: ' . $contract->status]);
        }

        DB::beginTransaction();
        try {
            // Change status from draft to pending
            $contract->update(['status' => 'pending']);

            DB::commit();

            return redirect()->route('master.clinics.manage-contract', $clinic)
                ->with('success', 'Contract sent successfully! The clinic admin must accept it on next login to access the system.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to send contract: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a draft contract.
     */
    public function deleteContract(Clinic $clinic, \App\Models\ClinicContract $contract)
    {
        $this->authorizeAccessibleClinic($clinic);

        // Verify contract belongs to this clinic
        if ($contract->clinic_id !== $clinic->id) {
            abort(403, 'Contract does not belong to this clinic.');
        }

        // Can only delete draft contracts
        if ($contract->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft contracts can be deleted. Current status: ' . $contract->status]);
        }

        try {
            $contract->delete();

            return redirect()->route('master.clinics.manage-contract', $clinic)
                ->with('success', 'Draft contract deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete contract: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset a demo clinic by deleting all user-generated data.
     */
    public function resetDemoClinic(Clinic $clinic)
    {
        $this->authorizeAccessibleClinic($clinic);

        // Only allow resetting demo clinics
        if (!$clinic->is_demo) {
            return back()->withErrors(['error' => 'Only demo clinics can be reset.']);
        }

        DB::beginTransaction();
        try {
            // Get patient IDs before deleting patients (for tables that don't have clinic_id)
            $patientIds = $clinic->patients()->pluck('id')->toArray();

            // Delete data in tables that might not have clinic_id but have patient_id
            // Do this BEFORE deleting patients
            if (!empty($patientIds)) {
                // Delete nutrition plans (no clinic_id, only patient_id)
                if (Schema::hasTable('nutrition_plans') && Schema::hasColumn('nutrition_plans', 'patient_id')) {
                    DB::table('nutrition_plans')->whereIn('patient_id', $patientIds)->delete();
                }
            }

            // Now delete clinic-level data

            // Delete prescriptions
            $clinic->prescriptions()->delete();

            // Delete appointments
            $clinic->appointments()->delete();

            // Delete medicines
            $clinic->medicines()->delete();

            // Delete lab tests
            if (method_exists($clinic, 'labTests')) {
                $clinic->labTests()->delete();
            }

            // Delete invoices
            if (method_exists($clinic, 'invoices')) {
                $clinic->invoices()->delete();
            }

            // Delete receipts
            if (Schema::hasTable('receipts') && Schema::hasColumn('receipts', 'clinic_id')) {
                DB::table('receipts')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete expenses
            if (method_exists($clinic, 'expenses')) {
                $clinic->expenses()->delete();
            }

            // Delete advertisements
            if (method_exists($clinic, 'advertisements')) {
                $clinic->advertisements()->delete();
            }

            // Delete dental charts and related data
            if (Schema::hasTable('dental_charts') && Schema::hasColumn('dental_charts', 'clinic_id')) {
                DB::table('dental_charts')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete dental treatments
            if (Schema::hasTable('dental_treatments') && Schema::hasColumn('dental_treatments', 'clinic_id')) {
                DB::table('dental_treatments')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete aesthetic sessions
            if (Schema::hasTable('aesthetic_sessions') && Schema::hasColumn('aesthetic_sessions', 'clinic_id')) {
                DB::table('aesthetic_sessions')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete custom checkups
            if (Schema::hasTable('checkups') && Schema::hasColumn('checkups', 'clinic_id')) {
                DB::table('checkups')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete patient images
            if (Schema::hasTable('patient_images') && Schema::hasColumn('patient_images', 'clinic_id')) {
                DB::table('patient_images')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete patient videos
            if (Schema::hasTable('patient_videos') && Schema::hasColumn('patient_videos', 'clinic_id')) {
                DB::table('patient_videos')->where('clinic_id', $clinic->id)->delete();
            }

            // Delete communication logs
            if (method_exists($clinic, 'communicationLogs')) {
                $clinic->communicationLogs()->delete();
            }

            // Delete audit logs
            if (method_exists($clinic, 'auditLogs')) {
                $clinic->auditLogs()->delete();
            }

            // Delete non-admin users
            $clinic->users()->where('role', '!=', 'admin')->delete();

            // Delete patients last (cascade deletes will handle many related records)
            $clinic->patients()->delete();

            // Reset storage usage
            $clinic->update(['storage_used' => 0]);

            DB::commit();

            \Log::info('Demo clinic reset', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'reset_by' => auth()->id(),
            ]);

            return back()->with('success', 'Demo clinic "' . $clinic->name . '" has been reset successfully. All user-generated data has been deleted.');

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Failed to reset demo clinic', [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return back()->withErrors(['error' => 'Failed to reset demo clinic: ' . $e->getMessage()]);
        }
    }

    private function applyAccessibleClinicScope($query)
    {
        $user = auth()->user();

        if (!$user || $user->hasGlobalClinicAccess()) {
            return $query;
        }

        $clinicIds = $user->accessibleClinicIds();

        return $clinicIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('clinics.id', $clinicIds);
    }

    private function authorizeAccessibleClinic(Clinic $clinic): void
    {
        $user = auth()->user();

        if (!$user || !$user->canAccessClinic($clinic->id)) {
            abort(403, 'You do not have access to this clinic.');
        }
    }

    private function authorizeGlobalRoot(): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Only the Master Admin can perform this action.');
        }
    }

    private function authorizeClinicCreationAccess(): void
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return;
        }

        if ($user?->isMasterAdmin() && $user->canCreateManagedClinic()) {
            return;
        }

        abort(403, 'You are not allowed to create additional clinics.');
    }

    private function authorizeClinicDeletion(Clinic $clinic): void
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return;
        }

        if ($user?->isMasterAdmin() && $user->ownsManagedClinic($clinic)) {
            return;
        }

        abort(403, 'You can only delete clinics that you created within your assigned quota.');
    }
}
