<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MasterAuthController extends Controller
{
    /**
     * Show the master login form.
     */
    public function showLoginForm()
    {
        return view('master.auth.login');
    }

    /**
     * Handle master login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check if user exists and is a program owner
        $user = DB::table('users')
            ->where('username', $request->username)
            ->where('role', 'program_owner')
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => [__('Invalid credentials or insufficient permissions.')],
            ]);
        }

        // Update last login
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'last_login_at' => now(),
                'updated_at' => now(),
            ]);

        // Log the master login
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'user_role' => $user->role,
            'clinic_id' => $user->clinic_id,
            'action' => 'master_login',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Master dashboard login',
            'old_values' => null,
            'new_values' => json_encode([
                'login_time' => now(),
                'session_id' => session()->getId(),
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create master session
        session([
            'master_user_id' => $user->id,
            'master_user_name' => $user->first_name . ' ' . $user->last_name,
            'master_user_email' => $user->email,
            'master_login_time' => now(),
        ]);

        return redirect()->intended(route('master.dashboard'))
            ->with('success', __('Welcome to ConCure Master Dashboard'));
    }

    /**
     * Handle master logout request.
     */
    public function logout(Request $request)
    {
        $userId = session('master_user_id');

        if ($userId) {
            $user = DB::table('users')->where('id', $userId)->first();

            // Log the master logout
            DB::table('audit_logs')->insert([
                'user_id' => $userId,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                'user_role' => $user ? $user->role : 'unknown',
                'clinic_id' => $user ? $user->clinic_id : null,
                'action' => 'master_logout',
                'model_type' => 'User',
                'model_id' => $userId,
                'description' => 'Master dashboard logout',
                'old_values' => null,
                'new_values' => json_encode([
                    'logout_time' => now(),
                    'session_duration' => session('master_login_time') ? now()->diffInMinutes(session('master_login_time')) : 0,
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Clear master session
        session()->forget([
            'master_user_id',
            'master_user_name',
            'master_user_email',
            'master_login_time',
        ]);

        return redirect()->route('master.login')
            ->with('success', __('You have been logged out successfully.'));
    }

    /**
     * Check if user is authenticated as master.
     */
    public static function check()
    {
        return session()->has('master_user_id');
    }

    /**
     * Get the authenticated master user.
     */
    public static function user()
    {
        $userId = session('master_user_id');
        
        if (!$userId) {
            return null;
        }

        return DB::table('users')
            ->where('id', $userId)
            ->where('role', 'program_owner')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get master user ID.
     */
    public static function id()
    {
        return session('master_user_id');
    }
}
