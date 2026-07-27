<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use App\Models\ActivationCode;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $activationCode = ActivationCode::where('code', $request->activation_code)
                                       ->valid()
                                       ->first();

        if (!$activationCode) {
            throw ValidationException::withMessages([
                'activation_code' => ['Invalid or expired activation code.'],
            ]);
        }

        if ($activationCode->type === 'clinic') {
            return $this->registerClinic($request, $activationCode);
        } else {
            return $this->registerUser($request, $activationCode);
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'activation_code' => ['required', 'string'],
            'clinic_name' => ['required_if:is_clinic_registration,true', 'string', 'max:255'],
            'clinic_email' => ['required_if:is_clinic_registration,true', 'email', 'max:255'],
            'clinic_phone' => ['nullable', 'string', 'max:20'],
            'clinic_address' => ['nullable', 'string'],
        ]);
    }

    /**
     * Register a new clinic with admin user.
     */
    protected function registerClinic(Request $request, ActivationCode $activationCode)
    {
        // Create clinic
        $clinic = Clinic::create([
            'name' => $request->clinic_name,
            'email' => $request->clinic_email,
            'phone' => $request->clinic_phone,
            'address' => $request->clinic_address,
            'is_active' => true,
            'max_users' => 5, // Trial limit - Basic plan limit
            'activated_at' => now(),
            'is_trial' => true,
            'trial_started_at' => now(),
            'trial_expires_at' => now()->addDays(7),
            'subscription_status' => 'trial',
        ]);

        // Create admin user for the clinic
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'role' => 'admin',
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'clinic_id' => $clinic->id,
        ]);

        // Mark activation code as used
        $activationCode->markAsUsed($user);

        // Log the user in
        auth()->login($user);

        return redirect($this->redirectPath())->with('success', 'Clinic registered successfully!');
    }

    /**
     * Register a new user for an existing clinic.
     */
    protected function registerUser(Request $request, ActivationCode $activationCode)
    {
        if (!$activationCode->clinic_id || !$activationCode->role) {
            throw ValidationException::withMessages([
                'activation_code' => ['Invalid activation code configuration.'],
            ]);
        }

        $clinic = Clinic::find($activationCode->clinic_id);
        if (!$clinic || !$clinic->isActiveWithValidSubscription()) {
            throw ValidationException::withMessages([
                'activation_code' => ['Clinic is not active or subscription has expired.'],
            ]);
        }

        if ($clinic->hasReachedUserLimit()) {
            throw ValidationException::withMessages([
                'activation_code' => ['Clinic has reached its user limit.'],
            ]);
        }

        // Create user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'role' => $activationCode->role,
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'clinic_id' => $clinic->id,
            'created_by' => $activationCode->created_by,
        ]);

        // Mark activation code as used
        $activationCode->markAsUsed($user);

        // Log the user in
        auth()->login($user);

        return redirect($this->redirectPath())->with('success', 'Account registered successfully!');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // This method is not used in our custom registration flow
        // but is required by the RegistersUsers trait
        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'role' => 'patient', // Default role
            'is_active' => false, // Requires activation
            'language' => 'en',
        ]);
    }
}
