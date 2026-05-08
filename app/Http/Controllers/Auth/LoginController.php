<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\SessionManagementService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        // Support username OR email in the same "username" field
        $loginInput = $request->input($this->username());
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $loginInput,
            'password' => $request->input('password'),
        ];

        // First check if user exists and is active
        $user = \App\Models\User::where($field, $loginInput)->first();

        if (!$user) {
            return false;
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                $this->username() => ['Your account has been deactivated. Please contact your administrator.'],
            ]);
        }

        if (!$user->activated_at) {
            throw ValidationException::withMessages([
                $this->username() => ['Your account requires activation. Please contact your administrator.'],
            ]);
        }

        // Check clinic status for all clinic users
        if ($user->clinic) {
            if (!$user->clinic->is_active) {
                throw ValidationException::withMessages([
                    $this->username() => ['Your clinic has been deactivated. Please contact support.'],
                ]);
            }

            if (!$user->clinic->activated_at) {
                throw ValidationException::withMessages([
                    $this->username() => ['Your clinic requires activation. Please contact support.'],
                ]);
            }

            // Subscription expiry check removed - no longer needed
            if (false) { // Disabled subscription check
                throw ValidationException::withMessages([
                    $this->username() => ['Your clinic subscription has expired. Please renew to continue.'],
                ]);
            }
        }

        // Enforce account expiry if set
        if ($user->expires_at && now()->greaterThan($user->expires_at)) {
            throw ValidationException::withMessages([
                $this->username() => ['Your account has expired. Please contact your administrator.'],
            ]);
        }

        // Attempt the login
        $loginAttempt = $this->guard()->attempt($credentials, $request->filled('remember'));

        // If login was successful, create session record
        if ($loginAttempt) {
            try {
                $credential = $field === 'email' ? $user->email : $user->username;
                SessionManagementService::createSession($user, $credential, $request);
                \Log::info('User session created after login attempt', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to create session after login', ['error' => $e->getMessage()]);
            }
        }

        return $loginAttempt;
    }

    /**
     * The user has been authenticated.
     * This is called after successful login via AuthenticatesUsers trait.
     */
    protected function authenticated(Request $request, $user)
    {
        // Get the credential used (username or email)
        $loginInput = $request->input($this->username());
        $credential = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? $loginInput : $user->username;

        // CREATE SESSION RECORD - Terminate old sessions for this credential
        try {
            $session = SessionManagementService::createSession($user, $credential, $request);
            if ($session) {
                \Log::info('Session created for user', ['user_id' => $user->id, 'session_id' => $session->session_id]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create user session', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Log successful login
        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'user_role' => $user->role,
            'clinic_id' => $user->clinic_id,
            'action' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        // Set user's preferred language
        if ($user->language) {
            session(['locale' => $user->language]);
            app()->setLocale($user->language);
        }

        // Return null to let the default behavior handle the redirect
        // The AuthenticatesUsers trait will handle the redirect based on isSuperAdmin
        return null;
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        if ($user) {
            // Terminate the session record
            SessionManagementService::terminateSessionBySessionId($sessionId, 'manual_logout');

            // Log logout
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
            ]);
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect('/');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Log failed login attempt
        $username = $request->input($this->username());
        AuditLog::create([
            'user_id' => null,
            'user_name' => $username,
            'user_role' => null,
            'clinic_id' => null,
            'action' => 'failed_login',
            'description' => "Failed login attempt for username: {$username}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
