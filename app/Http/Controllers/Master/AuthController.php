<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the master login form.
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated as super admin
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return redirect()->route('master.dashboard');
        }

        return view('master.auth.login');
    }

    /**
     * Handle master login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Find user by email
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Check if user is super admin
        if (!$user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['Access denied. Super admin privileges required.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Update last login
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('master.dashboard'));
    }

    /**
     * Handle master logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('master.login');
    }

    /**
     * Show the master registration form (for initial setup).
     */
    public function showRegistrationForm()
    {
        // Only allow registration if no super admin exists
        if (User::where('role', 'super_admin')->exists()) {
            abort(403, 'Super admin already exists. Contact existing super admin for access.');
        }

        return view('master.auth.register');
    }

    /**
     * Handle master registration (for initial setup).
     */
    public function register(Request $request)
    {
        // Only allow registration if no super admin exists
        if (User::where('role', 'super_admin')->exists()) {
            abort(403, 'Super admin already exists.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        // Create the first super admin
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'super_admin',
            'is_active' => true,
            'activated_at' => now(),
            'clinic_id' => null, // Super admin doesn't belong to any clinic
        ]);

        // Log the user in
        Auth::login($user);

        return redirect()->route('master.dashboard')->with('success', 'Super admin account created successfully!');
    }
}
