<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle CSRF token mismatch exceptions
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Your session has expired. Please refresh the page and try again.'),
                    'error' => 'token_mismatch',
                    'redirect' => $request->url()
                ], 419);
            }

            // For regular requests, redirect back with helpful message
            return redirect()->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('error', __('Your session has expired. Please try again.'))
                ->with('csrf_error', true);
        }

        return parent::render($request, $exception);
    }
}
