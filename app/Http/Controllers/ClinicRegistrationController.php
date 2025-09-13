<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClinicRegistrationController extends Controller
{
    /**
     * Show the clinic registration form.
     */
    public function showRegistrationForm()
    {
        return view('clinic-registration.register');
    }

    /**
     * Handle clinic registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            // Clinic Information
            'clinic_name' => 'required|string|max:255',
            'clinic_email' => 'required|email|unique:clinics,email',
            'clinic_phone' => 'nullable|string|max:20',
            'clinic_address' => 'nullable|string|max:500',
            
            // Admin User Information
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            
            // Terms and Conditions
            'terms_accepted' => 'required|accepted',
            'privacy_accepted' => 'required|accepted',
        ], [
            'clinic_email.unique' => 'A clinic with this email address is already registered.',
            'admin_email.unique' => 'A user with this email address already exists.',
            'terms_accepted.required' => 'You must accept the Terms and Conditions.',
            'privacy_accepted.required' => 'You must accept the Privacy Policy.',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique activation code
            $activationCode = $this->generateUniqueActivationCode();

            // Create clinic (inactive by default)
            $clinic = Clinic::create([
                'name' => $request->clinic_name,
                'email' => $request->clinic_email,
                'phone' => $request->clinic_phone,
                'address' => $request->clinic_address,
                'max_users' => 10, // Default user limit
                'is_active' => false, // Requires approval
                'activation_code' => $activationCode,
                'settings' => json_encode([
                    'registration_date' => now()->toDateString(),
                    'registration_ip' => $request->ip(),
                    'requires_approval' => true,
                ]),
            ]);

            // Generate unique username for admin
            $adminUsername = $this->generateUniqueUsername($request->admin_first_name, $request->admin_last_name);

            // Create admin user (inactive until clinic is approved)
            $adminUser = User::create([
                'first_name' => $request->admin_first_name,
                'last_name' => $request->admin_last_name,
                'email' => $request->admin_email,
                'username' => $adminUsername,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'clinic_id' => $clinic->id,
                'is_active' => false, // Will be activated when clinic is approved
            ]);

            DB::commit();

            // Send confirmation email to admin
            $this->sendRegistrationConfirmation($clinic, $adminUser);

            // Send notification to super admins
            $this->notifySuperAdmins($clinic, $adminUser);

            return redirect()->route('clinic-registration.success')
                ->with('clinic_id', $clinic->id)
                ->with('success', 'Registration submitted successfully! Please check your email for confirmation.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show registration success page.
     */
    public function success()
    {
        if (!session('success')) {
            return redirect()->route('clinic-registration.form');
        }

        return view('clinic-registration.success');
    }

    /**
     * Show registration status page.
     */
    public function status(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'activation_code' => 'required|string',
        ]);

        $clinic = Clinic::where('email', $request->email)
            ->where('activation_code', $request->activation_code)
            ->first();

        if (!$clinic) {
            return back()->withErrors(['error' => 'Invalid email or activation code.']);
        }

        return view('clinic-registration.status', compact('clinic'));
    }

    /**
     * Show status check form.
     */
    public function showStatusForm()
    {
        return view('clinic-registration.check-status');
    }

    /**
     * Generate unique activation code.
     */
    private function generateUniqueActivationCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Clinic::where('activation_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique username.
     */
    private function generateUniqueUsername($firstName, $lastName)
    {
        $baseUsername = strtolower(str_replace(' ', '', $firstName . $lastName));
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Send registration confirmation email.
     */
    private function sendRegistrationConfirmation($clinic, $adminUser)
    {
        // TODO: Implement email sending
        // For now, we'll just log it
        \Log::info('Registration confirmation email should be sent to: ' . $adminUser->email, [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'activation_code' => $clinic->activation_code,
        ]);
    }

    /**
     * Notify super admins of new registration.
     */
    private function notifySuperAdmins($clinic, $adminUser)
    {
        $superAdmins = User::where('role', 'super_admin')->get();
        
        foreach ($superAdmins as $superAdmin) {
            // TODO: Implement email notification
            \Log::info('Super admin notification should be sent to: ' . $superAdmin->email, [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'admin_email' => $adminUser->email,
            ]);
        }
    }
}
