<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page.
     */
    public function index()
    {
        return view('welcome.index');
    }

    /**
     * Show the tenant registration form.
     */
    public function register()
    {
        return view('welcome.register');
    }

    /**
     * Store a new tenant registration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_address' => 'required|string|max:500',
            'clinic_phone' => 'required|string|max:20',
            'clinic_email' => 'required|email|max:255|unique:clinics,email',
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        try {
            DB::beginTransaction();

            // Create the clinic
            $clinic = Clinic::create([
                'name' => $request->clinic_name,
                'address' => $request->clinic_address,
                'phone' => $request->clinic_phone,
                'email' => $request->clinic_email,
                'activation_code' => Str::upper(Str::random(10)),
                'activated_at' => now(), // Automatically activate
                'is_active' => true,
                // Subscription system removed - direct activation
            ]);

            // Create the admin user
            $admin = User::create([
                'username' => $request->admin_email, // Use email as username
                'first_name' => $request->admin_first_name,
                'last_name' => $request->admin_last_name,
                'email' => $request->admin_email,
                'phone' => $request->admin_phone,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'clinic_id' => $clinic->id,
                'is_active' => true,
                'permissions' => [
                    'users_view', 'users_create', 'users_edit', 'users_delete',
                    'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
                    'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_print',
                    'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
                    'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
                    'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
                    'settings_view', 'settings_edit',
                ],
            ]);

            DB::commit();

            // Log in the new admin user
            auth()->login($admin);

            return redirect()->route('dashboard')
                           ->with('success', 'Welcome to ConCure! Your clinic has been successfully registered and activated.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the actual error for debugging
            \Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return back()->withErrors(['error' => 'Registration failed. Please try again.'])
                        ->withInput();
        }
    }

    /**
     * Show the tenant login form.
     */
    public function login()
    {
        return view('welcome.login');
    }

    /**
     * Handle tenant login.
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

            // Check if user and clinic exist and are active
            if (!$user) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Authentication failed. Please try again.',
                ]);
            }

            // Check if clinic exists and is active (only for non-program_owner users)
            if ($user->role !== 'program_owner' && (!$user->clinic || !$user->clinic->is_active)) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Your clinic account has been suspended. Please contact support.',
                ]);
            }

            // Subscription checks removed - direct access

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome.index');
    }
}
