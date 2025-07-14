<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterWelcomeController extends Controller
{
    /**
     * Show the master welcome page.
     */
    public function index()
    {
        return view('master.welcome.index');
    }

    /**
     * Show the program owner registration form.
     */
    public function register()
    {
        return view('master.welcome.register');
    }

    /**
     * Store a new program owner registration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
            'owner_first_name' => 'required|string|max:255',
            'owner_last_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username|alpha_dash',
            'owner_phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        try {
            DB::beginTransaction();

            // Create the program owner user
            $programOwner = User::create([
                'username' => $request->username,
                'first_name' => $request->owner_first_name,
                'last_name' => $request->owner_last_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'password' => Hash::make($request->password),
                'role' => 'program_owner',
                'clinic_id' => null, // Program owners don't belong to a clinic
                'is_active' => true,
                'activated_at' => now(), // Auto-activate program owners
                'permissions' => [
                    'master_dashboard_access',
                    'clinics_view', 'clinics_create', 'clinics_edit', 'clinics_delete',
                    'platform_users_view', 'platform_users_create', 'platform_users_edit', 'platform_users_delete',
                    'analytics_view', 'analytics_export',
                    'audit_logs_view', 'audit_logs_export',
                    'system_settings_view', 'system_settings_edit',
                    'activation_codes_view', 'activation_codes_create', 'activation_codes_delete',
                    'backups_view', 'backups_create', 'backups_download',
                    'system_health_view',
                ],
                'metadata' => [
                    'company_name' => $request->company_name,
                    'company_website' => $request->company_website,
                    'company_phone' => $request->company_phone,
                    'company_address' => $request->company_address,
                    'registration_date' => now()->toDateString(),
                    'account_type' => 'program_owner',
                ],
            ]);

            DB::commit();

            // Log in the new program owner
            auth()->login($programOwner);

            return redirect()->route('master.dashboard')
                           ->with('success', 'Welcome to ConCure Master Control! Your program owner account has been successfully created.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the actual error for debugging
            \Log::error('Program Owner Registration Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Show the master login form.
     */
    public function login()
    {
        return view('master.welcome.login');
    }

    /**
     * Handle master authentication.
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Check if user has master access
            if (!in_array($user->role, ['program_owner', 'platform_admin', 'support_agent'])) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'You do not have access to the master control panel.',
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact support.',
                ]);
            }

            return redirect()->intended(route('master.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle master logout.
     */
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('master.welcome.index');
    }

    /**
     * Show team member invitation form.
     */
    public function inviteTeam()
    {
        // Only program owners can invite team members
        if (auth()->user()->role !== 'program_owner') {
            abort(403, 'Only program owners can invite team members.');
        }

        return view('master.welcome.invite-team');
    }

    /**
     * Send team member invitation.
     */
    public function sendInvitation(Request $request)
    {
        // Only program owners can invite team members
        if (auth()->user()->role !== 'program_owner') {
            abort(403, 'Only program owners can invite team members.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:platform_admin,support_agent',
            'permissions' => 'required|array',
        ]);

        try {
            // Generate a temporary password
            $temporaryPassword = Str::random(12);

            // Create the team member
            $teamMember = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($temporaryPassword),
                'role' => $request->role,
                'clinic_id' => null,
                'is_active' => true,
                'permissions' => $request->permissions,
                'metadata' => [
                    'invited_by' => auth()->user()->id,
                    'invitation_date' => now()->toDateString(),
                    'account_type' => 'team_member',
                    'temporary_password' => true,
                ],
            ]);

            // In a real application, you would send an email with the temporary password
            // For now, we'll just show it in the success message

            return back()->with('success', "Team member invited successfully! Temporary password: {$temporaryPassword}");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to invite team member. Please try again.'])
                        ->withInput();
        }
    }
}
