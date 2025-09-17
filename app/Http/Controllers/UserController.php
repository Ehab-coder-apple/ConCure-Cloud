<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\ActivationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Only clinic admins or super admins can access the full Users management
        if (!(method_exists($user, 'isClinicAdmin') && $user->isClinicAdmin()) && !(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            abort(403, 'Unauthorized: user management is restricted to admins.');
        }

        $query = User::with('clinic', 'creator');

        // Filter by clinic for non-program-owner users
        // All clinic users are restricted to their clinic
        $query->where('clinic_id', $user->clinic_id);

        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'activated') {
                $query->whereNotNull('activated_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('activated_at');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $user = auth()->user();

        // Only clinic admins or super admins can create users
        if (!(method_exists($user, 'isClinicAdmin') && $user->isClinicAdmin()) && !(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            abort(403, 'Unauthorized: creating users is restricted to admins.');
        }

        // Check user limit
        $userLimitInfo = null;
        if ($user->clinic) {
            $userLimitInfo = $user->clinic->getUserLimitInfo();
            if ($userLimitInfo['has_reached_limit']) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot create new user. Your clinic has reached its user limit. Please contact your administrator.');
            }
        }

        // Determine available roles based on current user and whether this is a subuser creation
        $availableRoles = $this->getAvailableRoles($user);
        $assignToId = request()->integer('assign_to');
        if ($assignToId && $user->role === 'doctor' && $assignToId === $user->id) {
            // Doctors creating a subuser for themselves may only create assistants
            $availableRoles = ['assistant'];
        }

        // Clinic users only see their own clinic
        $clinics = collect([$user->clinic]);

        return view('users.create', compact('availableRoles', 'clinics', 'userLimitInfo'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Only clinic admins or super admins can store users
        if (!(method_exists($user, 'isClinicAdmin') && $user->isClinicAdmin()) && !(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            abort(403, 'Unauthorized: creating users is restricted to admins.');
        }

        // Compute available roles for this request
        $availableRolesForRequest = $this->getAvailableRoles($user);
        if ($request->filled('assign_to_user_id') && $user->role === 'doctor' &&
            (int) $request->assign_to_user_id === (int) $user->id) {
            // Doctors creating subusers for themselves may only create assistants
            $availableRolesForRequest = ['assistant'];
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:100',
            'role' => ['required', Rule::in($availableRolesForRequest)],
            'clinic_id' => 'nullable',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'language' => 'required|in:en,ar,ku',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $clinicId = $user->clinic_id;

        // Check clinic user limit
        if ($clinicId) {
            $clinic = Clinic::find($clinicId);
            if ($clinic && $clinic->hasReachedUserLimit()) {
                $userLimitInfo = $clinic->getUserLimitInfo();
                return back()->withErrors([
                    'clinic_id' => "Cannot create user. Your clinic has reached its user limit ({$userLimitInfo['current_users']}/{$userLimitInfo['max_users']} users). Please contact your administrator to increase the user limit."
                ]);
            }
        }

        $newUser = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'title_prefix' => $request->title_prefix,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'activated_at' => now(), // Direct creation means immediate activation
            'language' => $request->language,
            'permissions' => $request->input('permissions', []),
            'clinic_id' => $clinicId,
            'created_by' => $user->id,
        ]);

        // If creating a subuser for a specific user (e.g., doctor), auto-assign as assistant
        if ($request->filled('assign_to_user_id')) {
            $assignTo = User::where('id', $request->integer('assign_to_user_id'))
                ->where('clinic_id', $user->clinic_id)
                ->first();

            if ($assignTo && $newUser->role === 'assistant') {
                $assignTo->assistants()->syncWithoutDetaching([$newUser->id]);
                $assignTo->assistants()->updateExistingPivot($newUser->id, ['clinic_id' => $user->clinic_id]);
            }
        }


        if ($request->filled('assign_to_user_id') && isset($assignTo) && $assignTo) {
            return redirect()->route('users.show', $assignTo->id)
                             ->with('success', __('Subuser created and assigned successfully.'));
        }

        return redirect()->route('users.index')
                        ->with('success', __('User created successfully.'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorizeUserAccess($user);

        $user->load('clinic', 'creator', 'createdUsers', 'auditLogs');

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorizeUserAccess($user);

        $currentUser = auth()->user();
        if (!(method_exists($currentUser, 'isClinicAdmin') && $currentUser->isClinicAdmin()) && !(method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized: editing users is restricted to admins.');
        }
        $availableRoles = $this->getAvailableRoles($currentUser);

        $clinics = collect([$currentUser->clinic]);

        // For doctor accounts, prepare assistants management data
        $assistants = collect();
        $availableAssistants = collect();
        if ($user->role === 'doctor') {
            $user->loadMissing('assistants');
            $assistants = $user->assistants()->orderBy('first_name')->orderBy('last_name')->get();
            $availableAssistants = User::byClinic($currentUser->clinic_id)
                ->byRole('assistant')
                ->whereNotIn('id', $assistants->pluck('id'))
                ->orderBy('first_name')->orderBy('last_name')
                ->get();
        }

        return view('users.edit', compact('user', 'availableRoles', 'clinics', 'assistants', 'availableAssistants'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeUserAccess($user);

        $currentUser = auth()->user();
        if (!(method_exists($currentUser, 'isClinicAdmin') && $currentUser->isClinicAdmin()) && !(method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized: updating users is restricted to admins.');
        }

        // Compute available roles for this request/user
        $availableRolesForRequest = $this->getAvailableRoles($currentUser);
        if ($currentUser->role === 'doctor') {
            $availableRolesForRequest = ['assistant'];
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:100',
            'role' => ['required', Rule::in($availableRolesForRequest)],
            'clinic_id' => 'nullable',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'language' => 'required|in:en,ar,ku',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
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
        ];

        // Clinic users cannot change clinic assignment

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
                        ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorizeUserAccess($user);

        $currentUser = auth()->user();
        if (!(method_exists($currentUser, 'isClinicAdmin') && $currentUser->isClinicAdmin()) && !(method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized: deleting users is restricted to admins.');
        }

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'Cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'User deleted successfully.');
    }

    /**
     * Show activation codes management.
     */
    public function activationCodes(Request $request)
    {
        $user = auth()->user();

        $query = ActivationCode::with('clinic', 'usedByUser', 'creator');

        // All clinic users are restricted to their clinic
        $query->where('clinic_id', $user->clinic_id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'unused') {
                $query->unused();
            } elseif ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        $codes = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('users.activation-codes', compact('codes'));
    }

    /**
     * Generate a new activation code.
     */
    public function generateActivationCode(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'type' => 'required|in:clinic,user',
            'clinic_id' => 'required_if:type,user|exists:clinics,id',
            'role' => 'required_if:type,user|in:admin,doctor,assistant,nurse,accountant,patient',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($request->type === 'clinic') {
            $code = ActivationCode::createClinicCode($user, $request->notes);
        } else {
            $clinic = Clinic::find($request->clinic_id);
            $code = ActivationCode::createUserCode($user, $clinic, $request->role, $request->notes);
        }

        return back()->with('success', "Activation code generated: {$code->code}");
    }

    /**
     * Get available roles based on current user's role.
     */
    private function getAvailableRoles(User $user): array
    {
        // Super admin can manage all roles
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return ['admin', 'doctor', 'nutritionist', 'assistant', 'nurse', 'accountant', 'patient'];
        }

        // Clinic admin can manage clinic roles (including creating other admins)
        if (method_exists($user, 'isClinicAdmin') && $user->isClinicAdmin()) {
            return ['admin', 'doctor', 'nutritionist', 'assistant', 'nurse', 'accountant', 'patient'];
        }

        // Doctors can only manage assistants
        if ($user->role === 'doctor') {
            return ['assistant'];
        }

        return [];
    }

    /**
     * Authorize access to user management.
     */
    private function authorizeUserAccess(User $user): void
    {
        $currentUser = auth()->user();

        // Check if user has permission to access users
        if (!$currentUser->hasPermission('users_view') && !$currentUser->hasPermission('users_edit')) {
            abort(403, 'You do not have permission to access user management.');
        }

        // Admins can access users across all clinics in their clinic
        // Other users can only access users in their clinic
        if ($user->clinic_id !== $currentUser->clinic_id) {
            abort(403, 'Unauthorized access to user from different clinic.');
        }
    }

    /**
     * Delete activation code.
     */
    public function deleteActivationCode($codeId)
    {
        try {
            $user = auth()->user();

            // Find the activation code and ensure it belongs to the user's clinic
            $activationCode = ActivationCode::where('id', $codeId)
                ->where('clinic_id', $user->clinic_id)
                ->first();

            if (!$activationCode) {
                return response()->json([
                    'success' => false,
                    'message' => __('Activation code not found.')
                ], 404);
            }

            // Check if the activation code has been used
            if ($activationCode->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete a used activation code.')
                ], 400);
            }

            // Delete the activation code
            $activationCode->delete();

            return response()->json([
                'success' => true,
                'message' => __('Activation code deleted successfully.')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error deleting activation code: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extend activation code.
     */
    public function extendActivationCode($codeId)
    {
        try {
            $user = auth()->user();

            // Find the activation code and ensure it belongs to the user's clinic
            $activationCode = ActivationCode::where('id', $codeId)
                ->where('clinic_id', $user->clinic_id)
                ->first();

            if (!$activationCode) {
                return response()->json([
                    'success' => false,
                    'message' => __('Activation code not found.')
                ], 404);
            }

            // Check if the activation code has been used
            if ($activationCode->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot extend a used activation code.')
                ], 400);
            }

            // Extend the expiry date by 30 days
            $newExpiryDate = $activationCode->expires_at->addDays(30);
            $activationCode->update(['expires_at' => $newExpiryDate]);

            return response()->json([
                'success' => true,
                'message' => __('Activation code extended successfully. New expiry date: ') . $newExpiryDate->format('M d, Y'),
                'new_expiry' => $newExpiryDate->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error extending activation code: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign an assistant to a doctor.
     */
    public function attachAssistant(Request $request, User $user)
    {
        $this->authorizeUserAccess($user);

        // Only admins or the doctor themselves can manage assignments
        $currentUser = auth()->user();
        $isSelfDoctor = $currentUser->id === $user->id && $currentUser->role === 'doctor';
        if (!$currentUser->isClinicAdmin() && !$isSelfDoctor) {
            abort(403, 'Only clinic admin or the doctor can manage assistants.');
        }

        $data = $request->validate([
            'assistant_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // Validate assistant belongs to same clinic and has assistant role
        $assistant = User::where('id', $data['assistant_id'])
            ->where('clinic_id', $currentUser->clinic_id)
            ->where('role', 'assistant')
            ->first();

        if (!$assistant) {
            return back()->withErrors(['assistant_id' => __('Selected assistant is invalid or from another clinic.')]);
        }

        // Attach without detaching others
        $user->assistants()->syncWithoutDetaching([$assistant->id]);

        // Optionally store clinic_id on pivot for auditing
        $user->assistants()->updateExistingPivot($assistant->id, ['clinic_id' => $currentUser->clinic_id]);

        return back()->with('success', __('Assistant assigned successfully.'));
    }

    /**
     * Remove an assistant from a doctor.
     */
    public function detachAssistant(User $user, $assistantId)
    {
        $this->authorizeUserAccess($user);

        $currentUser = auth()->user();
        $isSelfDoctor = $currentUser->id === $user->id && $currentUser->role === 'doctor';
        if (!$currentUser->isClinicAdmin() && !$isSelfDoctor) {
            abort(403, 'Only clinic admin or the doctor can manage assistants.');
        }

        $assistant = User::where('id', $assistantId)
            ->where('clinic_id', $currentUser->clinic_id)
            ->where('role', 'assistant')
            ->first();

        if ($assistant) {
            $user->assistants()->detach($assistant->id);
        }

        return back()->with('success', __('Assistant removed successfully.'));
    }
}

