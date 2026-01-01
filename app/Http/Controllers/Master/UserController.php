<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Traits\SmartSearch;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;

class UserController extends Controller
{
    use SmartSearch;
    /**
     * Display a listing of master users.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'master_admin');

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
        $availableRoles = ['master_admin'];
        $masterPermissions = User::MASTER_PERMISSIONS;

        return view('master.users.create', compact('availableRoles', 'masterPermissions'));
    }

    /**
     * Store a newly created master user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:100',
            'role' => 'required|in:master_admin',
            'is_active' => 'boolean',
            'language' => 'required|in:en,ar,ku',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(User::MASTER_PERMISSIONS)),
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'title_prefix' => $request->title_prefix,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'activated_at' => now(),
            'language' => $request->language,
            'permissions' => $request->input('permissions', []),
            'clinic_id' => null, // Master users don't belong to any clinic
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('master.users.index')
            ->with('success', 'Master user created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Prevent viewing super admin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $user->load(['clinic', 'createdBy']);
        
        // Get user activity stats
        $stats = [
            'patients_created' => $user->clinic ? $user->clinic->patients()->count() : 0,
            'prescriptions_created' => $user->clinic ? $user->clinic->prescriptions()->count() : 0,
            'appointments_created' => $user->clinic ? $user->clinic->appointments()->count() : 0,
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
        // Prevent editing super admin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        // Only allow editing master users
        if (!$user->isMasterAdmin()) {
            abort(403, 'Access denied. Only master users can be edited here.');
        }

        $availableRoles = ['master_admin'];
        $masterPermissions = User::MASTER_PERMISSIONS;

        return view('master.users.edit', compact('user', 'availableRoles', 'masterPermissions'));
    }

    /**
     * Update the specified master user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent updating super admin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        // Only allow updating master users
        if (!$user->isMasterAdmin()) {
            abort(403, 'Access denied. Only master users can be updated here.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:100',
            'role' => 'required|in:master_admin',
            'is_active' => 'boolean',
            'language' => 'required|in:en,ar,ku',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(User::MASTER_PERMISSIONS)),
        ]);

        $updateData = [
            'username' => $request->username,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'title_prefix' => $request->title_prefix,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'language' => $request->language,
            'permissions' => $request->input('permissions', []),
            'clinic_id' => null, // Master users don't belong to any clinic
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return redirect()->route('master.users.show', $user)
            ->with('success', 'Master user updated successfully.');
    }

    /**
     * Activate a user.
     */
    public function activate(User $user)
    {
        // Prevent modifying super admin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

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
        // Prevent modifying super admin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

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
        // Prevent deleting super admin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

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

        $user->delete();

        return redirect()->route('master.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Get user statistics for charts.
     */
    public function getUserStats()
    {
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
}
