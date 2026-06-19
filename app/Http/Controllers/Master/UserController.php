<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Traits\SmartSearch;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use SmartSearch;
    /**
     * Display a listing of master users.
     */
    public function index(Request $request)
    {
        $this->authorizeGlobalRoot();

        $query = User::where('role', 'master_admin')
            ->with(['superAdminClinics:id,name,city', 'createdBy:id,first_name,last_name']);

        // Apply smart search filter
        $searchTerm = $this->getValidatedSearchTerm($request);
        if ($searchTerm !== null) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('username', 'like', "%{$searchTerm}%");
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

        $users = $query->latest()->paginate(20);

        return view('master.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new master user.
     */
    public function create()
    {
        $this->authorizeGlobalRoot();

        $availableRoles = ['master_admin'];
        $masterPermissions = User::MASTER_PERMISSIONS;
        $clinics = Clinic::orderBy('name')->get(['id', 'name', 'city', 'is_active']);

        return view('master.users.create', compact('availableRoles', 'masterPermissions', 'clinics'));
    }

    /**
     * Store a newly created master user in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeGlobalRoot();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:100',
            'scientific_degree' => 'nullable|string|max:100',
            'educational_institution' => 'nullable|string|max:255',
            'role' => 'required|in:master_admin',
            'is_active' => 'boolean',
            'language' => 'required|in:en,ar,ku',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(User::MASTER_PERMISSIONS)),
            'clinic_ids' => 'nullable|array',
            'clinic_ids.*' => 'integer|exists:clinics,id',
            'managed_clinic_limit' => 'nullable|integer|min:0|max:1000',
        ]);

        $clinicIds = $this->sanitizeClinicIds($request->input('clinic_ids', []));
        $managedClinicLimit = max(0, (int) $request->input('managed_clinic_limit', 0));

        if ($clinicIds === [] && $managedClinicLimit === 0) {
            throw ValidationException::withMessages([
                'clinic_ids' => 'Assign at least one clinic or allow the Super Admin to create at least one clinic.',
            ]);
        }

        DB::transaction(function () use ($request, $clinicIds, $managedClinicLimit) {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'title_prefix' => $request->title_prefix,
                'scientific_degree' => $request->scientific_degree,
                'educational_institution' => $request->educational_institution,
                'role' => $request->role,
                'is_active' => $request->boolean('is_active', true),
                'activated_at' => now(),
                'language' => $request->language,
                'permissions' => $request->input('permissions', []),
                'metadata' => $this->buildManagedClinicMetadata([], $managedClinicLimit),
                'clinic_id' => null,
                'created_by' => auth()->id(),
            ]);

            $user->superAdminClinics()->sync($clinicIds);
        });

        return redirect()->route('master.users.index')
            ->with('success', 'Super Admin created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorizeGlobalRoot();
        $this->authorizeManagedUser($user);

        $user->load(['clinic', 'createdBy', 'superAdminClinics' => function ($query) {
            $query->orderBy('name');
        }]);

        $clinicIds = $user->superAdminClinics->pluck('id')->all();
        
        $stats = [
            'allocated_clinics' => count($clinicIds),
            'created_clinics' => $user->createdManagedClinicsCount(),
            'managed_clinic_limit' => $user->getManagedClinicCreationLimit(),
            'remaining_creation_slots' => $user->remainingManagedClinicCreationSlots(),
            'clinic_users' => $clinicIds === [] ? 0 : User::whereIn('clinic_id', $clinicIds)->count(),
            'active_clinic_users' => $clinicIds === [] ? 0 : User::whereIn('clinic_id', $clinicIds)->where('is_active', true)->count(),
            'clinic_patients' => $clinicIds === [] ? 0 : Patient::withoutGlobalScopes()->whereIn('clinic_id', $clinicIds)->count(),
            'last_login' => $user->last_login_at,
            'account_age' => $user->created_at->diffForHumans(),
        ];

        return view('master.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified master user.
     */
    public function edit(User $user)
    {
        $this->authorizeGlobalRoot();
        $this->authorizeManagedUser($user);

        $availableRoles = ['master_admin'];
        $masterPermissions = User::MASTER_PERMISSIONS;
        $clinics = Clinic::orderBy('name')->get(['id', 'name', 'city', 'is_active']);
        $user->load('superAdminClinics:id,name');

        return view('master.users.edit', compact('user', 'availableRoles', 'masterPermissions', 'clinics'));
    }

    /**
     * Update the specified master user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeGlobalRoot();
        $this->authorizeManagedUser($user);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:100',
            'scientific_degree' => 'nullable|string|max:100',
            'educational_institution' => 'nullable|string|max:255',
            'role' => 'required|in:master_admin',
            'is_active' => 'boolean',
            'language' => 'required|in:en,ar,ku',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(User::MASTER_PERMISSIONS)),
            'clinic_ids' => 'nullable|array',
            'clinic_ids.*' => 'integer|exists:clinics,id',
            'managed_clinic_limit' => 'nullable|integer|min:0|max:1000',
        ]);

        $clinicIds = $this->sanitizeClinicIds($request->input('clinic_ids', []));
        $managedClinicLimit = max(0, (int) $request->input('managed_clinic_limit', 0));

        if ($clinicIds === [] && $managedClinicLimit === 0) {
            throw ValidationException::withMessages([
                'clinic_ids' => 'Assign at least one clinic or allow the Super Admin to create at least one clinic.',
            ]);
        }

        $updateData = [
            'username' => $request->username,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'title_prefix' => $request->title_prefix,
            'scientific_degree' => $request->scientific_degree,
            'educational_institution' => $request->educational_institution,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'language' => $request->language,
            'permissions' => $request->input('permissions', []),
            'metadata' => $this->buildManagedClinicMetadata($user->metadata, $managedClinicLimit),
            'clinic_id' => null, // Master users don't belong to any clinic
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        DB::transaction(function () use ($user, $updateData, $clinicIds) {
            $user->update($updateData);
            $user->superAdminClinics()->sync($clinicIds);
        });

        return redirect()->route('master.users.show', $user)
            ->with('success', 'Super Admin updated successfully.');
    }

    /**
     * Activate a user.
     */
    public function activate(User $user)
    {
        $this->authorizeGlobalRoot();
        $this->authorizeManagedUser($user);

        $user->update([
            'is_active' => true,
            'activated_at' => now(),
        ]);

        return back()->with('success', 'User activated successfully.');
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(User $user)
    {
        $this->authorizeGlobalRoot();
        $this->authorizeManagedUser($user);

        // Prevent deactivating the last admin of a clinic
        if ($user->role === 'admin') {
            $activeAdmins = User::where('clinic_id', $user->clinic_id)
                ->where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($activeAdmins === 0) {
                return back()->withErrors(['error' => 'Cannot deactivate the last active admin of a clinic.']);
            }
        }

        $user->update([
            'is_active' => false,
        ]);

        return back()->with('success', 'User deactivated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorizeGlobalRoot();
        $this->authorizeManagedUser($user);

        // Prevent deleting the last admin of a clinic
        if ($user->role === 'admin') {
            $activeAdmins = User::where('clinic_id', $user->clinic_id)
                ->where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($activeAdmins === 0) {
                return back()->withErrors(['error' => 'Cannot delete the last admin of a clinic.']);
            }
        }

        // Check if user has created any critical data
        $hasData = false;
        if ($user->clinic) {
            // Check if user created patients, prescriptions, etc.
            $hasData = $user->clinic->patients()->where('created_by', $user->id)->exists() ||
                      $user->clinic->prescriptions()->where('created_by', $user->id)->exists();
        }

        if ($hasData) {
            return back()->withErrors(['error' => 'Cannot delete user with existing data. Deactivate instead.']);
        }

        DB::transaction(function () use ($user) {
            $user->superAdminClinics()->detach();
            $user->delete();
        });

        return redirect()->route('master.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Get user statistics for charts.
     */
    public function getUserStats()
    {
        $this->authorizeGlobalRoot();

        $roleStats = User::where('role', '!=', 'super_admin')
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        $statusStats = [
            'active' => User::where('role', '!=', 'super_admin')->where('is_active', true)->count(),
            'inactive' => User::where('role', '!=', 'super_admin')->where('is_active', false)->count(),
        ];

        return response()->json([
            'roles' => $roleStats,
            'status' => $statusStats,
        ]);
    }

    /**
     * Get users by clinic for charts.
     */
    public function getUsersByClinic()
    {
        $this->authorizeGlobalRoot();

        $clinicStats = User::where('role', '!=', 'super_admin')
            ->join('clinics', 'users.clinic_id', '=', 'clinics.id')
            ->selectRaw('clinics.name, count(*) as count')
            ->groupBy('clinics.id', 'clinics.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'name')
            ->toArray();

        return response()->json($clinicStats);
    }

    private function authorizeGlobalRoot(): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Only the Master Admin can manage Super Admin accounts.');
        }
    }

    private function authorizeManagedUser(User $user): void
    {
        if ($user->isSuperAdmin() || !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Only scoped Super Admin accounts can be managed here.');
        }
    }

    private function sanitizeClinicIds(array $clinicIds): array
    {
        return collect($clinicIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function buildManagedClinicMetadata(?array $metadata, int $managedClinicLimit): array
    {
        $metadata = is_array($metadata) ? $metadata : [];
        $metadata[User::METADATA_MANAGED_CLINIC_LIMIT] = $managedClinicLimit;

        return $metadata;
    }
}
