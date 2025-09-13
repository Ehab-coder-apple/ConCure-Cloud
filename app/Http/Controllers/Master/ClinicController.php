<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClinicController extends Controller
{
    /**
     * Display a listing of clinics.
     */
    public function index(Request $request)
    {
        $query = Clinic::with(['users' => function($q) {
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

        $clinics = $query->latest()->paginate(15);

        return view('master.clinics.index', compact('clinics'));
    }

    /**
     * Show the form for creating a new clinic.
     */
    public function create()
    {
        return view('master.clinics.create');
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
            'max_users' => 'required|integer|min:1|max:1000',
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            // Create clinic
            $clinic = Clinic::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'max_users' => $request->max_users,
                'is_active' => true,
                'activated_at' => now(),
            ]);

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
        return view('master.clinics.edit', compact('clinic'));
    }

    /**
     * Update the specified clinic.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('clinics')->ignore($clinic->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'max_users' => 'required|integer|min:1|max:1000',
        ]);

        $clinic->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'max_users' => $request->max_users,
        ]);

        return redirect()->route('master.clinics.show', $clinic)
            ->with('success', 'Clinic updated successfully.');
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
        // Check if clinic has any data
        $hasData = $clinic->patients()->exists() || 
                   $clinic->prescriptions()->exists() || 
                   $clinic->appointments()->exists();

        if ($hasData) {
            return back()->withErrors(['error' => 'Cannot delete clinic with existing data. Deactivate instead.']);
        }

        DB::beginTransaction();
        try {
            // Delete all users first
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
}
