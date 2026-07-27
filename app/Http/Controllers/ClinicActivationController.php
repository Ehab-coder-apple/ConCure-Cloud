<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClinicActivationController extends Controller
{
    /**
     * Show the clinic activation form.
     */
    public function showActivationForm()
    {
        return view('auth.activate-clinic');
    }

    /**
     * Process clinic activation.
     */
    public function activate(Request $request)
    {
        try {
            $request->validate([
                'activation_code' => 'required|string|size:15', // CLINIC-XXXXXXXX format
                'admin_username' => 'required|string|unique:users,username|min:3|max:50',
                'admin_password' => 'required|string|min:8|confirmed',
                'clinic_phone' => 'nullable|string|max:20',
                'clinic_address' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('error', __('Please check the form and try again.'));
        }

        try {
            DB::beginTransaction();

            // Find and validate activation code
            $activationRecord = DB::table('activation_codes')
                ->where('code', $request->activation_code)
                ->where('type', 'clinic')
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$activationRecord) {
                return back()->withErrors([
                    'activation_code' => __('Invalid or expired activation code.')
                ])->withInput();
            }

            // Get clinic information
            $clinic = DB::table('clinics')->where('id', $activationRecord->clinic_id)->first();
            
            if (!$clinic) {
                return back()->withErrors([
                    'activation_code' => __('Clinic not found.')
                ])->withInput();
            }

            // Parse metadata from activation code
            $metadata = json_decode($activationRecord->metadata, true);

            // Update clinic information
            DB::table('clinics')
                ->where('id', $clinic->id)
                ->update([
                    'phone' => $request->clinic_phone,
                    'address' => $request->clinic_address,
                    'is_active' => true,
                    'activated_at' => now(),
                    'updated_at' => now(),
                ]);

            // Create admin user
            $adminUserId = DB::table('users')->insertGetId([
                'username' => $request->admin_username,
                'email' => $metadata['admin_email'],
                'password' => Hash::make($request->admin_password),
                'first_name' => $metadata['admin_first_name'],
                'last_name' => $metadata['admin_last_name'],
                'role' => 'admin',
                'is_active' => true,
                'clinic_id' => $clinic->id,
                'language' => 'en',
                'activated_at' => now(),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mark activation code as used
            DB::table('activation_codes')
                ->where('id', $activationRecord->id)
                ->update([
                    'is_used' => true,
                    'used_at' => now(),
                    'used_by' => $adminUserId,
                    'updated_at' => now(),
                ]);

            // Create audit log
            DB::table('audit_logs')->insert([
                'user_id' => $adminUserId,
                'action' => 'clinic_activated',
                'model_type' => 'Clinic',
                'model_id' => $clinic->id,
                'changes' => json_encode([
                    'activation_code' => $request->activation_code,
                    'admin_created' => true,
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            DB::commit();

            // Auto-login the new admin
            Auth::loginUsingId($adminUserId);

            return redirect()->route('dashboard')
                ->with('success', __('Clinic activated successfully! Welcome to ConCure.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'activation_code' => __('Error activating clinic: ') . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Validate activation code via AJAX.
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:15',
        ]);

        $activationRecord = DB::table('activation_codes')
            ->leftJoin('clinics', 'activation_codes.clinic_id', '=', 'clinics.id')
            ->select(
                'activation_codes.*',
                'clinics.name as clinic_name',
                'clinics.email as clinic_email'
            )
            ->where('activation_codes.code', $request->code)
            ->where('activation_codes.type', 'clinic')
            ->first();

        if (!$activationRecord) {
            return response()->json([
                'valid' => false,
                'message' => __('Activation code not found.'),
            ]);
        }

        if ($activationRecord->is_used) {
            return response()->json([
                'valid' => false,
                'message' => __('This activation code has already been used.'),
            ]);
        }

        if (Carbon::parse($activationRecord->expires_at)->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => __('This activation code has expired.'),
            ]);
        }

        $metadata = json_decode($activationRecord->metadata, true);

        return response()->json([
            'valid' => true,
            'message' => __('Activation code is valid.'),
            'clinic_info' => [
                'name' => $activationRecord->clinic_name,
                'email' => $activationRecord->clinic_email,
                'admin_email' => $metadata['admin_email'] ?? '',
                'admin_first_name' => $metadata['admin_first_name'] ?? '',
                'admin_last_name' => $metadata['admin_last_name'] ?? '',
                'max_users' => $metadata['max_users'] ?? 10,
                'expires_at' => $activationRecord->expires_at,
            ],
        ]);
    }

    /**
     * Show public clinic registration page.
     */
    public function showRegistrationForm()
    {
        return view('public.clinic-registration');
    }

    /**
     * Process clinic registration request.
     */
    public function requestRegistration(Request $request)
    {
        $request->validate([
            'clinic_name' => 'required|string|max:255',
            'admin_first_name' => 'required|string|max:100',
            'admin_last_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'clinic_phone' => 'nullable|string|max:20',
            'clinic_address' => 'nullable|string|max:500',
            'clinic_website' => 'nullable|url|max:255',
            'expected_users' => 'required|integer|min:1|max:1000',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            // Store registration request
            DB::table('clinic_registration_requests')->insert([
                'clinic_name' => $request->clinic_name,
                'admin_first_name' => $request->admin_first_name,
                'admin_last_name' => $request->admin_last_name,
                'admin_email' => $request->admin_email,
                'clinic_phone' => $request->clinic_phone,
                'clinic_address' => $request->clinic_address,
                'clinic_website' => $request->clinic_website,
                'expected_users' => $request->expected_users,
                'message' => $request->message,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', __('Registration request submitted successfully! We will contact you soon.'));

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', __('Error submitting registration request: ') . $e->getMessage());
        }
    }
}
