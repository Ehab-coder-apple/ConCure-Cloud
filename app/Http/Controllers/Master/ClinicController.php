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
        $specialities = $this->specialityOptions();
        $availableModules = \App\Models\Clinic::AVAILABLE_MODULES;
        $moduleGroups = \App\Models\Clinic::MODULE_GROUPS;
        $countries = \App\Models\Country::where('is_active', true)->orderBy('name')->get();
        return view('master.clinics.create', compact('specialities', 'availableModules', 'moduleGroups', 'countries'));
    }

    /**
     * Store a newly created clinic.
     */
    public function store(Request $request)
    {
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
            // Admin
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
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

            // Module access control
            if (Schema::hasColumn('clinics', 'enabled_modules')) {
                $clinicData['enabled_modules'] = $request->input('enabled_modules', null);
            }

            $clinic = Clinic::create($clinicData);

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

            DB::commit();

            return redirect()->route('master.clinics.index')
                ->with('success', 'Clinic created successfully with admin user.');

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

            // Storage limit
            if (Schema::hasColumn('clinics', 'storage_limit') && $request->filled('storage_limit_gb')) {
                $updateData['storage_limit'] = (int) ($request->storage_limit_gb * 1024 * 1024 * 1024);
            }

            // Module access control
            if (Schema::hasColumn('clinics', 'enabled_modules')) {
                // If no checkboxes checked, store null (= all modules enabled by default)
                $updateData['enabled_modules'] = $request->input('enabled_modules', null);
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
        // Check if clinic has any data that would prevent deletion
        $hasData = $clinic->patients()->exists() ||
                   $clinic->prescriptions()->exists() ||
                   $clinic->appointments()->exists() ||
                   $clinic->medicines()->exists() ||
                   $clinic->labTests()->exists() ||
                   $clinic->invoices()->exists() ||
                   $clinic->expenses()->exists() ||
                   $clinic->advertisements()->exists();

        if ($hasData) {
            return back()->withErrors(['error' => 'Cannot delete clinic with existing data (patients, prescriptions, appointments, medicines, lab tests, invoices, expenses, or advertisements). Deactivate the clinic instead.']);
        }

        DB::beginTransaction();
        try {
            // Delete related data that can be safely removed
            $clinic->auditLogs()->delete();
            $clinic->activationCodes()->delete();
            $clinic->clinicSettings()->delete();
            $clinic->communicationLogs()->delete();

            // Delete all users
            $clinic->users()->delete();

            // Delete the clinic
            $clinic->delete();

            DB::commit();

            return redirect()->route('master.clinics.index')
                ->with('success', 'Clinic deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete clinic: ' . $e->getMessage()]);
        }
    }

    /**
     * Configure WhatsApp Meta Cloud API for a clinic.
     */
    public function configureWhatsApp(Request $request, Clinic $clinic)
    {
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
}
