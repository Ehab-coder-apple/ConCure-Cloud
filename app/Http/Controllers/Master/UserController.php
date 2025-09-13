<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('clinic')->where('role', '!=', 'super_admin');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Clinic filter
        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
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
        $clinics = Clinic::orderBy('name')->get();
        $roles = User::ROLES;
        unset($roles['super_admin']); // Remove super_admin from filter options

        return view('master.users.index', compact('users', 'clinics', 'roles'));
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
