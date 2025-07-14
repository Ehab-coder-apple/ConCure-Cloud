<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
        $credentials = $this->credentials($request);
        
        // First check if user exists and is active
        $user = \App\Models\User::where($this->username(), $credentials[$this->username()])->first();
        
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

            if ($user->clinic->subscription_expires_at && $user->clinic->subscription_expires_at->isPast()) {
                throw ValidationException::withMessages([
                    $this->username() => ['Your clinic subscription has expired. Please renew to continue.'],
                ]);
            }
        }

        return $this->guard()->attempt($credentials, $request->filled('remember'));
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
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

        return redirect()->intended($this->redirectPath());
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

        if ($user) {
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
