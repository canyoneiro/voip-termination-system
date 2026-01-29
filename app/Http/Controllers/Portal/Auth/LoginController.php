<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomerUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('portal.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = 'portal_login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        // Find user
        $user = CustomerUser::where('email', $request->email)->first();

        // Verify credentials
        if (!$user || !password_verify($request->password, $user->password)) {
            RateLimiter::hit($key, 900); // 15 minutes

            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Check if user is active
        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been disabled.'],
            ]);
        }

        // Check if customer has portal access
        if (!$user->customer?->portal_enabled) {
            throw ValidationException::withMessages([
                'email' => ['Portal access is not enabled for your account.'],
            ]);
        }

        // Check if customer is active
        if (!$user->customer?->active) {
            throw ValidationException::withMessages([
                'email' => ['Your company account has been suspended.'],
            ]);
        }

        // Clear rate limiter
        RateLimiter::clear($key);

        // Login
        Auth::guard('customer')->login($user, $request->boolean('remember'));

        // Record login
        $user->recordLogin($request->ip());

        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
